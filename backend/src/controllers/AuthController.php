<?php
namespace App\Controllers;

use App\Http\Response;
use App\Http\Request;
use App\Repositories\AuthRepository;
use App\Repositories\BlacksmithProfileRepository;
use App\Services\AuthService;

class AuthController {
    public function __construct(
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
        $queryParams = $request->getQueryParams();
        $debugEnv = ($queryParams['debug_env'] ?? '0') === '1';
        $serverParams = $request->getServerParams();
        $remoteAddr = $serverParams['REMOTE_ADDR'] ?? '';
        $isLocal = in_array($remoteAddr, ['127.0.0.1', '::1'], true);
        $appDebug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';

        if ($debugEnv && $isLocal && $appDebug) {
            $response->getBody()->write(json_encode([
                'success' => true,
                'debug_env' => true,
                'env' => $_ENV,
                'server' => [
                    'REMOTE_ADDR' => $remoteAddr,
                    'APP_DEBUG' => $_ENV['APP_DEBUG'] ?? null,
                    'APP_BASE_PATH' => $_ENV['APP_BASE_PATH'] ?? null,
                ],
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $authUser = $request->getAttribute('auth_user');

        if (!$authUser || empty($authUser['id'])) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Unauthorized',
            ]));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        $email = $authUser['email'] ?? '';
        $username = $authUser['username'] ?? '';
        if ($username === '' && $email !== '') {
            $username = explode('@', $email)[0];
        }

        $user = $this->authRepository->upsertWebHatcheryUser((int) $authUser['id'], $email, $username ?: 'blacksmith');

        $profile = $this->profileRepository->findByUserId((int) $user['id']);
        if (!$profile) {
            $forgeName = $username !== '' ? ucfirst($username) . ' Forge' : 'New Forge';
            $profile = $this->profileRepository->createDefaultProfile((int) $user['id'], $forgeName);
        } else {
            $this->profileRepository->updateLastSeen((int) $user['id']);
        }

        $response->getBody()->write(json_encode([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'] ?? $email,
                    'username' => $user['username'] ?? $username,
                ],
                'profile' => $profile->toArray(),
            ],
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
