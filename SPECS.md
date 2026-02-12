# Atlas Routing: Technical Specifications

## 1. Project Overview
Atlas is a high-performance, modular PHP routing engine designed for professional-grade applications. It prioritizes developer experience, architectural purity, and interoperability through PSR-7 support.

## 2. Technical Requirements
- **PHP Version**: `^8.2`
- **Interoperability**: Strict compliance with PSR-7 (`Psr\Http\Message\ServerRequestInterface`) for request matching.
- **Dependencies**: 
    - `psr/http-message`: For HTTP message interfaces.
    - `phpunit/phpunit` (Dev): For testing.

## 3. Architectural Principles
- **SOLID**: Strict adherence to object-oriented design principles.
- **KISS**: Favoring simple, maintainable solutions over complexity.
- **DRY**: Minimizing duplication through abstraction.
- **YAGNI**: Implementing only requested and necessary functionality.
- **Single Responsibility Principle (SRP)**: Applied at both class and method levels.
- **Inversion of Control (IoC)**: No environment guessing; all configurations must be explicitly injected.
- **Verbose & Expressive Style**: Self-documenting code with clear, descriptive naming.
- **Type Safety**: Mandatory use of PHP 8.2+ type hinting (scalar, union, intersection, DNF).

## 4. Core Routing Features

### 4.1 Route Definition
- Support for standard HTTP methods: `GET`, `POST`, `PUT`, `PATCH`, `DELETE`.
- Fluent, chainable API for route configuration.
- **Example Usage**:
    - `$router->get('/path', 'Handler');`
    - `$router->post('/path', 'Handler');`
- Support for named routes: `->name('route_name')`.
    - **Example**: `$router->get(...)->name('user_list');`
- Reverse routing (URL Generation): `$router->url(string $name, array $parameters = []): string`.
    - Automatically replaces `{{var}}` placeholders.
    - Throws exception if required parameters are missing.
    - **Example**: `$router->url('single_user', ['user_id' => 5]);`

### 4.2 URI Matching & Parameters
- **Syntax**: Double curly braces `{{variable_name}}` for parameter encapsulation.
    - **Example**: `/users/{{user_id}}`
- **Optional Parameters**: 
    - Indicated by a trailing question mark inside the braces: `{{variable_name?}}`.
    - **Example**: `/blog/{{slug?}}`
- **Validation**: 
    - Chaining: `->valid('param', ['rule1', 'rule2'])`
    - Array: `->valid(['param1' => ['rule'], 'param2' => ['rule']])`
    - **Example**: `$router->get(...)->valid('user_id', ['numeric', 'int', 'required']);`
- **Default Values**: 
    - Set via `->default(string $param, mixed $value)`.
    - Providing a default value automatically marks a parameter as optional.
    - **Example**: `$router->get('/blog/{{page}}')->default('page', 1);`
- **Regex Support**: Dynamic matching for complex URI patterns.

### 4.3 Route Groups
- Route groups are **first-class objects**.
- Routes can be added directly to a group instance: `$group->get(...)`.
- Nested grouping with `->group()` method.
- Indefinite nesting support.
- Recursive merging of prefixes and middleware.
- Support for parameter validation at the group level.
- **Example**:
    ```php
    $group = $router->group(['prefix' => '/users/{{user_id}}'])->valid('user_id', ['int']);
    $group->get('/posts', 'PostController@index');
    ```

### 4.4 Redirection
- Native support for redirects: `$router->redirect(string $path, string $destination, int $status = 302)`.

### 4.5 Route Attributes (Metadata)
- "Tag-along" data for routes using `->attr(string $key, mixed $value)` or `->meta(array $data)`.
- Used for non-routing logic (e.g., Breadcrumbs, ACL, UI hints).
- Accessible via the matched `Route` object.

## 5. Modular Routing

### 5.1 Discovery & Structure
- **Explicit Injection**: `modules_path` (string or array) and `routes_file` (default `routes.php`) must be provided.
- **Optional `modules_glob`**: Custom pattern for module discovery (e.g., `modules/*/Http/routes.php`).
- **Convention**: Standard location is `src/Modules/{ModuleName}/routes.php`.
- **Automatic Registration**: The `module('ModuleName', 'prefix')` method triggers discovery.
- **Example Configuration**:
    ```php
    $router = new Router([
        'modules_path' => '/abs/path/to/modules',
        'routes_file'  => 'routes.php'
    ]);
    ```
- **Exception**: Throws `MissingConfigurationException` if `module()` is called without `modules_path`.

### 5.2 Inheritance
- Modules inherit base URL prefixes and module-level middleware.
- Conflict resolution mechanisms for overlapping route names or paths.

## 6. Advanced Functionality

### 6.1 Subdomain Routing
- Ability to constrain routes or groups to specific subdomains.

### 6.2 Internationalization (i18n)
- Localized route support for translated URI segments.

### 6.3 Performance & Caching
- Implementation of route caching to optimize matching in large-scale applications.
- Optimized, serializable structure for production environments.

### 6.4 Error Handling
- Localized and global fallback handlers: `fallback(callable|string $handler)`.
- Support for custom 404 responses at the module or group level.

## 7. CLI & Tooling

### 7.1 Programmatic Inspector API
- `getRoutes()`: Returns `iterable<RouteDefinition>` containing method, path, name, handler, middleware, module, and group data.
- `match(string $method, string $url, array $server = [])`: Returns a `MatchResult` for debugging.
- `toArray()` / `jsonSerialize()`: For structured output.

### 7.2 CLI Commands (Optional)
- `route:list`: 
    - Filters: `--method`, `--name`, `--path`, `--module`, `--domain`, `--json`.
    - Output: method(s), path template, name, handler, middleware, module, group prefix.
- `route:test <METHOD> <URL>`: 
    - Options: `--host`, `--accept-language`.
    - Diagnostics: name, template, handler, params, middleware chain, module/group context, timing.
    - Exit codes: `0` (match found), `2` (no match), `1` (error).

## 8. Quality Assurance
- **Testing**: Mandatory Unit and Integration tests using PHPUnit.
- **Documentation**: PHPDoc for all public members; updated README and examples.
- **Regression**: Every bug fix requires a corresponding regression test.
