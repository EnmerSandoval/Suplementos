<?php
/**
 * Router simple para manejo de rutas
 */

class Router {
    private $routes = [];
    private $notFoundCallback;

    /**
     * Agregar ruta GET
     */
    public function get($path, $callback) {
        $this->addRoute('GET', $path, $callback);
    }

    /**
     * Agregar ruta POST
     */
    public function post($path, $callback) {
        $this->addRoute('POST', $path, $callback);
    }

    /**
     * Agregar ruta PUT
     */
    public function put($path, $callback) {
        $this->addRoute('PUT', $path, $callback);
    }

    /**
     * Agregar ruta DELETE
     */
    public function delete($path, $callback) {
        $this->addRoute('DELETE', $path, $callback);
    }

    /**
     * Agregar ruta genérica
     */
    private function addRoute($method, $path, $callback) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'callback' => $callback
        ];
    }

    /**
     * Definir callback para ruta no encontrada
     */
    public function notFound($callback) {
        $this->notFoundCallback = $callback;
    }

    /**
     * Ejecutar el router
     */
    public function run() {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = $_SERVER['REQUEST_URI'];

        // Remover query string
        if (false !== $pos = strpos($requestUri, '?')) {
            $requestUri = substr($requestUri, 0, $pos);
        }

        // Remover trailing slash
        $requestUri = rtrim($requestUri, '/');

        foreach ($this->routes as $route) {
            $pattern = $this->convertPathToRegex($route['path']);

            if ($route['method'] === $requestMethod && preg_match($pattern, $requestUri, $matches)) {
                array_shift($matches); // Remover el match completo

                // Llamar al callback con los parámetros extraídos
                call_user_func_array($route['callback'], $matches);
                return;
            }
        }

        // Ruta no encontrada
        if ($this->notFoundCallback) {
            call_user_func($this->notFoundCallback);
        } else {
            errorResponse('Ruta no encontrada', 404);
        }
    }

    /**
     * Convertir path a expresión regular
     */
    private function convertPathToRegex($path) {
        // Convertir {id} a (\d+) y {param} a ([^/]+)
        $pattern = preg_replace('/\{(\w+)\}/', '([^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    /**
     * Obtener datos del body de la petición
     */
    public static function getRequestBody() {
        $body = file_get_contents('php://input');
        return json_decode($body, true) ?? [];
    }

    /**
     * Obtener parámetros de query string
     */
    public static function getQueryParams() {
        return $_GET;
    }
}
