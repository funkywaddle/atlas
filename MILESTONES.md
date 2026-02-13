# Atlas Routing: Milestones

This document outlines the phased development roadmap for the Atlas Routing engine, based on the `SPECS.md`.

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

## Milestone 3: Parameters & Validation
*Goal: Support for dynamic URIs with the `{{var}}` syntax and parameter validation.*
- [ ] Implement `{{variable_name}}` and `{{variable_name?}}` (optional) parsing.
- [ ] Add `valid()` method (chaining and array support).
- [ ] Add `default()` method and logic for implicit optional parameters.
- [ ] Support for dynamic/regex-based segment matching.

## Milestone 4: Route Groups & First-Class Objects
*Goal: Implement recursive grouping and the ability to treat groups as functional objects.*
- [ ] Implement `group()` method with prefix/middleware inheritance.
- [ ] Ensure Route Groups are first-class objects (routes can be added directly to them).
- [ ] Implement indefinite nesting and recursive merging of properties.
- [ ] Support group-level parameter validation.

## Milestone 5: Modular Routing
*Goal: Automate route discovery and registration based on directory structure.*
- [ ] Implement the `module()` method.
- [ ] Build the discovery logic for `src/Modules/{Name}/routes.php`.
- [ ] Implement middleware/prefix inheritance for modules.
- [ ] Conflict resolution for overlapping module routes.

## Milestone 6: Advanced Capabilities & Interoperability
*Goal: Add specialized routing features and full PSR-7 compatibility.*
- [ ] Implement `redirect()` native support.
- [ ] Add Route Attributes/Metadata (`attr()` and `meta()`).
- [ ] Implement `url()` generation (Reverse Routing).
- [ ] Add `fallback()` support at group/module levels.
- [ ] Implement Subdomain Constraints and i18n support.

## Milestone 7: Tooling & Inspector API
*Goal: Provide developer tools for debugging and inspecting the routing table.*
- [ ] Develop the Programmatic Inspector API (`getRoutes()`, `match()`).
- [ ] Build the `route:list` CLI command.
- [ ] Build the `route:test` CLI command with diagnostic output.
- [ ] Ensure JSON output support for tooling integration.

## Milestone 8: Performance & Optimization
*Goal: Finalize the engine with caching and production-ready performance.*
- [ ] Implement Route Caching (serializable optimized structure).
- [ ] Performance benchmarking and matcher optimization.
- [ ] Final Documentation (KDoc, README, Examples).
- [ ] Release v1.0.0.
