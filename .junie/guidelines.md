# Atlas Routing: Development Guidelines

These guidelines ensure that all development by AI agents remains consistent with the project's standards for quality, maintainability, and architectural purity.

## 1. Execution Policy (CRITICAL)
- **Sequential Implementation**: Milestones defined in `MILESTONES.md` MUST be implemented one at a time.
- **No Auto-Advance**: Do not automatically move to the next milestone. Stop and wait for verification or explicit instruction after completing a milestone.
- **Strict Completion (Definition of Done)**: A milestone is NOT complete until:
    - The full suite of tests passes.
    - Zero deprecation warnings.
    - Zero errors.
    - Zero failures.

## 2. Core Requirements
- **PHP Version**: `^8.2`
- **Principles**:
    - **SOLID**: Strict adherence to object-oriented design principles.
    - **KISS**: Prefer simple solutions over clever ones.
    - **DRY**: Minimize duplication by abstracting common logic.
    - **YAGNI**: Avoid over-engineering; only implement what is actually required.

## 3. Coding Style & Architecture
- **Verbose Coding Style**: Code must be expressive and self-documenting. Use descriptive variable and method names.
- **Single Responsibility Principle (SRP)**:
    - **Classes**: Each class must have one, and only one, reason to change.
    - **Methods**: Each method should perform a single, well-defined task.
- **Type Safety**: Strictly use PHP 8.2+ type hinting for all properties, parameters, and return values.
- **Interoperability**: Prioritize PSR compliance (especially PSR-7 for HTTP messages).

## 4. Documentation & Quality Assurance
- **Well Documented**: Every public class and method must have comprehensive PHPDoc blocks.
- **Fully Tested**:
    - Aim for high test coverage.
    - Every bug fix must include a regression test.
    - Every new feature must be accompanied by relevant tests.
    - Use PHPUnit for the testing suite.
