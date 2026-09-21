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

## 2026-09-20 — Product capability roadmap execution baseline

### Reconnaissance read

- Re-read Carting `AGENTS.md`, `README.md`, development/production Composer manifests, live `PRODUCT_CAPABILITY_AUDIT.adoc`, architecture documents, RC milestone notes, current Cart/CartItem entities, merge/mutation services, repository contracts, controller wiring, tests, Gating profile/rules, and the existing CMCP journal.
- Re-read the required Objecting, Cruding, Viewing, Interfacing, Gating, and Canonization contour. Canonization remains READ_ONLY; relevant normative rules consulted for this pass include Canon000, Canon003, Canon012, Canon013, Canon017, Canon018, Canon019, Canon021, and Canon044, with the guard matrix used only as enforcement metadata.
- Market/peer baseline rechecked against current Shopify Storefront Cart and Medusa cart/checkout behavior: line mutation and mutable-cart state remain cart responsibilities, while order commitment and specialized pricing/promotion/tax/stock engines stay outside Carting.

### Current repository state

- Branch: `rc/carting-schema-parity-20260914`.
- Baseline worktree has one pre-existing untracked authoritative roadmap file: `PRODUCT_CAPABILITY_AUDIT.adoc`. This execution does not treat its untracked status as permission to omit or overwrite its current content.
- Live roadmap is newer than prior RC journal claims and currently marks buyer/guest identity, cart merge conflict semantics, typed producer results, and saved/recoverable carts as incomplete.

### Target-to-canon mapping

- Canon000/018: `carting/cart` maps to `App\\Carting\\` with `Cart*` subject vocabulary.
- Canon003/012: stable merge/mutation outcomes and cross-component facts must use explicit typed contracts rather than long-lived unshaped arrays.
- Canon013: no placeholder merge or idempotency behavior may be used to make the roadmap appear complete.
- Canon017: roadmap/architecture documentation must be updated to match proven runtime behavior.
- Canon019: no Domain/Application/Infrastructure or Port/Adapter/Adaptor root taxonomy may be introduced.
- Canon021: Carting keeps only cart-owned business operations; generic CRUD remains in Cruding.
- Canon044/Objecting: existing entity-native audit fields remain consumed through Objecting packs; this milestone does not clone system fields locally.

### Selected RC-critical workstream

- Implement the earliest incomplete roadmap milestone: make guest-to-owner merge identity requirements explicit and deterministic, preserve conflicting commercial snapshots as separate owner lines instead of silently summing unlike snapshots, and prove both successful reassociation and conflict behavior in tests.
- Preserve the Carting/Ordering boundary and leave Pricing, Promoting, Taxating, Stocking, payment, shipping, and committed-order ownership outside this milestone.

### Growth workstream (non-blocking)

- Typed producer-result integration, saved/recoverable cart lifecycle, richer recovery diagnostics, and end-to-end Ordering acceptance remain subsequent roadmap work after the merge/identity invariant is green.

### Material risks and gates

- Risk: combining lines solely by offer reference can silently alter commercial intent when guest and owner snapshots differ in price/title/metadata.
- Risk: a method named guest-to-owner merge currently accepts carts whose owner identities contradict those roles.
- Verification gates planned: Composer validation, PHP lint, PHPUnit/coverage, PHPStan, PHP-CS-Fixer, Gating, Symfony container/YAML checks, Doctrine/schema parity when applicable, plus repository-specific tests.

### Material implementation completed

- Milestone 1: added explicit guest-cart claiming, enforced guest/owner roles during merge, and changed merge coalescing from offer-reference-only to full commercial-snapshot equality so conflicting price/title/currency/metadata snapshots are preserved as separate lines.
- Milestone 2: replaced scalar/array producer-result seams with Carting-owned typed DTO facts for pricing, promotion, tax, and stock availability. Added optional producer source references and persisted them on cart adjustments.
- Checkout replay provenance: cart summaries now expose typed adjustment projections and checkout handoffs freeze adjustment type, label, amount, and source reference. Historical persisted handoff payloads without an adjustments key remain readable.
- Milestone 3: added explicit recovery of the active persisted owner cart without reactivating abandoned/expired terminal carts.
- Retry safety present in the current worktree: checkout preparation reuses the persisted handoff for a checkout-pending cart; accepted completion reuses the downstream reference and does not call the consumer twice. These checkout-idempotency edits appeared concurrently during this execution and were preserved, reviewed, and verified rather than overwritten.
- Milestone 4 boundary check: current Ordering tree exposes no explicit CartCheckout handoff acceptance contract discoverable by repository search. Carting therefore keeps the host-neutral CartCheckoutHandoffConsumerInterface; a concrete Ordering adapter remains host/integration ownership and is not fabricated here.

### Verification results

