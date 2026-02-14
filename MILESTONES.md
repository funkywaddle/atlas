# Atlas Routing: Milestones

This document outlines the phased development roadmap for the Atlas Routing engine, based on the `SPECS.md`.

## Rules of Development
- **One at a Time**: Milestones must be implemented one at a time. Do not move to the next milestone until the current one is fully completed and verified.
- **Definition of Done**: A milestone is considered complete only when:
    - The full suite of tests passes.
    - There are no deprecation warnings.
    - There are no errors.
    - There are no failures.
- **Manual Transition**: Do not automatically proceed to the next milestone without explicit verification of the current milestone's completion.

## Milestone 1: Foundation & Core Architecture
*Goal: Establish the base classes, configuration handling, and the internal route representation.*
- [x] Define `Route` and `RouteDefinition` classes (SRP focused).
- [x] Implement the `Router` class with Inversion of Control (DI for config).
- [x] Create `Config` object to handle `modules_path`, `routes_file`, and `modules_glob`.
- [x] Implement `MissingConfigurationException`.
- [x] Setup Basic PHPUnit suite with a "Hello World" route test.

## Milestone 2: Basic URI Matching & Methods
*Goal: Implement the matching engine for standard HTTP methods and static URIs.*
- [x] Implement fluent methods: `get()`, `post()`, `put()`, `patch()`, `delete()`.
- [x] Build the URI Matcher for static paths.
- [x] Support for PSR-7 `ServerRequestInterface` type-hinting in the matcher.
- [x] Implement basic Error Handling (Global 404).

## Milestone 3: Comprehensive Test Coverage
*Goal: Bring the testing suite up to standards by covering untested core functionality and edge cases.*
- [x] Implement unit tests for `RouteGroup` to verify prefixing and registration logic.
- [x] Implement integration tests for `Router::module()` using mock/temporary files for discovery.
- [x] Expand `Router::url()` tests to cover parameter replacement and error cases (missing parameters).
- [x] Add unit tests for `Router::fallback()` and its handler execution.
- [x] Implement comprehensive unit tests for `Config` class methods and interface implementations.
- [x] Add regression tests for `MissingConfigurationException` in module discovery.

## Milestone 4: Architectural Refinement (SRP & SOLID)
*Goal: Decompose the Router into focused components for better maintainability and testability.*
- [x] Extract route storage and retrieval into `RouteCollection`.
- [x] Extract matching logic into a dedicated `RouteMatcher` class.
- [x] Extract module discovery and loading logic into `ModuleLoader`.
- [x] Refactor `Router` to act as a Facade/Orchestrator delegating to these components.
- [x] Update existing tests to maintain compatibility with the refactored `Router` architecture.

## Milestone 5: Code Quality & Error Standardization
*Goal: Eliminate duplication and unify the exception handling strategy.*
- [x] Create `PathHelper` to centralize and standardize path normalization.
- [x] Consolidate `NotFoundRouteException` and `RouteNotFoundException` into a single expressive exception.
- [x] Refactor `matchOrFail()` to utilize `match()` to eliminate logic duplication (DRY).
- [x] Update and expand the test suite to reflect centralized normalization and consolidated exceptions.

## Milestone 6: Fluent Configuration & Dynamic Matching
*Goal: Implement the complete fluent interface and support for dynamic URIs.*
- [x] Add fluent configuration methods to `RouteDefinition` (`name`, `valid`, `default`, `middleware`, `attr`).
- [x] Implement `{{parameter}}` and `{{parameter?}}` syntax support in the matching engine.
- [x] Implement regex generation for dynamic URI patterns.
- [x] Enable nested `RouteGroup` support with recursive merging of prefixes and middleware.
- [x] Create comprehensive tests for dynamic matching, parameter extraction, and nested group logic.

## Milestone 7: Documentation & Quality Assurance
*Goal: Ensure professional-grade quality through comprehensive docs and tests.*
- [x] Conduct a full PHPDoc audit and ensure 100% documentation coverage.
- [x] Add integration tests for nested groups and modular loading.
- [x] Add regression tests for consolidated exceptions and path normalization.
- [x] Verify that all existing and new tests pass with 100% success rate.

## Milestone 8: Parameters & Validation
*Goal: Support for dynamic URIs with the `{{var}}` syntax and parameter validation.*
- [x] Implement `{{variable_name}}` and `{{variable_name?}}` (optional) parsing.
- [x] Add `valid()` method (chaining and array support).
- [x] Add `default()` method and logic for implicit optional parameters.
- [x] Support for dynamic/regex-based segment matching.
- [x] Add unit tests for parameter parsing, optionality, and validation rules.

## Milestone 9: Route Groups & First-Class Objects
*Goal: Implement recursive grouping and the ability to treat groups as functional objects.*
- [x] Implement `group()` method with prefix/middleware inheritance.
- [x] Ensure Route Groups are first-class objects (routes can be added directly to them).
- [x] Implement indefinite nesting and recursive merging of properties.
- [x] Support group-level parameter validation.
- [x] Add tests for nested group inheritance and group-level validation logic.

## Milestone 10: Modular Routing
*Goal: Automate route discovery and registration based on directory structure.*
- [x] Implement the `module()` method.
- [x] Build the discovery logic for `src/Modules/{Name}/routes.php`.
- [x] Implement middleware/prefix inheritance for modules.
- [x] Conflict resolution for overlapping module routes.
- [x] Add integration tests for module discovery and route registration.

## Milestone 11: Advanced Capabilities & Interoperability
*Goal: Add specialized routing features and full PSR-7 compatibility.*
- [x] Implement `redirect()` native support.
- [x] Add Route Attributes/Metadata (`attr()` and `meta()`).
- [x] Implement `url()` generation (Reverse Routing).
- [x] Add `fallback()` support at group/module levels.
- [x] Implement Subdomain Constraints and i18n support.
- [x] Add tests for redirection, attributes, subdomain constraints, and i18n.

## Milestone 12: Tooling & Inspector API
*Goal: Provide developer tools for debugging and inspecting the routing table.*
- [x] Develop the Programmatic Inspector API (`getRoutes()`, `match()`).
- [x] Build the `route:list` CLI command.
- [x] Build the `route:test` CLI command with diagnostic output.
- [x] Ensure JSON output support for tooling integration.
- [x] Add tests for Inspector API and CLI command outputs.

## Milestone 13: Performance & Optimization
*Goal: Finalize the engine with caching and production-ready performance.*
- [x] Implement Route Caching (serializable optimized structure).
- [x] Performance benchmarking and matcher optimization.
- [x] Final Documentation (KDoc, README, Examples).
- [x] Implement performance regression tests and benchmark verification.
- [x] Release v1.0.0.
