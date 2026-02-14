# Atlas Routing

## Development Standards & Conventions

To ensure Atlas remains a high-quality, maintainable, and professional-grade library, all development must adhere to the following standards:

### Core Requirements
- **PHP Version**: `^8.2`
- **Execution Policy**:
    - **Sequential Implementation**: Milestones are implemented one at a time.
    - **No Auto-Advance**: Do not automatically move to the next milestone.
    - **Strict Completion**: A milestone is NOT complete until the full suite of tests passes with zero deprecation warnings, zero errors, and zero failures.
- **Principles**: 
    - **SOLID**: Strict adherence to object-oriented design principles.
    - **KISS** (Keep It Simple, Stupid): Prefer simple solutions over clever ones.
    - **DRY** (Don't Repeat Yourself): Minimize duplication by abstracting common logic.
    - **YAGNI** (You Ain't Gonna Need It): Avoid over-engineering; only implement what is actually required.

### Coding Style & Architecture
- **Verbose Coding Style**: Code should be expressive and self-documenting. Use descriptive variable and method names. Favor clarity over brevity.
- **Single Responsibility Principle (SRP)**:
    - **Classes**: Each class must have one, and only one, reason to change.
    - **Methods**: Each method should perform a single, well-defined task. If a method is doing too much, it should be refactored into smaller, focused methods.
- **Type Safety**: Strictly use PHP 8.2+ type hinting (including union types, intersection types, and DNF types where appropriate) for all properties, parameters, and return values.
- **Interoperability**: Prioritize PSR (PHP Standard Recommendation) compliance where applicable to ensure the library can be used across the wider PHP ecosystem.

### Documentation & Quality Assurance
- **Well Documented**: 
    - Every public class and method must have comprehensive PHPDoc blocks.
    - Complex internal logic should be explained with inline comments.
    - Maintain an up-to-date `README.md` and detailed usage examples.
- **Fully Tested**:
    - Aim for high test coverage (Unit and Integration tests).
    - Every bug fix must include a regression test.
    - Every new feature must be accompanied by relevant tests.
    - Use PHPUnit for the testing suite.

## List of needed functionality
| Functionality | Description                                                                                                                  |
|:--------------|:-----------------------------------------------------------------------------------------------------------------------------|
|Route Definition| Ability to define routes using a simple and intuitive syntax, supporting various HTTP methods (GET, POST, etc.).             |
|URI Matching| Efficient matching of incoming requests to defined routes, considering parameterized and optional segments.                  |
|Route Parameters| Support for capturing parameters from the URI, with options for required and optional parameters.                            |
|Route Groups| Grouping of multiple routes under a common prefix, with shared middleware or attributes.                                     |
|Middleware Support| Ability to attach middleware to routes or route groups to handle pre-processing of requests (authentication, logging, etc.). |
|Route Names| Allow routes to be named for easy reference in redirects and generating URLs.                                                |
|Default Values| Support for setting default values for route parameters, enhancing flexibility.                                              |
|Subdomain Routing| Ability to handle subdomains and route based on them.                                                                        |
|HTTP Method Constraints| Conditional routing based on HTTP methods, allowing different behavior for the same endpoint.                                |
|Route Caching| Implement caching of route definitions for performance optimization, especially on large applications.                       |
|Dynamic Route Matching| Support for dynamic or regex-based routes for more complex matching scenarios.                                               |
|Custom Route Filters| Allow developers to define custom filters for route validation before handling requests.                                     |
|Error Handling|Graceful handling of routing errors, including 404 errors and other edge cases, with customizable responses (supports global and group/module level fallbacks).|
|URL Generation|Ability to generate URLs from named routes, automatically injecting required parameters.|
|Redirection Routes|Support for defining redirect routes directly in the router (e.g., 301/302 redirects).|
|Route Attributes|Ability to attach custom metadata/attributes to routes for use in middleware, views, or breadcrumbs.|
|Internationalization (i18n)| Support for localized routes, allowing route names to be translated based on user preferences.                               |
|Debugging Tools| Offer debugging options for inspecting routing behavior and matching, including generating route documentation.              |
|PSR-7 Support| Support for PSR-7 HTTP Message interfaces for incoming server requests and outgoing responses.                                |

## Notes
### Implementation Considerations
1. Performance: Prioritize performance in URI matching and route resolution. Efficient algorithms for these processes are crucial for responsive applications, especially as route complexity grows.
2. Flexibility and Extensibility: Ensure the package is extensible so developers can easily add custom functionality or override default behaviors.
3. Middleware Chain Handling: Develop a robust middleware management system to ensure middleware can be easily applied to individual routes or groups while maintaining execution order.
4. Documentation & Examples: Provide comprehensive documentation and usage examples to encourage adoption and understanding by potential users.
5. Community Feedback: Engage with the community to gather insights and feedback during development to align more closely with developer needs and preferences.

By focusing on these components and considerations, your custom routing package can offer competitive features that provide value and flexibility, aiming to match or surpass existing solutions like Symfony’s routing component.


Should support modular routing as well as normal routing.

## Key Components of Modular Routing
| Feature | Description1 |
|:--------|:-------------|
|Module Registration|A `module()` method that facilitates the registration of modules along with their base URL.|
|Nested Routes|The ability to automatically load and register routes from a specified nested routes file for each module.|
|Expected File Structure|Define where the package expects to find the module's routing file (e.g., `src/Modules/{Name}/routes.php`).|
|Route Files|Each module should have a designated routing file (e.g., `routes.php`) to define its unique routes.|
|Route Prefixing|Automatically prefix routes from the module’s route file with the base URL specified during registration.|
|Dynamic URL Segmentation|Support for dynamic URL segments in module routes, allowing flexibility in defining routes.|
|Middleware Inheritance|Ability to apply middleware at the module level, affecting all routes within that module.|
|Conflict Resolution|Mechanisms to prevent or handle conflicts between route names and paths from different modules.|

### Benefits of Modular Routing
- Separation of Concerns: By organizing routes into modules, you create a clean separation of different functional areas, making it easier to manage and scale.
- Ease of Maintenance: Individual teams can work on their respective modules without interfering with others, streamlining development and testing.
- Clear Expectations: Establishing a standardized location for route files improves discovery and automated loading.
- Reusability: Modules can be easily reused in different projects or contexts, enhancing efficiency.

Implementing modular routing in this way will not only enhance the functionality of your routing package but also foster better practices among developers using your framework. This feature could set your framework apart by offering a clean and efficient way to manage routing for larger applications.

## Ending Thoughts
- Atlas will have a method per HTTP Method (`get`, `post`, `put`, `patch`, `delete`) (standard routing methods)
- Atlas will have a module `method` (Atlas will search the indicated module for a routes file and import those routes into a `group` with a prefix indicated by URL route prefix)
- Atlas will have a method `group` (be able to group routes by URL section or middleware or etc)
  - `group` prefix parameter supports url parameters
  - `group` method supports parameter validation
  - `group` methods can be nested indefinitely, with prefixes and middleware merging recursively.
  - route groups are first class objects
    - routes get added to route groups
      - ``` 
        $group1 = $router->group(['prefix' => '/users/{{user-id}}'])->valid(['user_id' => ['numeric', 'int', 'required']]);
        $group1->get('/posts/{{post_id}}', 'PostController@show')->valid(['post_id' => ['numeric', 'int', 'required']]);
        ```
- Atlas will have Middleware support
- Atlas will have route parameters
  - indicated by `{{var}}` 
    - example: `https://example.com/users/{{user_id}}`
  - optional parameters indicated by `{{var?}}`
    - example: `https://example.com/blog/{{slug?}}`
- Atlas will have route parameter validation options
  - `$router->get('/users/{{user_id}}', 'UserController::__invoke')->valid('user_id', ['numeric','int','required']);`
  - Supporting for multiple parameters each having their own validation
    - associative array syntax
      - `$router->get('/users/{{user_id}}/posts/{{post_id}}', 'PostController@show')->valid(['user_id' => ['numeric', 'int', 'required'], 'post_id' => ['numeric', 'int', 'required']]);`
    - chaining syntax
      - `$router->get('/users/{{user_id}}/posts/{{post_id}}', 'PostController@show')->valid('user_id', ['required'])->valid('post_id', ['required']);`
- Atlas will have Default Values
  - `$router->get('/blog/{{page}}')->default('page', 1);`
  - Providing a default value automatically marks the parameter as optional.
- Atlas will have route names
  - `$router->get('/users/{{user_id}}', 'UserController::__invoke')->name('single_user');`
  - `$router->get('/users', 'UserController::__invoke')->name('user_list');`
- Atlas will have Error handling
  - `fallback(callable|string $handler)` method to define a "catch-all" for a group, module, or global scope.
  - Allows for domain or module-specific 404 responses.
- Atlas will have URL Generation
  - `$router->url(string $name, array $parameters = []): string`
  - Automatically replaces `{{var}}` placeholders in the route path with provided values.
  - Throws exception if required parameters are missing.
- Atlas will have Redirect support
  - `$router->redirect(string $path, string $destination, int $status = 302)`
  - Provides a shortcut for common URL redirections without needing a custom handler.
- Atlas will have Route Attributes (Metadata)
  - `$router->get(...)->attr(string $key, mixed $value)`
  - `$router->get(...)->meta(array $data)`
  - Allows attaching "tag-along" data to routes (e.g., breadcrumbs, permissions, UI hints).
  - Attributes are accessible via the matched `Route` object, keeping handler signatures clean.
- Atlas will support i18n
- Atlas will have PSR-7 Support
  - Atlas will type-hint `Psr\Http\Message\ServerRequestInterface` for request matching.
  - This ensures interoperability with any PSR-7 compliant framework or library.
  - No built-in PSR-7 implementation; Atlas expects the consumer to provide one (DI).
- Atlas will have debugging tools


## Configuration & Dependency Injection

Atlas follows the principle of **Inversion of Control**. It does not read environment variables or attempt to "guess" the application structure. All configuration must be explicitly injected via the constructor or setter methods. This ensures Atlas remains a pure library, easy to test, and agnostic of the framework it's used in.

### Router Configuration

The `Router` constructor and `setOptions()` method accept a configuration array or a dedicated `Config` object.

- `modules_path`
  - Purpose: The base directory where Atlas looks for modules when `module()` is called.
  - Type: `string` or `array`
  - Example: `src/Modules` or `['src/Modules', 'app/Features']`
  - Resolution: For `module('Blog')`, Atlas searches for `{modules_path}/Blog/{routes_file}`.

- `routes_file`
  - Purpose: The filename Atlas expects for module route definitions.
  - Type: `string`
  - Default: `routes.php`

- `modules_glob` (optional)
  - Purpose: A custom glob pattern for module discovery, bypassing the standard directory convention.
  - Example: `modules/*/Http/routes.php`

### Usage Examples

```php
// Standard injection via constructor
$router = new Router([
    'modules_path' => '/abs/path/to/modules',
    'routes_file'  => 'routes.php'
]);

// Fluent configuration via setters
$router->setModulesPath('/abs/path/to/modules')
       ->setRoutesFile('custom-routes.php');
```

Implementation guidance:
- Atlas should throw a `MissingConfigurationException` if `module()` is called but `modules_path` has not been set.
- By avoiding `getenv()`, the package remains compatible with any environment (CLI, Web, RoadRunner, etc.) and is fully deterministic in tests.

## CLI & Tooling

Atlas should provide a small, optional CLI—or at minimum a programmatic inspector API that a framework CLI can call—to surface routing information.

### Commands

- route:list
  - Purpose: List all known routes (including those imported via `module()` and `group()`), useful for debugging and documentation.
  - Options:
    - `--method=GET|POST|...`
    - `--name=<regex>` filter by route name
    - `--path=<glob|regex>` filter by path template
    - `--module=<name>` filter routes originating from a specific module
    - `--domain=<host>` filter by domain/subdomain when supported
    - `--json` output machine‑readable JSON
  - Output fields (typical): method(s), path template (e.g., `/users/{{user_id}}`), name, handler, middleware stack, module, group prefix, i18n locale (if applicable).

- route:test <METHOD> <URL> [--host=sub.example.com] [--accept-language=en]
  - Purpose: Quickly test which route matches a given request.
  - Behavior: Runs the matcher against the compiled route table and prints the result.
  - Output on match:
    - Matched route name (if any)
    - Path template (e.g., `/users/{{user_id}}/posts/{{post_id}}`)
    - Handler/controller target
    - Extracted params (e.g., `{ user_id: 42, post_id: 7 }`)
    - Applied middleware chain (resolved order)
    - Module and group context (if applicable)
    - Locale/domain matched info (if enabled)
    - Match time diagnostics
  - Exit codes: `0` match found; `2` no match; `1` error/invalid input.

### Integration Patterns

- Standalone CLI: Ship a thin `atlas` binary (PHP entrypoint) providing `route:list` and `route:test` built on top of the public inspector API.
- Framework integration: Expose an `Inspector` or `RouteRegistry` API:
  - `getRoutes(): iterable<RouteDefinition>` returning structured route data
  - `match(string $method, string $url, array $server = []): MatchResult` used by `route:test`
  - `toArray()/jsonSerialize()` to support `--json` output
- Frameworks can wire these into their own CLIs (e.g., `php artisan atlas:route:list`).

### Notes for Implementation

- Route caching should also cache an optimized, serializable structure used by both the runtime matcher and `route:list` for instant output.
- Validation rule summaries for `valid()` should be available in the inspector output to aid debugging.
- Keep the CLI optional: the package must work without it; frameworks can depend on the inspector API only.
