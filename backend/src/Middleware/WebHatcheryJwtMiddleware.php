<?php

namespace App\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Http\Response;
use App\Http\Request;
class WebHatcheryJwtMiddleware
{
    public function __invoke(Request $request, Response $response, array $routeParams = []): Response|Request|bool
    {
        $authHeader = $request->getHeaderLine('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $this->unauthorized($response, 'Authorization header missing or invalid');
        }

        $token = $matches[1];
        $secret = $this->requiredEnv('JWT_SECRET');

        try {
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));
            $isGuest = (bool) ($decoded->is_guest ?? false);

            $expectedIssuer = $this->requiredEnv('JWT_ISSUER');
            if (!$isGuest && isset($decoded->iss) && $decoded->iss !== $expectedIssuer) {
                return $this->unauthorized($response, 'Invalid token issuer');
            }

            $expectedAudience = $this->requiredEnv('JWT_AUDIENCE');
            if (!$isGuest && $expectedAudience && isset($decoded->aud)) {
                $aud = $decoded->aud;
                $isValidAudience = is_array($aud) ? in_array($expectedAudience, $aud, true) : $aud === $expectedAudience;
                if (!$isValidAudience) {
                    return $this->unauthorized($response, 'Invalid token audience');
                }
            }

            $userId = $decoded->sub ?? $decoded->user_id ?? null;
            if (!$userId) {
                return $this->unauthorized($response, 'Token missing user identifier');
            }

            $request = $request->withAttribute('auth_user', [
                'id' => (int) $userId,
                'email' => $decoded->email ?? null,
                'username' => $decoded->username ?? null,
                'roles' => $decoded->roles ?? [],
                'role' => $decoded->role ?? (($decoded->roles[0] ?? null) ?: 'user'),
                'display_name' => $decoded->display_name ?? $decoded->username ?? null,
                'auth_type' => $decoded->auth_type ?? ($isGuest ? 'guest' : 'frontpage'),
                'is_guest' => $isGuest,
                'guest_user_id' => $decoded->guest_user_id ?? null,
            ]);

            return $request;
        } catch (\Exception $e) {
            return $this->unauthorized($response, 'Invalid token');
        }
    }

    private function unauthorized(Response $response, string $message): Response
    {
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => $message,
            'login_url' => $this->requiredEnv('LOGIN_URL'),
        ]));
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    }

    private function requiredEnv(string $name): string
    {
        $value = $_ENV[$name] ?? '';
        if ($value === '') {
            throw new \RuntimeException("Missing required environment variable: {$name}");
        }
        return $value;
    }
}
