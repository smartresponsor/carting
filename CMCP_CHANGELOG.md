# CMCP Execution Journal

## 2026-09-14 — RC hardening baseline

### Reconnaissance read

- Carting: `AGENTS.md`, `README.md`, `composer.json`, `composer.prod.json`, architecture AsciiDoc, core entities, controller, repository, checkout/mutation services, Symfony wiring, PHPUnit/PHPStan/PHP-CS-Fixer configuration, and current test inventory.
- Required runtime contour: Objecting, Cruding, Viewing, and Interfacing `AGENTS.md`, `README.md`, `composer.json`, plus the relevant Objecting audit/version contracts.
- Canonization: authoritative guard matrix and rules Canon018, Canon019, Canon021, Canon022, Canon023, Canon024, Canon026, Canon029, Canon030, Canon043, Canon044, and Canon045.
- Gating: `AGENTS.md`, `README.md`, `composer.json`, and `MANIFEST.json` as the executable-enforcement companion.
- Market/peer baseline: Shopify Storefront Cart and Medusa Cart/checkout documentation, focused strictly on mutable-cart responsibility and checkout handoff.

### Current repository state

- Branch: `canon/carting-structure-runtime-20260910-publish`.
- Baseline worktree was not clean before this run: untracked `.gating/` already existed and is treated as pre-existing local tooling, not product source for this change.
- `composer qa` passes: 45 tests / 162 assertions, PHPStan clean, PHP-CS-Fixer clean.
- Baseline `schema:parity` was blocked before mapping validation because standalone Carting did not bootstrap any test `DATABASE_URL`; this run materialized a bounded isolated PostgreSQL parity lifecycle and closed the blocker.
- Carting is a standalone Symfony component/application (`bin/console` + `config/bundles.php`) and therefore Canon022 applies.

### Canon mapping and selected RC-critical work

- Canon018: `carting/cart` correctly maps to `App\\Carting\\` and Cart-prefixed product types.
- Canon019: no competing `/src/Domain`, Port/Adapter/Adaptor taxonomy is part of the target model.
- Canon021: CartController routes are cart-owned business operations; generic CRUD stays in Cruding.
- Canon022: development/production manifests must directly declare the complete standalone platform baseline; Carting currently lacks direct Collectioning and Tabling dependencies.
- Canon023: existing sibling development path repositories correctly use `symlink: true`.
- Canon024: production manifest exists and uses VCS repositories rather than local paths.
- Canon026: PHP 8.4 / Symfony 8.1 baseline is already present.
- Canon029: PHPStan and PHP-CS-Fixer dependencies/config/scripts are present.
- Canon030: parity command exists but environment wiring currently blocks execution.
- Canon043: local first-party dependencies still use non-canonical `*@dev` and path repositories lack explicit `options.versions = dev-master` pins.
- Canon044/Objecting: Cart and CartItem duplicate audit timestamps (`created_at` + legacy `updated_at`) instead of consuming the Objecting audit pack and canonical `modified_at` vocabulary.
- Canon045: root development manifest must expose the reachable first-party local path repository closure, including Collectioning and Tabling reached through Cruding.

Selected RC-critical workstream: close Composer/canonical dependency defects and migrate Cart/CartItem audit fields to Objecting without weakening Cart's existing Doctrine optimistic lock. Keep checkout correctness, idempotency, lifecycle guards, schema parity, and regression coverage in scope.

### Growth workstream (post-RC, non-blocking)

- Buyer-context enrichment, delivery preference modeling, richer cart warnings, batch line mutation, cart-level diagnostics/metrics, and explicit public API ergonomics.
- Keep inventory ownership, catalog pricing authority, payment, shipping fulfillment, committed order creation, tax engine, and promotion engine outside Carting.

### Material risks

- Objecting's current version embeddable does not carry Doctrine `#[ORM\\Version]`; Cart keeps its existing Doctrine optimistic-lock field rather than weakening concurrency safety.
- Objecting audit embeddables require an explicit Doctrine mapping in Carting standalone runtime; `config/packages/doctrine.yaml` now maps the packaged Objecting embeddable path.
- Pre-existing local `.gating/` mirror is treated as local tooling state; `/.gating/` is now ignored so it remains outside source history and no longer blocks integration.

### Implemented RC hardening

- Canon022/043/045: added Collectioning/Tabling standalone baseline dependencies, complete local path repository closure, exact `dev-master` constraints, and explicit path-repository version pins; synchronized `composer.lock`.
- Production manifest: added packaged VCS resolution for Collectioning/Tabling and an executable `validate:prod` contract.
- Objecting audit adoption: Cart, CartItem, and CartCheckoutHandoffEntity now consume `ObjectAuditedInterface` + `ObjectAuditEmbeddableTrait`; Cart retains its existing Doctrine optimistic lock.
- Doctrine metadata: Carting standalone configuration now maps Objecting embeddables from the installed Composer package.
- Schema transition: added `Version20260914082000` to backfill `updated_at` into canonical `modified_at`, add Objecting actor fields, add missing checkout-handoff acceptance columns, and align the status check with `active`, `checkout_pending`, `converted`, `merged`, `abandoned`, and `expired`.
- Quality: PHPStan execution now includes migrations; architecture/entity regression tests cover dependency contour, production packaging, Objecting mapping, audit lifecycle, and schema transition.

### Verification results

- `composer validate --strict --check-lock`: PASS.
- `composer validate --strict --no-check-all composer.prod.json` via `validate:prod`: PASS.
- `composer qa`: PASS — 51 tests / 197 assertions, PHP-CS-Fixer clean, PHPStan clean across `src`, `tests`, and `migrations`.
- Changed PHP lint: PASS, including `migrations/Version20260914082000.php`.
- `composer audit`: PASS — no security vulnerability advisories.
- `composer schema:parity`: PASS against a clean disposable PostgreSQL `carting_test` database: database drop/create, complete migration chain, Doctrine mapping validation, schema synchronization, and migration-currentness all pass. Host credentials are reused only at runtime; no database secret is copied or persisted in Carting.
- Local Gating copy was read as executable-policy evidence; direct `gating` execution is not an allowlisted repository check in the current Console MCP execution surface, so canonical hard rules were verified through their textual rules, Composer validation, regression tests, and available gates.

### Residual RC condition

- None in the current Carting RC scope. Canon030 now reproduces current Doctrine metadata from the full migration chain on the isolated disposable `carting_test` database, and `schema:diff` is empty.
- `config/reference.php` is Symfony-generated local reference output and is explicitly ignored; it is not product source.
