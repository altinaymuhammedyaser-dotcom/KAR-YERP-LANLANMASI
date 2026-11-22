<?php
/**
 * Akilli Kariyer - Basit yonlendirici
 *
 * API uc noktalari ve modul sayfalari icin basit yonlendirme saglar.
 */

namespace App\Core;

final class Router
{
    /** @var array<string,callable> */
    private array $routes = [];
    private string $prefix = '';

    public function prefix(string $prefix): self
    {
        $this->prefix = rtrim($prefix, '/');
        return $this;
    }

    public function get(string $path, callable $handler): self
    {
        return $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): self
    {
        return $this->add('POST', $path, $handler);
    }

    public function any(string $path, callable $handler): self
    {
        return $this->add('*', $path, $handler);
    }

    private function add(string $method, string $path, callable $handler): self
    {
        $this->routes[strtoupper($method) . ' ' . $this->prefix . $path] = $handler;
        return $this;
    }

    /**
     * Mevcut istegi yonlendirir.
     */
    public function dispatch(?string $uri = null, ?string $method = null): mixed
    {
        $uri = $uri ?? ($_SERVER['REQUEST_URI'] ?? '/');
        $method = strtoupper($method ?? ($_SERVER['REQUEST_METHOD'] ?? 'GET'));

        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = '/' . trim(preg_replace('#^' . preg_quote(BASE_PATH, '#') . '#', '', $path), '/');

        foreach ($this->routes as $route => $handler) {
            [$routeMethod, $routePath] = explode(' ', $route, 2);
            if ($routeMethod !== '*' && $routeMethod !== $method) {
                continue;
            }
            $pattern = '#^' . rtrim($routePath, '/') . '$#';
            if (preg_match($pattern, $path, $m)) {
                return $handler($m, $this->input());
            }
        }

        http_response_code(404);
        return $this->notFound();
    }

    /**
     * Form ve JSON govdesini birlestirir.
     */
    public function input(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }
        $data = $_POST;
        $raw = file_get_contents('php://input');
        if ($raw !== false && $raw !== '') {
            $json = json_decode($raw, true);
            if (is_array($json)) {
                $data = array_merge($data, $json);
            }
        }
        $cache = $data;
        return $data;
    }

    private function notFound(): void
    {
        if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Kaynak bulunamadı.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        http_response_code(404);
        require APP_ROOT . '/includes/error-page.php';
        exit;
    }
}
