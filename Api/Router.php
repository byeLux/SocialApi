<?php

declare(strict_types=1);

namespace SocialApi\Api;

class Router
{
    private array $routes = [];

    public function addRoute(string $method, string $path, callable $handler): void
    {
        $pattern = preg_quote($path, '#');
        $pattern = str_replace('\\{id\\}', '([^/]+)', $pattern);

        $this->routes[] = [$method, '#^' . $pattern . '$#D', $handler];
    }

    public function dispatch(Request $request): JsonResponse
    {
        $allowed = [];

        foreach($this->routes as [$method, $pattern, $handler]) {
            if(!preg_match($pattern, $request->getPath(), $matches)) {
                continue;
            }

            $allowed[] = $method;
            if($request->getMethod() !== $method) {
                continue;
            }

            return $handler($request, ...array_slice($matches, 1));
        }

        if($allowed === []) {
            return new JsonResponse(['error' => 'not_found'], 404);
        }

        $allow = implode(', ', array_merge(array_unique($allowed), ['OPTIONS']));

        if($request->getMethod() === 'OPTIONS') {
            return new JsonResponse([], 204, ['Allow' => $allow]);
        }

        return new JsonResponse(['error' => 'method_not_allowed'], 405, ['Allow' => $allow]);
    }
}
