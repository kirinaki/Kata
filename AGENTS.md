# AGENTS.md

Guidelines for AI agents and contributors working in this repository.

## Project overview

Kata is a Laravel 12 package (composer package `kirinaki/kata`) that generates
self-contained modules under a host app's `modules/` directory from predefined
scaffolds: `basic`, `core`, `frontend-ssr`, and `frontend-spa`.

- Language of code and documentation: **PHP 8.2+** for the backend, TypeScript and
  Blade for the frontend scaffolds.
- Public entry point: `KataServiceProvider` (package discovery).
- Core orchestration classes live under `src/Modules/` and `src/Console/`.

## Code style

### Guard clauses

Prefer early returns that exit a method as soon as a precondition fails, keeping
the happy path flat and at the end of the method.

```php
// Bad: nested ifs and an else
if ($condition) {
    // ...
} else {
    // ...
}

// Good: guard clause exits early
if (! $condition) {
    return;
}

// ...
```

### SOLID

Follow SOLID principles:

- One class, one responsibility (`ModuleGenerator`, `ModuleRemover`, `ModuleName`,
  `StubRenderer`, `ProviderRegistrar`, ...).
- Depend on abstractions, not concretions (see `Reporter`, `ComposerRunner`,
  `ProviderRegistry` interfaces and their `ConsoleReporter`,
  `ProcessComposerRunner`, `ProviderRegistrar` implementations).
- Wire dependencies through the container in `KataServiceProvider::register()`;
  do not instantiate collaborators inside constructors.
- Adding a new scaffold means: create stubs under `stubs/{scaffold}/`, add the
  case to the `Scaffold` enum, and cover it in its `match` methods.

## Tests

- Unit tests (`tests/Unit/`): plain PHPUnit with test doubles
  (`FakeComposerRunner`, `FakeProviderRegistry`, `SpyReporter`). No Laravel app
  required.
- Integration tests (`tests/Feature/`): run the real `kata:*` commands against a
  Testbench application (`Orchestra\Testbench\TestCase`) with
  `getPackageProviders()`.
- Add unit or integration tests for any new behavior, fix, or refactor, following
  the existing fixtures and patterns.
- Run the suite before finishing a change:

```bash
./vendor/bin/phpunit
```

## Documentation

- Document important changes: new commands, new scaffolds, changed behavior, and
  breaking changes must be reflected in the README (and in commit messages).
- Keep placeholders and scaffold tables in the README in sync with the code.
- The package README is written in Spanish; code comments and commit messages may
  be in Spanish or English, but keep them consistent.

## When in doubt

- If you do not know how to do something, research the official documentation on
  the internet before implementing it.
- If you have doubts about requirements, scope, or the right approach, ask for
  clarification before continuing.

## Conventions

- Follow the existing PSR-4 layout: `Kata\ => src/`,
  `Kata\Tests\ => tests/`.
- Do not add code comments unless they add value (explain *why*, not *what*).