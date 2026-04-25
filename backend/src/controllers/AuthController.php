<?php
namespace App\Controllers;

use App\Http\Response;
use App\Http\Request;
use App\Repositories\AuthRepository;
use App\Repositories\BlacksmithProfileRepository;
use App\Services\AuthService;
use Firebase\JWT\JWT;

class AuthController {
    public function __construct(
        private \PDO $pdo,
        private AuthRepository $authRepository,
        private BlacksmithProfileRepository $profileRepository,
        private AuthService $authService
    ) {}

    public function register(Request $request, Response $response, $args) {
        $data = $request->getParsedBody();
        try {
            $user = $this->authService->register($data);
            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => $user->toArray()
            ]));
        } catch (\InvalidArgumentException $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function login(Request $request, Response $response, $args) {
        $data = $request->getParsedBody();
        try {
            $user = $this->authService->login($data['username'] ?? '', $data['password'] ?? '');
            if (!$user) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
            }

            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => $user->toArray()
            ]));
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function logout(Request $request, Response $response, $args) {
        $authUser = $request->getAttribute('auth_user');
        $userId = $authUser['id'] ?? null;
        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => $userId ? 'Logged out' : 'No session'
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function session(Request $request, Response $response, $args): Response
    {
        $authUser = $request->getAttribute('auth_user');

        if (!$authUser || empty($authUser['id'])) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Unauthorized',
                'login_url' => $_ENV['LOGIN_URL'] ?? ($_ENV['WEB_HATCHERY_LOGIN_URL'] ?? ''),
            ]));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        $email = $authUser['email'] ?? '';
        $username = $authUser['username'] ?? '';
        if ($username === '' && $email !== '') {
            $username = explode('@', $email)[0];
        }

        if (!empty($authUser['is_guest'])) {
            $user = $this->authRepository->findById((int) $authUser['id']);
            if (!$user) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'Guest session expired',
                ]));
                return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
            }
        } else {
            $user = $this->authRepository->upsertWebHatcheryUser((int) $authUser['id'], $email, $username ?: 'blacksmith');
        }

        $profile = $this->profileRepository->findByUserId((int) $user['id']);
        if (!$profile) {
            $forgeName = ($user['username'] ?? $username) !== '' ? ucfirst((string) ($user['username'] ?? $username)) . ' Forge' : 'New Forge';
            $profile = $this->profileRepository->createDefaultProfile((int) $user['id'], $forgeName);
        } else {
            $this->profileRepository->updateLastSeen((int) $user['id']);
        }

        $response->getBody()->write(json_encode([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => (int) $user['id'],
                    'email' => $user['email'] ?? $email,
                    'username' => $user['username'] ?? $username,
                    'auth_provider' => $user['auth_provider'] ?? ($authUser['auth_type'] ?? 'webhatchery'),
                    'auth_type' => $authUser['auth_type'] ?? 'frontpage',
                    'is_guest' => (bool) ($authUser['is_guest'] ?? false),
                    'guest_user_id' => !empty($authUser['is_guest']) ? (int) $user['id'] : null,
                ],
                'profile' => $profile->toArray(),
            ],
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function guestSession(Request $request, Response $response, $args): Response
    {
        $secret = $_ENV['JWT_SECRET']
            ?? $_SERVER['JWT_SECRET']
            ?? getenv('JWT_SECRET')
            ?: '';

        if ($secret === '') {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'JWT secret not configured',
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

        $guestBase = 'guest_' . strtolower(substr(bin2hex(random_bytes(3)), 0, 6));
        $user = $this->authRepository->createGuestUser($guestBase);
        $profile = $this->profileRepository->createDefaultProfile((int) $user['id'], ucfirst($user['username']) . ' Forge');

        $issuedAt = time();
        $token = JWT::encode([
            'sub' => (int) $user['id'],
            'user_id' => (int) $user['id'],
            'guest_user_id' => (int) $user['id'],
            'username' => $user['username'],
            'display_name' => $user['username'],
            'role' => 'guest',
            'roles' => ['guest'],
            'auth_type' => 'guest',
            'is_guest' => true,
            'iss' => $_ENV['JWT_ISSUER'] ?? 'webhatchery',
            'aud' => $_ENV['JWT_AUDIENCE'] ?? ($_ENV['APP_URL'] ?? null),
            'iat' => $issuedAt,
            'exp' => $issuedAt + (60 * 60 * 24 * 30),
        ], $secret, 'HS256');

        $response->getBody()->write(json_encode([
            'success' => true,
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => (int) $user['id'],
                    'email' => $user['email'] ?? '',
                    'username' => $user['username'],
                    'auth_provider' => 'guest',
                    'auth_type' => 'guest',
                    'is_guest' => true,
                    'guest_user_id' => (int) $user['id'],
                ],
                'profile' => $profile->toArray(),
            ],
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function linkGuest(Request $request, Response $response, $args): Response
    {
        $authUser = $request->getAttribute('auth_user');
        if (!$authUser || empty($authUser['id'])) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Unauthorized',
                'login_url' => $_ENV['LOGIN_URL'] ?? ($_ENV['WEB_HATCHERY_LOGIN_URL'] ?? ''),
            ]));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        if (!empty($authUser['is_guest'])) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Guest sessions cannot link another guest session',
            ]));
            return $response->withStatus(422)->withHeader('Content-Type', 'application/json');
        }

        $data = $request->getParsedBody();
        $guestUserId = (int) ($data['guest_user_id'] ?? 0);
        if ($guestUserId <= 0) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Invalid guest user identifier',
            ]));
            return $response->withStatus(422)->withHeader('Content-Type', 'application/json');
        }

        $targetUserId = (int) $authUser['id'];
        if ($guestUserId === $targetUserId) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Guest account already linked',
            ]));
            return $response->withStatus(422)->withHeader('Content-Type', 'application/json');
        }

        $guestUser = $this->authRepository->findById($guestUserId);
        if (!$guestUser || ($guestUser['auth_provider'] ?? null) !== 'guest') {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Guest account not found',
            ]));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        $targetUser = $this->authRepository->upsertWebHatcheryUser(
            $targetUserId,
            $authUser['email'] ?? '',
            $authUser['username'] ?? 'blacksmith'
        );

        try {
            $this->pdo->beginTransaction();

            $this->mergeBlacksmithProfile($guestUserId, (int) $targetUser['id']);
            $this->mergeInventory($guestUserId, (int) $targetUser['id']);
            $this->mergeSimpleOwnershipTable('user_upgrades', $guestUserId, (int) $targetUser['id'], true);
            $this->mergeSimpleOwnershipTable('minigames', $guestUserId, (int) $targetUser['id']);
            $this->mergeSimpleOwnershipTable('crafting', $guestUserId, (int) $targetUser['id']);
            $this->mergeSimpleOwnershipTable('customer_instances', $guestUserId, (int) $targetUser['id']);
            $this->mergeSimpleOwnershipTable('customer_sales', $guestUserId, (int) $targetUser['id']);

            $deleteUser = $this->pdo->prepare('DELETE FROM users WHERE id = ?');
            $deleteUser->execute([$guestUserId]);

            $this->pdo->commit();
        } catch (\Exception $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Failed to link guest account: ' . $exception->getMessage(),
            ]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }

        $profile = $this->profileRepository->findByUserId((int) $targetUser['id']);
        if (!$profile) {
            $forgeName = ($targetUser['username'] ?? '') !== '' ? ucfirst((string) $targetUser['username']) . ' Forge' : 'New Forge';
            $profile = $this->profileRepository->createDefaultProfile((int) $targetUser['id'], $forgeName);
        }

        $response->getBody()->write(json_encode([
            'success' => true,
            'data' => [
                'linked' => true,
                'guest_user_id' => $guestUserId,
                'user' => [
                    'id' => (int) $targetUser['id'],
                    'email' => $targetUser['email'] ?? ($authUser['email'] ?? ''),
                    'username' => $targetUser['username'] ?? ($authUser['username'] ?? 'blacksmith'),
                    'auth_provider' => 'webhatchery',
                    'auth_type' => 'frontpage',
                    'is_guest' => false,
                    'guest_user_id' => null,
                ],
                'profile' => $profile->toArray(),
            ],
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }

    private function mergeBlacksmithProfile(int $guestUserId, int $targetUserId): void
    {
        $guest = $this->profileRepository->findByUserId($guestUserId);
        if (!$guest) {
            return;
        }

        $target = $this->profileRepository->findByUserId($targetUserId);
        if (!$target) {
            $stmt = $this->pdo->prepare('UPDATE blacksmith_profiles SET user_id = ? WHERE user_id = ?');
            $stmt->execute([$targetUserId, $guestUserId]);
            return;
        }

        $mergedMastery = array_merge((array) ($target->crafting_mastery ?? []), (array) ($guest->crafting_mastery ?? []));
        $mergedSettings = array_merge((array) ($guest->settings ?? []), (array) ($target->settings ?? []));

        $this->profileRepository->updateByUserId($targetUserId, [
            'forge_name' => $target->forge_name ?: $guest->forge_name,
            'level' => max((int) $target->level, (int) $guest->level),
            'reputation' => (int) $target->reputation + (int) $guest->reputation,
            'coins' => (int) $target->coins + (int) $guest->coins,
            'crafting_mastery' => json_encode($mergedMastery),
            'settings' => json_encode($mergedSettings),
            'last_seen_at' => date('Y-m-d H:i:s'),
        ]);

        $stmt = $this->pdo->prepare('DELETE FROM blacksmith_profiles WHERE user_id = ?');
        $stmt->execute([$guestUserId]);
    }

    private function mergeInventory(int $guestUserId, int $targetUserId): void
    {
        $select = $this->pdo->prepare('SELECT items FROM inventory WHERE user_id = ? LIMIT 1');
        $select->execute([$guestUserId]);
        $guestRow = $select->fetch(\PDO::FETCH_ASSOC);
        if (!$guestRow) {
            return;
        }

        $select->execute([$targetUserId]);
        $targetRow = $select->fetch(\PDO::FETCH_ASSOC);

        $guestPayload = json_decode((string) ($guestRow['items'] ?? '{}'), true) ?: [];

        if (!$targetRow) {
          $stmt = $this->pdo->prepare('UPDATE inventory SET user_id = ? WHERE user_id = ?');
          $stmt->execute([$targetUserId, $guestUserId]);
          return;
        }

        $targetPayload = json_decode((string) ($targetRow['items'] ?? '{}'), true) ?: [];

        $merged = [
            'items' => array_values(array_merge($targetPayload['items'] ?? [], $guestPayload['items'] ?? [])),
            'materials' => array_merge($targetPayload['materials'] ?? [], $guestPayload['materials'] ?? []),
            'reserved_materials' => array_merge($targetPayload['reserved_materials'] ?? [], $guestPayload['reserved_materials'] ?? []),
        ];

        foreach (['materials', 'reserved_materials'] as $key) {
            $combined = [];
            foreach (($targetPayload[$key] ?? []) as $name => $quantity) {
                $combined[$name] = ($combined[$name] ?? 0) + (int) $quantity;
            }
            foreach (($guestPayload[$key] ?? []) as $name => $quantity) {
                $combined[$name] = ($combined[$name] ?? 0) + (int) $quantity;
            }
            $merged[$key] = $combined;
        }

        $update = $this->pdo->prepare('UPDATE inventory SET items = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?');
        $update->execute([json_encode($merged), $targetUserId]);

        $delete = $this->pdo->prepare('DELETE FROM inventory WHERE user_id = ?');
        $delete->execute([$guestUserId]);
    }

    private function mergeSimpleOwnershipTable(string $table, int $guestUserId, int $targetUserId, bool $dedupe = false): void
    {
        if ($dedupe && $table === 'user_upgrades') {
            $stmt = $this->pdo->prepare(
                'INSERT IGNORE INTO user_upgrades (user_id, upgrade_id, purchased_at)
                 SELECT ?, upgrade_id, purchased_at FROM user_upgrades WHERE user_id = ?'
            );
            $stmt->execute([$targetUserId, $guestUserId]);

            $delete = $this->pdo->prepare('DELETE FROM user_upgrades WHERE user_id = ?');
            $delete->execute([$guestUserId]);
            return;
        }

        $stmt = $this->pdo->prepare("UPDATE {$table} SET user_id = ? WHERE user_id = ?");
        $stmt->execute([$targetUserId, $guestUserId]);
    }
}