- Changed PHP lint: PASS across 27 changed/untracked PHP files before the final documentation-only edits.
- PHPUnit: PASS — 69 tests / 246 assertions.
- PHPStan: PASS — 0 errors across src, tests, and migrations.
- PHP-CS-Fixer: PASS after repository-local formatter application — 0 fixable files.
- Doctrine schema parity: PASS from a clean disposable PostgreSQL carting_test database; 7 migrations / 72 SQL queries, mapping valid, schema synchronized, migrations current.
- Production Composer manifest validation: PASS.
- Test coverage: lines 81.1% (514/634), methods 59.0% (79/134), branches 77.4% (329/425). Canon040 line and branch targets are green; method coverage remains warning-level debt.
- Gating: PASS with 36 rules, 0 failures, 2 warnings, 3 skipped. Remaining warnings are Canon040 method coverage and Canon042 missing behavioral/UI coverage evidence.
- No browser/mobile UI behavior changed in this wave; no visual artifact is required for the implementation itself.

### Remaining RC integration condition

- Carting-side product capability milestones are implemented through the handoff boundary. A concrete end-to-end Carting -> Ordering adapter cannot be truthfully implemented inside this repository until Ordering publishes a stable acceptance contract or the host application owns that adapter.
- Canon042 evidence remains repository-level test-observability debt; no synthetic behavioral/UI denominator was invented.

## 2026-09-21 — Carting RC verification and integration pass

### Reconnaissance and canon mapping

- Re-read Carting AGENTS, README, development/production Composer manifests, Gating profile/rules, current capability audit, checkout-readiness architecture document, current worktree status/diff, and the active RC journal.
- Re-read the required Objecting, Cruding, Viewing, Interfacing and Gating AGENTS/README/Composer contracts that materially govern Carting, and treated their worktrees as read-only dependencies.
- Re-read Canonization authoritative textual rules Canon018, Canon019, Canon021, Canon022, Canon023, Canon024, Canon030, Canon040 and Canon044. Mapping: carting/cart => App\\Carting\\ + Cart*; no alternative root taxonomy; generic CRUD remains Cruding-owned; local platform dependencies use symlinked path repositories; production uses packaged dependencies; Doctrine migration chain must equal current metadata; PHP line/method/branch coverage is independently measured; Objecting system fields remain entity-native.
- Code Memory scope resolution found no declared memory:scope:resolve Composer script, but Console MCP resolved the repo-local Carting graph as the implementation graph and the umbrella www graph as read-only navigation.

### Current state and selected RC work

- Branch rc/carting-schema-parity-20260914 was synchronized with upstream at HEAD 231bcd8059ee9a98219427c5dc572c51f08861d8 before integration and carried the existing product-capability workstream.
- RC-critical work for this pass was verification/repair of that workstream, not speculative feature growth.
- External market baseline remained consistent with the component boundary: mutable cart state and checkout handoff belong to Carting; promotion/tax/pricing/inventory engines and committed-order ownership remain separate capabilities.
- Growth remains non-blocking: richer UX/API diagnostics and Canon042 behavioral/UI evidence should follow after RC integration.

### Repairs made during verification

- Normalized tests/Unit/DTO/CartProducerResultDTOTest.php with the repository PHP-CS-Fixer and made its producer-kind match exhaustive for PHPStan.
- Replaced formatting-sensitive Composer string assertions in tests/Unit/Architecture/CartingArchitectureTest.php with semantic JSON assertions over direct dev-master requirements and exactly one symlinked path repository per canonical platform dependency.

### Verification

- composer validate --strict --check-lock: PASS.
- composer qa: PASS — 69 tests / 246 assertions, PHP-CS-Fixer clean, PHPStan clean.
- composer schema:parity: PASS — clean disposable PostgreSQL database, 7 migrations / 72 SQL queries, mapping valid, schema synchronized, migrations current.
- composer validate:prod: PASS.
- composer test:coverage: PASS — Lines 81.07% (514/634), Methods 58.96% (79/134), Branches 77.41% (329/425). Canon040 line/branch targets pass; method coverage remains warning-level debt, not HIGH_TEST_DEBT.
- composer audit: PASS — no security advisories.
- Symfony lint:container: PASS; lint:yaml config: PASS for all 8 YAML files.
- npm test: PASS — Playwright tooling smoke 1/1; npm audit --audit-level=high: PASS with zero vulnerabilities.

### External Gating blocker

- composer gating:check cannot currently start because the live sibling Gating repository is mid canonical namespace/rule migration: its executable source expects App\\Gating\\Console\\GatingApplication while the installed package metadata still exposes Gating\\Gate\\ => src/.
- The Gating worktree is independently dirty on rc/gating-canon-sync-20260921 with 108 changes. Carting does not patch or commit that repository. This is an external integration blocker for executable Gating only; all independent Carting gates above are green.



