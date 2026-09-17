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

## 2026-09-16 — Canon030 clean-chain revalidation

### Reconnaissance and canon mapping
- Re-read Carting `AGENTS.md`, `README.md`, Composer manifest, responsibility/output/checkout-readiness architecture documents, current entities and migration chain, plus required Objecting, Cruding, Viewing, Interfacing, Gating, and Canonization root contracts.
- Consulted Canon000, Canon001, Canon002, Canon003, Canon007, Canon008, Canon018, Canon019, Canon020, Canon021, Canon022, Canon023, Canon024, Canon025, Canon026, Canon029, Canon030, Canon032, Canon033, Canon034, Canon036, Canon038, Canon039, Canon041, Canon043, Canon044, and Canon045 where applicable.
- RC-critical workstream: preserve mutable-cart and checkout-handoff boundaries while restoring exact migration/ORM schema parity. Growth remains richer cart diagnostics, mutation idempotency ergonomics, buyer-context enrichment, and handoff observability; inventory, pricing authority, payment, shipping, committed orders, tax, and promotion remain outside Carting.

### Factual baseline and verification
- `composer validate --strict`: PASS.
- `composer qa`: PASS, 51 tests / 197 assertions, PHP-CS-Fixer clean, PHPStan clean.
- Initial clean `schema:parity` reproduced a Canon030 defect: Doctrine requested renaming `idx_e99308e41ad5cdbf` to `idx_cart_adjustment_cart_id` and `idx_f0fe25271ad5cdbf` to `idx_cart_item_cart_id`.
- During this run, `migrations/Version20260916182500.php` changed concurrently from an ALTER-based repair to a DROP-hash/CREATE-canonical-index repair and appeared as an untracked file. This run did not overwrite or claim authorship of that concurrent change.
- Re-running `schema:parity` against the updated current worktree passed completely: clean database recreation, six migrations, Doctrine mapping and schema synchronization, and migration currentness all green.

### Worktree boundary
- The concurrent untracked migration is required by the currently passing schema chain but remains external/concurrent work from this run's perspective; it must not be silently staged as this run's authored change without integration ownership being clear.

## 2026-09-16 — Canon030 repair and current testing-canon uplift

### Reconnaissance and canon mapping

- Re-read the Carting repository documentation, market analysis, Composer manifests, Symfony/Doctrine configuration, current entities, migration chain, tests, scripts, and release-hardening notes on branch `rc/carting-schema-parity-20260914`.
- Re-read the required Objecting, Cruding, Viewing, and Interfacing contracts and confirmed the Carting boundary remains mutable cart intent plus checkout handoff; generic CRUD, presentation rendering, shell ownership, Objecting system-field semantics, tax engines, promotion engines, payment, fulfillment, and committed-order ownership remain outside Carting.
- Consulted Canonization normative rules Canon018, Canon019, Canon021, Canon022, Canon023, Canon024, Canon026, Canon029, Canon030, Canon039, Canon040, Canon041, Canon042, Canon043, Canon044, and Canon045, plus the guard matrix/rules journal; Gating was read as the executable companion.
- Market check reconfirmed the boundary against current Shopify Storefront Cart and Medusa Cart/Promotion/Tax module documentation: cart state and checkout handoff are distinct from downstream purchase completion and specialized tax/promotion engines.

### Factual baseline and selected RC-critical work

- Initial worktree was clean at `ac557cef5dd58e3a8237bd2970f0b014a2a50054`, one commit ahead of upstream.
- `composer qa` passed at baseline with 51 tests / 197 assertions, PHPStan clean, and PHP-CS-Fixer clean.
- Contrary to the prior journal entry, a fresh `composer schema:parity` failed after replaying the complete migration chain: Doctrine reported schema drift. The exact diff required restoration of two unique indexes and canonical names for two cart relation indexes.
- Canon039/041 had become relevant hard tooling requirements after the earlier hardening wave: the repository lacked an explicit PHPUnit source coverage population, persistent branch/path coverage script, Symfony Test Pack/Panther dependencies, and repository-local Playwright tooling.

### Implemented RC work

