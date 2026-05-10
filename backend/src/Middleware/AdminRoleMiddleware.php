<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Http\Request;
use App\Http\Response;

final class AdminRoleMiddleware
{
    public function __invoke(Request $request, Response $response, array $routeParams = []): Response|Request|bool
    {
        $authUser = $request->getAttribute('auth_user');
        if (!is_array($authUser)) {
            return $this->forbidden($response);
        }

        $roles = $authUser['roles'] ?? [];
        if (!is_array($roles)) {
            $roles = [$roles];
        }

        $role = $authUser['role'] ?? null;
        if (is_string($role) && $role !== '') {
            $roles[] = $role;
        }

        $normalizedRoles = array_map(
            static fn (mixed $value): string => strtolower((string) $value),
            $roles
        );

        if (!in_array('admin', $normalizedRoles, true)) {
            return $this->forbidden($response);
        }

        return $request;
    }

    private function forbidden(Response $response): Response
    {
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => 'Admin role required',
        ]));

        return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
    }
}
