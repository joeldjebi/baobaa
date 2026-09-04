# BAOBAA Platform Context

Use this skill whenever you work on BAOBAA domain flows, especially reservations, proforma invoices, dashboards, PEE/SAP/PSE roles, public venue pages, event projects, media storage, payments, sponsorship, or ticketing.

## Product Direction

BAOBAA is a premium francophone pan-African marketplace for event venues and event services.

Baseline: `Là où chaque événement prend racine`.

Primary roles:
- `SAP`: super admin platform owner.
- `PEE`: venue owner / partner, called propriétaire in routes.
- `Client`: final customer booking venues or composing events.
- `PSE`: event service provider for sound, lighting, podium, camera, photo, furniture, etc.

## Current Role And Portal Rules

- Each portal has a separate login route:
  - `/sap/login`
  - `/proprietaire/login`
  - `/client/login`
  - `/prestataire/login`
- Users can have multiple portals through `users.portal_roles`.
- Switching from client to PEE/PSE, or from PEE to client/PSE, requires SAP validation through `PortalAccessRequest`.
- Do not grant PEE/PSE access directly from public forms. Create a pending request and let SAP approve it.

## Reservation Workflow

The venue detail page has a public reservation form.

Current intended flow:
1. Client starts a request from a venue detail page.
2. The system creates a `Booking`.
3. The system creates/updates a `ProformaInvoice`.
4. The system creates an initiated deposit `Payment`.
5. Client and PEE can negotiate through `BookingMessage`.
6. Latest proposed amount becomes the negotiated base amount until a successful deposit payment exists.
7. Client and PEE must both confirm the proforma before test payment is accepted.
8. After deposit payment succeeds, financial amounts should not be recalculated from new proposed messages.

The public form supports:
- reserve at listed price;
- propose a negotiated amount;
- initial client notes;
- selected PSE services;
- SAP ticketing request.

## Event Projects

`EventProject` centralizes the client's event, whether it starts from a venue booking or from the standalone composer.

`EventProjectItem` separates entities:
- `venue_booking`: venue reservation from a PEE.
- `event_service`: selected service from a PSE.
- `ticketing`: BAOBAA/SAP ticketing request.

Use `EventProjectService` to create or synchronize project items. Avoid duplicating project-item creation in controllers.

## Standalone Event Composer

Route: `/composer-mon-evenement`.

Purpose:
- Client can compose an event without booking a venue first.
- PSE providers are displayed as tabs.
- Clicking a PSE tab shows that provider's published services.
- Client can select services and request BAOBAA ticketing.
- Saving requires client authentication and redirects to `/client/evenements`.

## PSE Scope

PSE accounts:
- register through `/prestataire/inscription`;
- wait for SAP approval;
- log in through `/prestataire/login`;
- manage services under `/prestataire/services`;
- manage profile/logo under `/prestataire/parametres`.

SAP owns event service types:
- route: `/sap/types-services`;
- PSE selects existing SAP-defined types when creating services.

Seeded demo account:
- email: `pse.demo@baobaa.local`
- password: `12345678`

Never add real secrets to this skill.

## SAP Scope

SAP dashboard handles:
- PEE list;
- client list;
- spaces;
- reservations;
- payments;
- subscription plans;
- commission rules;
- deposit rules;
- sponsorship plans;
- event service types;
- portal access requests;
- sponsorship approvals.

SAP creates and manages ticketing offers later. Current implementation stores ticketing requests as `EventProjectItem` with `provider_type = sap`.

## PEE Scope

PEE can:
- manage venues with multi-step draft saving;
- upload/remove media stored on Wasabi-compatible S3;
- manage add-on modules;
- manage bookings, proformas, negotiations and payout requests;
- configure profile/logo and payment methods per venue;
- sponsor venues based on SAP-defined sponsorship plans.

## Media And Wasabi

Venue media and partner logos use a Wasabi/S3-compatible disk.

Do not expose raw credentials. Use signed URLs for private media display. For production deployment, make sure the S3/Flysystem adapter is installed and `composer install --no-dev --optimize-autoloader` has run on the server.

## Testing Expectations

Before finishing changes in these areas, run focused tests:

```bash
vendor/bin/pint --format agent
php artisan test --compact tests/Feature/EventComposerTest.php tests/Feature/BookingRequestTest.php tests/Feature/OwnerVenueDraftTest.php tests/Feature/OwnerDashboardTest.php tests/Feature/PortalAuthenticationTest.php tests/Feature/ServiceProviderDashboardTest.php
php artisan view:cache --no-interaction
npm run build
```

Current expected focused result after the composer/PSE work:
- 70 tests passing;
- 445 assertions.

## Design Direction

Public pages and dashboards must stay premium, modern and organized:
- Tailwind CSS;
- refined blue-first palette;
- consistent navigation across public/client/PEE/SAP/PSE pages;
- no overloaded public detail pages;
- use modal/stepper patterns when forms become complex;
- keep large datasets organized with filters, pagination, tabs or carousels.

## Safety Notes

- Do not push `.env`.
- Do not commit production logs, `error_log`, cache artifacts or storage runtime files.
- For money-related workflows, keep proforma and payment state transitions explicit and covered by tests.
- For role changes, use SAP approval flow rather than direct role mutation from public/client actions.
