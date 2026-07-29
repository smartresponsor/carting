# Carting RC hardening milestone

## RC-critical track

1. Enforce cart and cart-item invariants at construction and mutation boundaries.
2. Validate resulting quantity, snapshot identity, currency, and non-negative money before persistence.
3. Preserve Doctrine aggregate ownership during guest-to-owner merge by copying item snapshots.
4. Prevent duplicate checkout handoffs with optimistic locking on carts and a database uniqueness constraint per cart.
5. Keep Carting free of Cruding routes and controllers and keep Ordering, pricing, inventory, tax, promotion, payment, and shipping ownership outside this repository.
6. Gate RC with PHPUnit, PHPStan, Composer validation and audit, Doctrine mapping and schema validation in a host application, and a clean tracked repository.

## Growth track

1. Stable mutation command and result API with idempotency keys supplied by host HTTP adapters.
2. First-class adjustment aggregation through provider contracts while keeping calculation engines external.
3. Cart recovery, expiry sweeps, abandonment diagnostics, and lifecycle metrics.
4. Host-facing OpenAPI examples and richer neutral surface states.
5. Load and concurrency tests against the host database and queue topology.

## Exit criteria

- Invalid currency, negative price, cross-cart child ownership, duplicate checkout, and stale concurrent conversion cannot persist.
- Checkout handoff remains a Carting output; committed order creation remains Ordering responsibility.
- Repository documentation distinguishes Carting-owned business routes from generic Cruding routes, and HTTP entrypoints live in the canonical Controller layer.
- All local package gates and host Doctrine validation are green.

## Current state
