<?php
namespace App\Controllers;

use App\Http\Response;
use App\Http\Request;
use App\Repositories\AuthRepository;
use App\Repositories\BlacksmithProfileRepository;

class AuthController {
    public function __construct(
        private AuthRepository $authRepository,
        private BlacksmithProfileRepository $profileRepository
    ) {}

    public function register(Request $request, Response $response, $args) {
        $data = $request->getParsedBody();
        $result = \App\Actions\AuthActions::register($data);
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function login(Request $request, Response $response, $args) {
        $data = $request->getParsedBody();
        $result = \App\Actions\AuthActions::login($data);
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function logout(Request $request, Response $response, $args) {
        $authUser = $request->getAttribute('auth_user');
        $userId = $authUser['id'] ?? null;
        $result = \App\Actions\AuthActions::logout($userId);
        $response->getBody()->write(json_encode($result));
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
