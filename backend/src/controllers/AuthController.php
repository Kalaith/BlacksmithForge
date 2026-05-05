<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Environment;
use App\Actions\LinkGuestAccountAction;
use App\Http\Response;
use App\Http\Request;
use App\Repositories\AuthRepository;
use App\Repositories\BlacksmithProfileRepository;
use Firebase\JWT\JWT;

class AuthController {
    public function __construct(
        private AuthRepository $authRepository,
        private BlacksmithProfileRepository $profileRepository,
        private LinkGuestAccountAction $linkGuestAccountAction
    ) {}

    public function loginInfo(Request $request, Response $response, $args): Response
    {
        $response->getBody()->write(json_encode([
            'success' => true,
            'data' => [
                'login_url' => Environment::required('WEB_HATCHERY_LOGIN_URL'),
            ],
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
                'login_url' => Environment::required('WEB_HATCHERY_LOGIN_URL'),
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
        $secret = Environment::required('JWT_SECRET');

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
            'iss' => Environment::required('JWT_ISSUER'),
            'aud' => Environment::required('JWT_AUDIENCE'),
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
                'login_url' => Environment::required('WEB_HATCHERY_LOGIN_URL'),
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

        try {
            $result = $this->linkGuestAccountAction->execute($authUser, $request->getParsedBody());
        } catch (\InvalidArgumentException $exception) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => $exception->getMessage(),
            ]));
            return $response->withStatus(422)->withHeader('Content-Type', 'application/json');
        } catch (\Exception $exception) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Failed to link guest account: ' . $exception->getMessage(),
            ]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }

        $response->getBody()->write(json_encode([
            'success' => true,
            'data' => $result,
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }
}
