<?php
/**
 * Lightweight and Robust REST API Router
 */

require_once __DIR__ . '/../helpers/Response.php';

class Router {
    private array $routes = [];

    public function get(string $path, $handler): self {
        return $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, $handler): self {
        return $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, $handler): self {
        return $this->addRoute('PUT', $path, $handler);
    }

    public function delete(string $path, $handler): self {
        return $this->addRoute('DELETE', $path, $handler);
    }

    public function patch(string $path, $handler): self {
        return $this->addRoute('PATCH', $path, $handler);
    }

    private function addRoute(string $method, string $path, $handler): self {
        // Convert {param} placeholders to regex named groups
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = "#^" . rtrim($pattern, '/') . "$#";

        $this->routes[] = [
            'method'  => $method,
            'path'    => $path,
            'pattern' => $pattern,
            'handler' => $handler
        ];
        return $this;
    }

    public function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Normalize URI: strip directory prefixes
        $uri = preg_replace('#^/ecommerce_api#i', '', $uri);
        $uri = preg_replace('#^/ecomerce/backend#i', '', $uri);
        $uri = preg_replace('#^/index\.php#i', '', $uri);
        $uri = '/' . trim($uri, '/');

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                try {
                    $handler = $route['handler'];

                    if (is_callable($handler)) {
                        call_user_func($handler, $params);
                        return;
                    }

                    if (is_array($handler) && count($handler) === 2) {
                        [$controllerName, $action] = $handler;
                        if (!class_exists($controllerName)) {
                            Response::error("Controller {$controllerName} not found", [], 500);
                        }
                        $controller = new $controllerName();
                        if (!method_exists($controller, $action)) {
                            Response::error("Method {$action} not found on {$controllerName}", [], 500);
                        }
                        $controller->$action($params);
                        return;
                    }
                } catch (Throwable $e) {
                    Response::error($e->getMessage(), [
                        'file' => basename($e->getFile()),
                        'line' => $e->getLine()
                    ], 500);
                }
            }
        }

        Response::error("Endpoint not found: [{$method}] {$uri}", [], 404);
    }
}
