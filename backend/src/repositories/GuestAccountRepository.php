<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class GuestAccountRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return array<string, int>
     */
    public function moveGuestData(int $guestUserId, int $targetUserId): array
    {
        $moved = [
            'blacksmith_profiles' => 0,
            'inventory' => 0,
            'user_upgrades' => 0,
            'minigames' => 0,
            'crafting' => 0,
            'customer_instances' => 0,
            'customer_sales' => 0,
            'users' => 0,
        ];

        $this->pdo->beginTransaction();
        try {
            $moved['blacksmith_profiles'] = $this->mergeBlacksmithProfile($guestUserId, $targetUserId);
            $moved['inventory'] = $this->mergeInventory($guestUserId, $targetUserId);
            $moved['user_upgrades'] = $this->mergeUserUpgrades($guestUserId, $targetUserId);
            $moved['minigames'] = $this->reassignOwnershipTable('minigames', $guestUserId, $targetUserId);
            $moved['crafting'] = $this->reassignOwnershipTable('crafting', $guestUserId, $targetUserId);
            $moved['customer_instances'] = $this->reassignOwnershipTable('customer_instances', $guestUserId, $targetUserId);
            $moved['customer_sales'] = $this->reassignOwnershipTable('customer_sales', $guestUserId, $targetUserId);
            $moved['users'] = $this->deleteGuestUser($guestUserId);

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }

        return $moved;
    }

    private function mergeBlacksmithProfile(int $guestUserId, int $targetUserId): int
    {
        $guest = $this->fetchProfile($guestUserId);
        if (!$guest) {
            return 0;
        }

        $target = $this->fetchProfile($targetUserId);
        if (!$target) {
            $statement = $this->pdo->prepare('UPDATE blacksmith_profiles SET user_id = ? WHERE user_id = ?');
            $statement->execute([$targetUserId, $guestUserId]);
            return $statement->rowCount();
        }

        $guestMastery = $this->decodeJson((string) ($guest['crafting_mastery'] ?? '{}'));
        $targetMastery = $this->decodeJson((string) ($target['crafting_mastery'] ?? '{}'));
        $guestSettings = $this->decodeJson((string) ($guest['settings'] ?? '{}'));
        $targetSettings = $this->decodeJson((string) ($target['settings'] ?? '{}'));

        $update = $this->pdo->prepare(
            'UPDATE blacksmith_profiles
             SET forge_name = ?, level = ?, reputation = ?, coins = ?, crafting_mastery = ?, settings = ?, last_seen_at = CURRENT_TIMESTAMP
             WHERE user_id = ?'
        );
        $update->execute([
            $target['forge_name'] ?: $guest['forge_name'],
            max((int) $target['level'], (int) $guest['level']),
            (int) $target['reputation'] + (int) $guest['reputation'],
            (int) $target['coins'] + (int) $guest['coins'],
            json_encode(array_merge($targetMastery, $guestMastery)),
            json_encode(array_merge($guestSettings, $targetSettings)),
            $targetUserId,
        ]);

        $delete = $this->pdo->prepare('DELETE FROM blacksmith_profiles WHERE user_id = ?');
        $delete->execute([$guestUserId]);

        return $update->rowCount() + $delete->rowCount();
    }

    private function mergeInventory(int $guestUserId, int $targetUserId): int
    {
        $guestRow = $this->fetchInventory($guestUserId);
        if (!$guestRow) {
            return 0;
        }

        $targetRow = $this->fetchInventory($targetUserId);
        if (!$targetRow) {
            $statement = $this->pdo->prepare('UPDATE inventory SET user_id = ? WHERE user_id = ?');
            $statement->execute([$targetUserId, $guestUserId]);
            return $statement->rowCount();
        }

        $guestPayload = $this->decodeJson((string) ($guestRow['items'] ?? '{}'));
        $targetPayload = $this->decodeJson((string) ($targetRow['items'] ?? '{}'));
        $merged = [
            'items' => array_values(array_merge($targetPayload['items'] ?? [], $guestPayload['items'] ?? [])),
            'materials' => $this->mergeQuantities($targetPayload['materials'] ?? [], $guestPayload['materials'] ?? []),
            'reserved_materials' => $this->mergeQuantities($targetPayload['reserved_materials'] ?? [], $guestPayload['reserved_materials'] ?? []),
        ];

        $update = $this->pdo->prepare('UPDATE inventory SET items = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?');
        $update->execute([json_encode($merged), $targetUserId]);

        $delete = $this->pdo->prepare('DELETE FROM inventory WHERE user_id = ?');
        $delete->execute([$guestUserId]);

        return $update->rowCount() + $delete->rowCount();
    }

    private function mergeUserUpgrades(int $guestUserId, int $targetUserId): int
    {
        $statement = $this->pdo->prepare(
            'INSERT IGNORE INTO user_upgrades (user_id, upgrade_id, purchased_at)
             SELECT ?, upgrade_id, purchased_at FROM user_upgrades WHERE user_id = ?'
        );
        $statement->execute([$targetUserId, $guestUserId]);
        $inserted = $statement->rowCount();

        $delete = $this->pdo->prepare('DELETE FROM user_upgrades WHERE user_id = ?');
        $delete->execute([$guestUserId]);

        return $inserted + $delete->rowCount();
    }

    private function reassignOwnershipTable(string $table, int $guestUserId, int $targetUserId): int
    {
        if (!in_array($table, ['minigames', 'crafting', 'customer_instances', 'customer_sales'], true)) {
            throw new \InvalidArgumentException('Unsupported ownership table');
        }

        $statement = $this->pdo->prepare("UPDATE {$table} SET user_id = ? WHERE user_id = ?");
        $statement->execute([$targetUserId, $guestUserId]);

        return $statement->rowCount();
    }

    private function deleteGuestUser(int $guestUserId): int
    {
        $statement = $this->pdo->prepare('DELETE FROM users WHERE id = ?');
        $statement->execute([$guestUserId]);

        return $statement->rowCount();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fetchProfile(int $userId): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM blacksmith_profiles WHERE user_id = ? LIMIT 1');
        $statement->execute([$userId]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fetchInventory(int $userId): ?array
    {
        $statement = $this->pdo->prepare('SELECT items FROM inventory WHERE user_id = ? LIMIT 1');
        $statement->execute([$userId]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJson(string $payload): array
    {
        $decoded = json_decode($payload, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param array<string, mixed> $target
     * @param array<string, mixed> $guest
     * @return array<string, int>
     */
    private function mergeQuantities(array $target, array $guest): array
    {
        $merged = [];
        foreach ($target as $name => $quantity) {
            $merged[(string) $name] = (int) $quantity;
        }
        foreach ($guest as $name => $quantity) {
            $key = (string) $name;
            $merged[$key] = ($merged[$key] ?? 0) + (int) $quantity;
        }

        return $merged;
    }
}
