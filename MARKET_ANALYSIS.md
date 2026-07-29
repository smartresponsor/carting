# Carting market analysis

Mature commerce platforms treat the cart as a mutable, concurrency-sensitive aggregate rather than a session array. Baseline expectations are stable cart identity, anonymous and authenticated ownership, line mutation, price snapshots, currency consistency, merge behavior, expiry, checkout readiness, and deterministic handoff to order creation.

Advanced maturity adds optimistic concurrency, idempotent mutation and checkout semantics, extension contracts for availability and estimates, lifecycle diagnostics, abandonment and recovery, and neutral API and UI projections. Tax, promotion calculation, inventory truth, payment, committed orders, and shipping must remain in their owning components.

## RC gap assessment

- Strong: explicit responsibility boundary, checkout readiness, neutral output surface, offer and availability contracts, and immutable handoff payload.
- Hardened: aggregate versioning, cumulative availability checking, money and currency invariants, merge child ownership, and one-handoff-per-cart persistence intent.
- Remaining RC blockers: host Doctrine migration and schema validation, concurrent database tests, PHPStan execution through policy, and restoration of a valid Git repository root.

## Competitive direction

Carting can differentiate through a small Symfony-native contract surface, host-neutral rendering data, strict ownership boundaries, and self-hosted extensibility.

Growth must not absorb catalog, inventory, promotion, tax, payment, Ordering, or Shipping engines.
