<?php

declare(strict_types=1);

namespace App\Actions;

use App\Core\Environment;
use App\Repositories\AuthRepository;
use App\Repositories\BlacksmithProfileRepository;
use App\Repositories\GuestAccountRepository;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

final class LinkGuestAccountAction
{
    public function __construct(
        private readonly AuthRepository $authRepository,
        private readonly BlacksmithProfileRepository $profileRepository,
        private readonly GuestAccountRepository $guestAccountRepository
    ) {
    }

    /**
     * @param array<string, mixed> $authUser
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function execute(array $authUser, array $data): array
    {
        if (!empty($authUser['is_guest'])) {
            throw new \InvalidArgumentException('Guest sessions cannot link another guest session');
        }

        $guestUserId = $this->resolveGuestUserId($data);
        if ($guestUserId <= 0) {
            throw new \InvalidArgumentException('Invalid guest user identifier');
        }

        $targetUserId = (int) ($authUser['id'] ?? 0);
        if ($targetUserId <= 0) {
            throw new \InvalidArgumentException('Authenticated user identifier is missing');
        }

        if ($guestUserId === $targetUserId) {
            throw new \InvalidArgumentException('Guest account already linked');
        }

        $guestUser = $this->authRepository->findById($guestUserId);
        if (!$guestUser || ($guestUser['auth_provider'] ?? null) !== 'guest') {
            throw new \RuntimeException('Guest account not found');
        }

        $targetUser = $this->authRepository->upsertWebHatcheryUser(
            $targetUserId,
            $authUser['email'] ?? '',
            $authUser['username'] ?? 'blacksmith'
        );

        $moved = $this->guestAccountRepository->moveGuestData($guestUserId, (int) $targetUser['id']);
        if (array_sum($moved) === 0) {
            throw new \RuntimeException('No guest data was moved');
        }

        $profile = $this->profileRepository->findByUserId((int) $targetUser['id']);
        if (!$profile) {
            $forgeName = ($targetUser['username'] ?? '') !== '' ? ucfirst((string) $targetUser['username']) . ' Forge' : 'New Forge';
            $profile = $this->profileRepository->createDefaultProfile((int) $targetUser['id'], $forgeName);
        }

        return [
            'linked' => true,
            'guest_user_id' => $guestUserId,
            'moved' => $moved,
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
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function resolveGuestUserId(array $data): int
    {
        $guestToken = isset($data['guest_token']) && is_string($data['guest_token']) ? trim($data['guest_token']) : '';
        if ($guestToken === '') {
            return (int) ($data['guest_user_id'] ?? 0);
        }

        $decoded = JWT::decode($guestToken, new Key(Environment::required('JWT_SECRET'), 'HS256'));
        if (empty($decoded->is_guest)) {
            throw new \InvalidArgumentException('Guest token must belong to a guest session');
        }

        return (int) ($decoded->guest_user_id ?? $decoded->user_id ?? $decoded->sub ?? 0);
    }

}