- Added forward migration `Version20260916182500` without rewriting historical migrations. It restores the two metadata-required unique indexes and deterministically recreates the CartItem/CartAdjustment relation indexes under the names declared by current Doctrine metadata.
- Added PHPUnit `src/` coverage population and `test:coverage`, using Xdebug path coverage and persistent `var/coverage/summary.txt` evidence.
- Added Symfony Test Pack and Panther development dependencies and synchronized `composer.lock`.
- Added repository-local Playwright tooling (`package.json`, `package-lock.json`, `playwright.config.js`) and an executable runner smoke that does not fabricate application UI behavior.
- Extended `.gitignore` for `node_modules/`, Playwright test results, and Playwright reports.

### Verification

- `composer schema:parity`: PASS from a clean disposable PostgreSQL database; 6 migrations replayed, mapping valid, schema synchronized, migrations current.
- `composer schema:diff`: PASS / empty after the repair.
- `composer qa`: PASS — 51 tests / 197 assertions; CS and PHPStan clean.
- `composer validate --strict --check-lock`: PASS.
- `validate:prod`: PASS.
- `composer audit`: PASS, no advisories.
- `npm test`: PASS — Playwright runner smoke 1/1.
- `npm audit --audit-level=high`: PASS, zero vulnerabilities.
- `composer test:coverage`: PASS and produced canonical evidence: Lines 78.97% (443/561), Methods 56.56% (69/122), Branches 74.28% (257/346). Canon040 branch target is satisfied; line and method targets remain warning-level remediation debt, not a hard Canon040 failure.
- Canon042 behavioral/UI coverage percentages are not invented: no schema-v2 denominator/evidence producer is claimed by this wave, so that remains measurable post-RC evidence debt rather than fabricated compliance.

### Growth / post-RC workstream

- Raise Canon040 line coverage from 78.97% to at least 80% and method coverage from 56.56% to at least 80% with behaviorally meaningful tests, prioritizing `CartMutationService`, `CartController`, entity lifecycle/accessor surfaces, and checkout services.
- Add a repository-owned Canon042 evidence producer only after stable functional/workflow/UI eligible inventories are explicitly defined; keep test counts out of the denominator.
- Continue richer buyer-context, cart diagnostics, batch mutation, and UX/API maturity only after RC correctness and evidence contracts remain stable.

## 2026-09-17 — Executable Gating integration and RC revalidation

### Reconnaissance and market boundary
- Started from a clean `rc/carting-schema-parity-20260914` worktree synchronized with its upstream and re-read Carting guidance, README, development/production Composer manifests, current coverage evidence, and the existing execution journal.
- Reused the required Objecting, Cruding, Viewing, Interfacing, Gating, and Canonization contour already established for Carting and mapped this pass to Canon029, Canon030, Canon039, Canon040, Canon041, Canon042, Canon043, Canon044, and Canon045.
- Current Shopify/Medusa cart practice continues to support Carting's boundary: buyer/cart context and mutable cart state belong with the cart, while tax and promotion engines remain distinct modules and payment/order fulfillment remain downstream.

### Material implementation
- Added development-only `gating/gate` through a canonical local path repository with `symlink: true` and exact `dev-master` version pin.
- Added repository-owned `config/cart_gating_profile.yaml` and `config/cart_gating_rules.yaml` and an executable `gating:check` Composer script using the installed Gating policy rather than a copied runtime tree.
- Updated the Composer lock through a package-scoped Gating update; Composer also refreshed the compatible local Collectioning/Tabling references and EasyAdmin within existing constraints.

### Verification and residual debt
- `composer validate --strict --check-lock`: PASS.
- `validate:prod`: PASS.
- `cs:check`: PASS, 0 fixable files.
- PHPUnit: PASS, 51 tests / 197 assertions.
- PHPStan: PASS, 0 errors across `src`, `tests`, and `migrations`.
- `schema:parity`: PASS from a clean disposable PostgreSQL `carting_test` database; 6 migrations / 71 SQL queries, mapping valid, schema synchronized, migrations current.
- `gating:check`: PASS with 36 rules, 0 failures, 2 warnings, 3 skipped. Canon040 reports lines 78.97%, methods 56.56%, branches 74.28%; Canon042 reports missing behavioral/UI coverage inventory.
- Canon040/042 remain explicit non-blocking evidence debt. No synthetic getter-test wave or fabricated behavioral/UI denominator was added merely to silence warnings.
