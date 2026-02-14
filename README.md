# Atlas Routing

A high-performance, modular PHP routing engine designed for professional-grade applications. It prioritizes developer experience, architectural purity, and interoperability through PSR-7 support.

## Features

- **Fluent API**: Expressive and chainable route definitions.
- **Dynamic Matching**: Support for `{{parameters}}` and `{{optional?}}` segments.
- **Parameter Validation**: Strict validation rules (numeric, alpha, regex, etc.).
- **Route Groups**: Recursive grouping with prefix and middleware inheritance.
- **Modular Routing**: Automatic route discovery from modules.
- **Reverse Routing**: Safe URL generation with parameter validation.
- **PSR-7 Support**: Built on standard HTTP message interfaces.
- **Advanced Capabilities**: Subdomain constraints, i18n support, and redirects.
- **Developer Tooling**: Programmatic Inspector API and CLI tools.
- **Performance**: Optimized matching engine with route caching support.

## Installation

```bash
composer require getphred/atlas
```

## Basic Usage

```php
use Atlas\Router\Router;
use Atlas\Config\Config;
use GuzzleHttp\Psr7\ServerRequest;

// 1. Setup Configuration
$config = new Config([
    'modules_path' => __DIR__ . '/src/Modules',
]);

// 2. Initialize Router
$router = new Router($config);

// 3. Define Routes
$router->get('/users', function() {
    return 'User List';
})->name('users.index');

$router->get('/users/{{id}}', function($id) {
    return "User $id";
})->name('users.show')->valid('id', 'numeric');

// 4. Match Request
$request = ServerRequest::fromGlobals();
$route = $router->match($request);

if ($route) {
    $handler = $route->getHandler();
    // Execute handler...
} else {
    // 404 Not Found
}
```

## Route Groups

```php
$router->group(['prefix' => '/api', 'middleware' => ['auth']])->group(function($group) {
    $group->get('/profile', 'ProfileHandler');
    $group->get('/settings', 'SettingsHandler');
});
```

You can also save a route group to a variable for more flexible route definitions:

```php
$api = $router->group(['prefix' => '/api']);

$api->get('/users', 'UserIndexHandler');
$api->post('/users', 'UserCreateHandler');
```

## Performance & Caching

For production environments, you can cache the route collection:

```php
if ($cache->has('routes')) {
    $routes = unserialize($cache->get('routes'));
    $router->setRoutes($routes);
} else {
    // Define your routes...
    $cache->set('routes', serialize($router->getRoutes()));
}
```

## CLI Tools

Atlas comes with a CLI tool to help you debug your routes:

```bash
# List all routes
./atlas route:list

# Test a specific request
./atlas route:test GET /users/5
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
