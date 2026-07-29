# Carting

Carting is the Smart Responsor component responsible for mutable shopping cart operations and checkout handoff. It manages anonymous and authenticated user selection states before order confirmation.

This module is **not** responsible for product inventory validation, price catalog management, payment processing, or order shipping.

## Current Posture

### What the component already does
- Cart identity lifecycle management (anonymous vs authenticated).
- Cart item listing, addition, deletion, and quantity mutation.
- Captures item price and title snapshots to lock selected item rates.
- Carts merging (e.g. merging guest carts on login).
- Prepares checkout handoff payloads for consumption by the `Ordering` service.

### What this repository does not claim yet
- Catalog updates or product stock adjustments.
- Sales tax calculation and processing.
- Discount code application or promotion rules engine.

## Runtime Surface & Entrypoints

The Carting bundle exposes services, entities, value objects, host-neutral surface contracts, and explicit cart business routes. It contains no generic Cruding controllers or generic CRUD route grammar:
- `src/Controller/CartController.php` - Cart-owned business endpoints for summary, item mutation, and checkout handoff.
- `src/Service/` - Cart operations, item updates, merging logic, and checkout generation.
- `src/Entity/` - Doctrine models for cart and cart item state persistence.
- `src/Value/` - immutable summaries, handoff payloads, and UI surface values.
- `src/ServiceInterface/` - narrow integration contracts for external offer, availability, and estimate providers.
- `src/CartingBundle.php` - Bundle configuration.

## Local Setup

Install dependencies:
```bash
composer install
```

Run test suite:
```bash
vendor/bin/phpunit
```

## Local Composer Path Installation

Include Carting as a path repository within your Symfony application:

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "../Carting",
      "options": {
        "symlink": true
      }
    }
  ],
  "require": {
    "carting/cart": "*@dev"
  }
}
```

## Documentation Map

- [Carting Responsibility Overview](docs/architecture/carting-responsibility.adoc)
- [Carting Output Surface Contract](docs/architecture/carting-output-surface-contract.adoc)
- [Checkout Handoff & Handoff Readiness](docs/architecture/carting-checkout-readiness.adoc)
