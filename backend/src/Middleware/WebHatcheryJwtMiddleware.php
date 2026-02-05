<?php

namespace App\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Http\Response;
use App\Http\Request;
class WebHatcheryJwtMiddleware
{
    public function __invoke(Request $request, Response $response, array $routeParams = []): bool
    {
        $queryParams = $request->getQueryParams();
        $debugEnv = ($queryParams['debug_env'] ?? '0') === '1';
        $serverParams = $request->getServerParams();
        $remoteAddr = $serverParams['REMOTE_ADDR'] ?? '';
        $isLocal = in_array($remoteAddr, ['127.0.0.1', '::1'], true);
        $appDebug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';

        if ($debugEnv && $isLocal && $appDebug) {
            $request = $request->withAttribute('auth_user', [
                'id' => 0,
                'email' => null,
                'username' => 'debug',
                'roles' => [],
            ]);
            return true;
        }

        $authHeader = $request->getHeaderLine('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $this->unauthorized($response, 'Authorization header missing or invalid');
            return false;
        }

        $token = $matches[1];
        $secret = $_ENV['WEBHATCHERY_JWT_SECRET']
            ?? $_SERVER['WEBHATCHERY_JWT_SECRET']
            ?? getenv('WEBHATCHERY_JWT_SECRET')
            ?? $_ENV['AUTH_JWT_SECRET']
            ?? $_SERVER['AUTH_JWT_SECRET']
            ?? getenv('AUTH_JWT_SECRET')
            ?? $_ENV['JWT_SECRET']
            ?? $_SERVER['JWT_SECRET']
            ?? getenv('JWT_SECRET')
            ?: '';
        if ($secret === '') {
            $this->unauthorized($response, 'JWT secret not configured');
            return false;
        }

        try {
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));

            $expectedIssuer = $_ENV['JWT_ISSUER'] ?? 'webhatchery';
            if (isset($decoded->iss) && $decoded->iss !== $expectedIssuer) {
                $this->unauthorized($response, 'Invalid token issuer');
                return false;
            }

            $expectedAudience = $_ENV['JWT_AUDIENCE'] ?? ($_ENV['APP_URL'] ?? null);
            if ($expectedAudience && isset($decoded->aud)) {
                $aud = $decoded->aud;
                $isValidAudience = is_array($aud) ? in_array($expectedAudience, $aud, true) : $aud === $expectedAudience;
                if (!$isValidAudience) {
                    $this->unauthorized($response, 'Invalid token audience');
                    return false;
                }
            }

            $userId = $decoded->sub ?? $decoded->user_id ?? null;
            if (!$userId) {
                $this->unauthorized($response, 'Token missing user identifier');
                return false;
            }

            $request = $request->withAttribute('auth_user', [
                'id' => (int) $userId,
                'email' => $decoded->email ?? null,
                'username' => $decoded->username ?? null,
                'roles' => $decoded->roles ?? [],
            ]);

            return true;
        } catch (\Exception $e) {
            $this->unauthorized($response, 'Invalid token');
            return false;
        }
    }

    private function unauthorized(Response $response, string $message): void
    {
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => $message,
        ]));
        $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    }
}
