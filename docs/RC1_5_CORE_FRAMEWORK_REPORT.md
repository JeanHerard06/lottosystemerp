# Lotto ERP Enterprise — RC1.5 Core Framework

## Objective

Introduce a small, dependency-free application core without forcing an immediate rewrite of legacy pages. The new framework can be adopted module by module while the existing application remains functional.

## Added components

- `Application`: application entry point and service registry.
- `Container`: dependency injection, singleton bindings, factory bindings and automatic constructor resolution.
- `Config`: dot-notation configuration access.
- `Request`: normalized HTTP input and JSON body handling.
- `Router`: lightweight GET/POST route registration and dispatch.
- `EventDispatcher`: decoupled domain event listeners.
- `Logger`: JSON-lines logs with sensitive values redacted.
- `ContainerInterface`: stable contract for future services and modules.
- `bootstrap/app.php`: reusable bootstrap for new endpoints.

## Compatibility strategy

The legacy global-class autoloading behavior is preserved. New components do not replace existing routes automatically. Teams can migrate one endpoint at a time through `bootstrap/app.php`.

## Validation

Run:

```bash
php scripts/core_architecture_check.php
```

This validates bootstrap, singleton resolution, event dispatch and router dispatch.

## Next migration candidates

1. Reports module
2. Notifications module
3. API v1 endpoints
4. New administrative pages

Existing financial and lottery calculations should remain in their established services and be injected through the container instead of duplicated.
