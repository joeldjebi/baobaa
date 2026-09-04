# BAOBAA Platform Context

Use this skill whenever you work on BAOBAA domain flows: public pages, venue booking, event composer, proforma invoices, payments, dashboards, PEE/SAP/PSE portals, media storage, sponsorship, ticketing, tests, or production deployment.

This skill is a handoff document for future agents. Read it before editing code.

## Product Identity

BAOBAA is a premium francophone pan-African marketplace for event venues and event services.

Baseline: `Là où chaque événement prend racine`.

Product promise:
- help clients find, compare, negotiate, reserve and pay for event venues;
- help clients compose a full event with independent service providers;
- let BAOBAA/SAP control trust, validation, commissions, subscriptions, deposits, sponsorship and ticketing;
- keep public UX modern, premium, blue-first, highly organized and not overloaded.

Design inspiration requested by the user:
- `eventsinminutes.com`;
- premium rounded UI;
- refined blue palette;
- Tailwind CSS;
- carousels/tabs where useful;
- public pages must hide internal/security-sensitive data.

## Design And UX Direction

BAOBAA must feel like a premium marketplace, not a generated admin template. Future agents must preserve a refined, modern, trustworthy UX across public pages and all dashboards.

Visual identity:
- primary brand direction: premium blue-first interface, with BAOBAA red used carefully for logo/brand accents, not as the dominant UI color;
- footer background requested and applied: `#07162f`;
- typography should stay modern, readable and French-first; use a Google Font already integrated or a similar clean sans-serif;
- avoid flat/basic layouts, oversized empty hero blocks, generic cards everywhere, one-color pages, and technical wording;
- use white, soft blue, deep navy, subtle borders, controlled shadows, and restrained gradients;
- UI should feel dense enough for a marketplace, but calm and scannable.

Public UX principles:
- public users are final clients, so never expose internal statuses, IDs, commissions, debug labels, storage paths, or provider-only data;
- write all visible hardcoded text in French with accents;
- use marketing copy that builds trust and clarity, not technical descriptions;
- prioritize fast comparison: photo, title, city/commune, capacity, starting price, rating/reviews, availability hint, partner/logo when relevant;
- large datasets must be organized with filters, pagination, horizontal carousels, sections and AJAX updates instead of dumping everything on one screen.

Homepage requirements:
- hero inspired closely by Eventsinminutes: centered premium headline, trust badge, search bar, category carousel on one line, smooth animated polish;
- category icons/logos and labels come from SAP-managed categories, and only categories with spaces should appear publicly;
- category carousel must support manual left/right navigation and horizontal scroll;
- search suggestions should appear after 3 typed characters and search category labels plus venue names;
- below hero, keep curated marketplace sections:
  - `Tendances actuelles` / `Annonces les plus populaires`;
  - popular by category;
  - popular by city;
  - popular by commune;
- carousel cards should show 4 full columns plus part of a 5th card on desktop to signal scrollability;
- the popular section can use two carousel rows, but each row needs its own left/right controls;
- sections need clear vertical spacing; do not let carousel controls float in confusing positions;
- footer must be premium, structured and dark navy with logo/brand, navigation, contact, legal links and trust copy.

Search and catalog UX:
- `/espaces` must be a premium catalog page, not an empty/simple list;
- keep the search/filter form visible and useful;
- filters should include what, where, date range, budget min/max, capacity/guest minimum, and category;
- filters must use AJAX where practical so the whole page does not refresh;
- filtering must query the full dataset, not only items visible on the current page;
- results can be list-based with pagination for readability at scale;
- form controls must not overlap on responsive breakpoints; use consistent heights and one visual container per field.

Venue detail UX:
- page should follow the reference detail page structure: gallery, metadata chips, sticky section tabs, content sections, similar venues and footer;
- sticky tabs must work without page reload and scroll to sections correctly;
- gallery images should open in a large viewer/modal with navigation through all images;
- reservation must become a compact CTA card plus a modal/stepper named around `Composer mon événement` or `Demander une réservation`;
- reservation modal should group:
  - date and time;
  - guests/budget;
  - negotiation/proposed price;
  - PEE modules;
  - PSE tabs and selectable services;
  - SAP ticketing request;
  - summary/proforma preview;
- the reservation flow must make it clear that login is required before saving booking/payment data;
- sticky reservation UI must not slide under sticky tabs or headers while scrolling.

Dashboard UX:
- SAP, PEE, PSE and Client dashboards should share the same premium system language;
- each dashboard needs a left sidebar on desktop and a hamburger/off-canvas menu on mobile;
- sidebar links should go to real pages, not tabs/anchors;
- tables need filters, pagination, stats, clear statuses, and action dropdowns that do not deform the table;
- forms should be step-based for long flows, save drafts via AJAX, and show success/error feedback;
- charts/stats should be useful, not decorative: revenue, reservations, conversion, pending validations, sponsorship, payments;
- actions must be clear and role-specific: client should not see owner/SAP links, PEE should not see SAP actions, etc.

Interaction and motion:
- animations should be subtle: hover elevation, soft fade/slide, carousel movement, loading skeletons;
- avoid shaky/stuttering scroll behavior; check sticky elements, `position: sticky`, transforms and excessive shadows when a page trembles;
- all dropdowns/modals must have high enough z-index and close when clicking outside;
- loaders should be intelligent: skeletons for content loading, inline saving state for forms, no blocking full-page loader for draft autosave.

Responsive and accessibility:
- every page must work cleanly on mobile, tablet and desktop;
- avoid text overflow in buttons, cards, filters and tables;
- use semantic buttons/links, focus states, labels and keyboard-friendly modals/dropdowns;
- never rely only on color to communicate status;
- dashboard tables should become responsive cards or horizontally scrollable tables on small screens.

Design debt and missing UX:
- reservation form still needs the premium modal/stepper redesign;
- public PSE/service catalog and service detail pages are missing;
- event project detail should show each provider/entity as its own negotiation/invoice lane;
- notification center is missing;
- payment gateway UX is still test-only;
- SAP ticketing needs a full management UX;
- public homepage sections should eventually be driven by analytics/popularity, not random/demo logic only;
- design tokens/components should be extracted to reduce repeated Tailwind classes.

## Glossary

- `SAP`: Super Admin Propriétaire, the BAOBAA platform owner and highest admin.
- `PEE`: Propriétaire d’Espace Événementiel, called `proprietaire` in French routes and `owner` in code.
- `PSE`: Prestataire de Services Événementiels, called `prestataire` in routes and `service_provider` in code.
- `Client`: end user who books a venue or composes an event.
- `Venue`: event venue/space published by a PEE.
- `EventService`: service published by a PSE, e.g. sound, lighting, podium, photo, video, furniture.
- `EventProject`: client event dossier that groups venue bookings, PSE services and ticketing.
- `ProformaInvoice`: professional proforma tied currently to venue booking workflow.
- `OwnerDepositRule`: SAP-defined deposit/acompte rule per PEE.

## Current Route Map

Public:
- `/` home.
- `/espaces` venue catalog with filters/AJAX results.
- `/espaces/{slug}` venue detail.
- `/partenaires` public PEE profiles.
- `/partenaires/{ownerProfile}` public PEE detail/list of spaces.
- `/composer-mon-evenement` standalone event composer for clients who may not want a venue first.

Portal login routes:
- `/sap/login`
- `/proprietaire/login`
- `/client/login`
- `/prestataire/login`

Client:
- `/client/dashboard`
- `/client/evenements`
- `/client/evenements/composer` POST store standalone event project.
- `/client/reservations`
- `/client/reservations/{booking}`
- `/client/reservations/{booking}/messages`
- `/client/reservations/{booking}/proforma/confirmer`
- `/client/reservations/{booking}/paiement-test`
- `/client/paiements`
- `/client/profil`
- `/client/mot-de-passe`

PEE:
- `/proprietaire/dashboard`
- `/proprietaire/espaces`
- `/proprietaire/espaces/nouveau`
- `/proprietaire/espaces/{venue}/modifier`
- `/proprietaire/espaces/brouillon`
- `/proprietaire/espaces/{venue}/medias/{venueMedia}` DELETE
- `/proprietaire/espaces/{venue}/statut`
- `/proprietaire/reservations`
- `/proprietaire/reservations/{booking}`
- `/proprietaire/reservations/{booking}/statut`
- `/proprietaire/reservations/{booking}/messages`
- `/proprietaire/reservations/{booking}/proforma/confirmer`
- `/proprietaire/paiements`
- `/proprietaire/sponsorings`
- `/proprietaire/disponibilites`
- `/proprietaire/modules`
- `/proprietaire/avis`
- `/proprietaire/parametres`

PSE:
- `/prestataire/inscription`
- `/prestataire/dashboard`
- `/prestataire/services`
- `/prestataire/services/nouveau`
- `/prestataire/services/{eventService}/modifier`
- `/prestataire/parametres`

SAP:
- `/sap/dashboard`
- `/sap/partenaires`
- `/sap/clients`
- `/sap/espaces`
- `/sap/reservations`
- `/sap/paiements`
- `/sap/forfaits-abonnement`
- `/sap/commissions`
- `/sap/acomptes`
- `/sap/types-services`
- `/sap/forfaits-sponsoring`
- `/sap/demandes-portails`

Portal access requests:
- `/portails/client`
- `/portails/proprietaire`
- `/portails/proprietaire/demande`
- `/portails/prestataire`
- `/portails/prestataire/demande`

## Authentication And Role Rules

Role enum: `App\Enums\UserRole`.

Role values:
- `sap`
- `admin`
- `owner`
- `service_provider`
- `client`

Users have:
- primary `users.role`;
- multi-portal `users.portal_roles` array.

Access protection uses `App\Http\Middleware\EnsureUserHasRole`, which calls `User::hasPortal()`.

Important rules:
- Do not grant `owner` or `service_provider` directly from public/client self-service.
- Client to PEE/PSE and PEE to client/PSE must go through `PortalAccessRequest` and SAP approval.
- `User::grantPortal()` is the supported way to add a portal after SAP approval.
- SAP approval is handled by `SapPortalRequestController::decide()`.

## Core Domain Models

User and profiles:
- `User`
- `OwnerProfile`
- `ServiceProviderProfile`
- `PortalAccessRequest`

Venue:
- `Venue`
- `VenueCategory`
- `Amenity`
- `VenueMedia`
- `VenueAvailability`
- `VenueRate`
- `VenueConfiguration`
- `VenueAddOn`
- `VenuePolicy`
- `VenueFaq`
- `VenueReview`

Booking/proforma/payment:
- `Booking`
- `BookingMessage`
- `ProformaInvoice`
- `ProformaInvoiceItem`
- `Payment`
- `BookingCommission`
- `Payout`
- `OwnerDepositRule`

Event project and PSE:
- `EventProject`
- `EventProjectItem`
- `EventServiceType`
- `EventService`

SAP monetization:
- `SubscriptionPlan`
- `CommissionRule`
- `SponsorshipPlan`
- `SponsorshipCampaign`
- `PlatformSetting`

## Services To Reuse

Do not duplicate these flows in controllers:
- `BookingWorkflowService`: orchestrates booking financial workflow, proforma, deposit payment and event project sync.
- `EventProjectService`: creates/syncs `EventProject` and `EventProjectItem`.
- `ProformaInvoiceService`: creates/updates proforma invoice for booking.
- `BookingDepositService`: computes deposit/acompte using SAP/PEE rules.
- `PartnerLogoService`: stores and signs PEE/PSE logos.
- `VenueMediaStorageService`: stores and signs venue media on Wasabi/S3-compatible storage.

## Reservation Workflow

Venue detail route: `/espaces/{slug}`.

Current intended flow:
1. Client opens a venue detail page.
2. Public form displays price, estimated deposit, payment methods, modules, PSE services in same city, and SAP ticketing request.
3. Guest must log in as client before saving.
4. Client starts a request.
5. `BookingController::store()` creates the booking.
6. If `booking_intent = negotiate` and `proposed_amount` is present, that amount becomes the initial negotiated amount.
7. Initial `client_notes` become a `BookingMessage`.
8. `BookingWorkflowService::ensureReadyForNegotiation()` creates/updates:
   - `ProformaInvoice`;
   - initiated deposit `Payment`;
   - `EventProject`;
   - `EventProjectItem` for the venue.
9. `BookingWorkflowService::syncRequestedAdditions()` adds selected PSE services and ticketing request to the event project.
10. Client and PEE can continue negotiating through `BookingMessage`.
11. Latest `BookingMessage.proposed_amount` becomes the negotiated base amount until a deposit payment succeeds.
12. Client and PEE must both confirm the proforma before payment is allowed.
13. Test payment route marks deposit payment as succeeded.
14. After successful deposit payment, later proposed amounts must not update financial totals.

Important: current real payment is a test flow. Do not represent it as a completed payment gateway integration.

## Proforma Rules

Current proforma workflow is strongest for venue bookings.

When negotiated amount changes:
- `booking.total_amount` updates;
- `booking.reservation_amount` recalculates from SAP/PEE deposit rule;
- `proforma_invoices.total_amount` updates;
- `proforma_invoices.deposit_amount` updates;
- `proforma_invoice_items.total_price` updates;
- initiated/pending `payments.amount` updates;
- proforma status resets to sent if needed.

When successful deposit payment exists:
- do not recalculate totals from new negotiation messages.

Current limitation:
- PSE services and ticketing are stored as project items, but they do not yet have full independent proforma/payment/negotiation workflows. Future work should add per-item proformas and messages, because one event can involve multiple providers.

## Event Projects

`EventProject` centralizes a client event, whether it starts from:
- venue detail reservation form;
- standalone event composer.

`EventProjectItem` separates all involved entities:
- `venue_booking`: venue reservation from a PEE.
- `event_service`: PSE service selected by client.
- `ticketing`: BAOBAA/SAP ticketing request.

Use `EventProjectService` for:
- `ensureVenueBookingItem()`
- `syncRequestedAdditions()`
- `createStandaloneProject()`
- `refreshTotals()`

Do not build project items manually in controllers unless there is no service method for the case.

## Standalone Event Composer

Route: `/composer-mon-evenement`.

Purpose:
- Client can compose an event without booking a venue.
- PSE providers are displayed as tabs.
- Clicking a PSE tab shows only that provider's published services.
- Client can select multiple services.
- Client can request `Billetterie BAOBAA`.
- Guest can view the page, but saving requires client authentication.
- Saving redirects to `/client/evenements`.

Store route:
- `POST /client/evenements/composer`
- controller: `EventComposerController::store()`

Created item types:
- selected PSE services become `event_service`;
- SAP ticketing request becomes `ticketing`.

Current limitation:
- no dedicated public PSE/service catalog page yet;
- no independent negotiation/chat per PSE service yet;
- no independent proforma/payment per PSE item yet.

## PSE Scope

PSE account:
- register through `/prestataire/inscription`;
- starts as client plus pending PSE request;
- SAP approves request;
- login through `/prestataire/login`.

PSE dashboard:
- `/prestataire/dashboard`
- `/prestataire/services`
- `/prestataire/services/nouveau`
- `/prestataire/services/{eventService}/modifier`
- `/prestataire/parametres`

PSE can:
- create services;
- select existing SAP-defined service type;
- define name, descriptions, location, service area, pricing unit, starting price, deposit amount, attributes and availability notes;
- publish/unpublish services;
- delete services;
- update logo/profile.

PSE cannot:
- create service types. SAP owns service types.
- bypass SAP validation.

Seeded local demo PSE:
- email: `pse.demo@baobaa.local`
- password: `12345678`
- Use only for local/demo testing. Do not reuse this password in production.

## SAP Scope

SAP dashboard handles:
- PEE list;
- client list;
- venues;
- reservations;
- payments;
- subscription plans;
- commission rules;
- deposit/acompte rules per PEE;
- event service types;
- sponsorship plans;
- portal access requests;
- sponsorship approvals.

SAP service types:
- route: `/sap/types-services`;
- model: `EventServiceType`;
- seed: `EventServiceTypeSeeder`;
- examples seeded: Sonorisation, Lumière et scénographie, Podium et scène, Photo et vidéo, Mobilier événementiel.

SAP ticketing:
- Currently client can request ticketing.
- Ticketing request is stored as `EventProjectItem`:
  - `item_type = ticketing`
  - `provider_type = sap`
  - `quoted_amount = 0`
  - metadata says commission/montant is negotiated offline.
- Future work: SAP should manage ticketing packages/options and generate a dedicated proforma.

SAP seeded account:
- Existing seeders include SAP creation.
- Check `database/seeders/SapUserSeeder.php` locally when credentials are needed.
- Do not put real production credentials, personal admin emails, or secrets in this skill.

## PEE Scope

PEE can:
- manage venue CRUD;
- save venue creation/update by steps as draft;
- activate/deactivate venues;
- upload/remove venue images/videos;
- store media on Wasabi-compatible S3;
- use signed URLs for public/private display;
- configure payment methods per venue;
- manage add-on modules from `/proprietaire/modules`;
- manage reservations and booking detail;
- negotiate through booking messages;
- confirm proforma;
- request payout after eligible completed bookings;
- manage payment history to BAOBAA;
- sponsor spaces based on SAP-defined sponsorship plans;
- update profile/logo in `/proprietaire/parametres`.

Important venue creation UX:
- multi-step form;
- button label should be `Continuer` except final step should save;
- Ajax save should not lock the page indefinitely;
- fields with multiple elements should use clean UI controls, not comma-separated raw inputs.

## Client Scope

Client can:
- register through `/client/inscription`;
- login through `/client/login`;
- browse venues;
- start a venue booking;
- propose a price during reservation;
- negotiate with PEE after booking;
- confirm proforma;
- make a test deposit payment after double confirmation;
- view reservation history;
- view payment history;
- edit profile and password;
- view `/client/evenements`;
- compose an event from `/composer-mon-evenement`.

Client dashboard navigation should stay coherent and responsive.

## Public Venue Detail Page

Key file: `resources/views/venues/show.blade.php`.

Important UX constraints from user:
- public detail page inspired by Eventsinminutes;
- tabs should be sticky and functional;
- reservation CTA/form must stay visible, but not go under sticky tabs;
- image gallery opens enlarged and scrolls/navigates through images;
- reservation workflow should not look technical;
- public users should not see internal/security-sensitive data;
- selected PSE services and ticketing must be visible in reservation flow;
- future preferred UX: turn full reservation form into a modal/stepper launched by a main button, because the form is becoming too rich.

Current implementation:
- sidebar form exists;
- supports reserve/propose amount;
- supports initial message;
- supports same-city PSE services;
- supports SAP ticketing checkbox;
- sticky offset adjusted to avoid tabs overlap.

Recommended next UI step:
- replace sidebar full form with a compact pricing/CTA card;
- open a polished modal stepper on `Demander une réservation`;
- steps: event details, negotiation, PSE tabs/services, ticketing, summary.

## Media And Wasabi

Venue media and partner logos use a Wasabi/S3-compatible disk.

Environment variable names used:
- `WAS_ACCESS_KEY`
- `WAS_SECRET_KEY`
- `WASABI_BUCKET`
- `WASABI_ENDPOINT`
- `WASABI_REGION`
- `WASABI_URL`

Never commit actual values.

Production issue previously seen:
- `Class "League\Flysystem\AwsS3V3\PortableVisibilityConverter" not found`
- fix requires installing the S3 Flysystem adapter on the server if missing and running Composer install/update correctly.

Useful command:

```bash
php artisan baobaa:diagnose-wasabi-images
```

Deployment notes:
- Run migrations after pulling code.
- Run `composer install --no-dev --optimize-autoloader`.
- Run `php artisan optimize:clear`.
- Run `php artisan migrate --force`.
- Run `npm run build` locally or on server depending deployment strategy.
- Ensure storage/cache directories are writable by the app user.

Do not commit:
- `.env`
- `error_log`
- production logs
- runtime cache files
- generated storage content

## Seeders And Demo Accounts

Relevant seeders:
- `SapUserSeeder`
- `VenueCategorySeeder`
- `AmenitySeeder`
- `EventServiceTypeSeeder`
- `OwnerProfileSeeder`
- `VenueSeeder`
- `ServiceProviderSeeder`
- `ClientSeeder`
- `SubscriptionPlanSeeder`
- `SponsorshipPlanSeeder`
- `CommissionRuleSeeder`
- `OwnerDepositRuleSeeder`
- `PlatformSettingSeeder`

PSE local demo:
- `pse.demo@baobaa.local`
- `12345678`
- `/prestataire/login`
- Use only for local/demo testing.

If demo account is missing locally:

```bash
php artisan db:seed --class=EventServiceTypeSeeder
php artisan db:seed --class=ServiceProviderSeeder
```

## Important Files By Area

Routes:
- `routes/web.php`

Public:
- `resources/views/welcome.blade.php`
- `resources/views/venues/index.blade.php`
- `resources/views/venues/show.blade.php`
- `resources/views/event-composer/create.blade.php`
- `resources/views/components/navigation/public-menu.blade.php`
- `resources/views/components/navigation/account-menu.blade.php`

Client:
- `resources/views/components/dashboards/client-shell.blade.php`
- `resources/views/dashboards/client.blade.php`
- `resources/views/dashboards/client-reservations.blade.php`
- `resources/views/dashboards/client-booking-show.blade.php`
- `resources/views/dashboards/client-projects.blade.php`
- `resources/views/dashboards/client-payments.blade.php`
- `resources/views/dashboards/client-profile.blade.php`

PEE:
- `resources/views/components/dashboards/owner-shell.blade.php`
- `resources/views/dashboards/owner*.blade.php`
- `resources/views/dashboards/owner/venue-create.blade.php`

PSE:
- `resources/views/components/dashboards/service-provider-shell.blade.php`
- `resources/views/dashboards/service-provider/index.blade.php`
- `resources/views/dashboards/service-provider/services.blade.php`
- `resources/views/dashboards/service-provider/service-form.blade.php`
- `resources/views/dashboards/service-provider/settings.blade.php`

SAP:
- `resources/views/components/dashboards/sap-shell.blade.php`
- `resources/views/dashboards/sap/*.blade.php`

Controllers:
- `PublicVenueController`
- `BookingController`
- `BookingMessageController`
- `ProformaInvoiceConfirmationController`
- `TestPaymentController`
- `DashboardController`
- `PortalAccessController`
- `SapPortalRequestController`
- `SapDashboardController`
- `EventComposerController`
- `EventServiceController`
- `ServiceProviderDashboardController`
- `ServiceProviderRegisteredUserController`

Tests:
- `tests/Feature/BookingRequestTest.php`
- `tests/Feature/EventComposerTest.php`
- `tests/Feature/OwnerDashboardTest.php`
- `tests/Feature/OwnerVenueDraftTest.php`
- `tests/Feature/PortalAuthenticationTest.php`
- `tests/Feature/ServiceProviderDashboardTest.php`

## Validation And Security Expectations

Before accepting user-submitted IDs:
- validate with `exists`;
- restrict by status where relevant, e.g. published services only;
- authorize ownership in controller with `abort_unless`.

Before showing public data:
- show only published venues/services;
- do not expose private owner/PSE/internal fields;
- use UUID/public identifiers for partner profiles where already implemented;
- do not leak raw storage paths if signed URLs are expected.

Money-related rules:
- keep amount changes covered by tests;
- use integer minor unit style amounts, currently XOF whole amounts;
- do not silently recalculate after a successful deposit payment;
- keep proforma confirmation before test payment.

Role-related rules:
- do not mutate role/portal from public action without SAP validation;
- use `PortalAccessRequest`;
- use `User::grantPortal()` after SAP approval.

## Testing Expectations

Run focused verification before finishing these areas:

```bash
vendor/bin/pint --format agent
php artisan test --compact tests/Feature/EventComposerTest.php tests/Feature/BookingRequestTest.php tests/Feature/OwnerVenueDraftTest.php tests/Feature/OwnerDashboardTest.php tests/Feature/PortalAuthenticationTest.php tests/Feature/ServiceProviderDashboardTest.php
php artisan view:cache --no-interaction
npm run build
```

Expected focused result after event composer/PSE workflow:
- 70 tests passing;
- 445 assertions.

If only editing Markdown, tests are not required, but still check for secrets before commit.

## Current Known Limitations

These are intentional unfinished areas, not bugs unless the user asks for them now:
- reservation form should be moved to a modal/stepper for premium UX;
- PSE services selected in event projects do not yet have independent negotiation/messages;
- PSE services do not yet have independent proforma/payment records;
- SAP ticketing request is stored, but SAP does not yet manage ticketing packages/forms/proformas;
- public PSE/service catalog page is not yet implemented;
- notifications are not yet fully implemented;
- real payment gateway is not integrated; current payment is test flow.

## Recommended Next Steps

Strongest next implementation path:
1. Replace venue detail reservation sidebar form with compact CTA plus modal stepper.
2. Add per-`EventProjectItem` negotiation thread so each PSE/PEE/SAP can manage its own invoice.
3. Add per-`EventProjectItem` proforma/payment records or polymorphic invoice source.
4. Build SAP ticketing management: ticket categories, fee rule, commission or fixed BAOBAA fee.
5. Build public PSE service catalog and service detail pages.
6. Add notification events for booking/proforma/payment/portal validation.
7. Harden availability checks to prevent double booking under concurrency.

## Git And Deployment Discipline

User may ask to push manually. Do not push unless explicitly requested.

Before pushing:
- `git status --short`
- confirm `.env` is not staged;
- search for secrets:

```bash
rg -n "WAS_ACCESS_KEY|WAS_SECRET_KEY|WASABI|SECRET|ACCESS_KEY" . --glob '!vendor/**' --glob '!node_modules/**' --glob '!.git/**' --glob '!storage/logs/**' --glob '!bootstrap/cache/**'
```

Acceptable hits are config files using `env(...)`, not real credential values.

Last known pushed feature commit before this skill expansion:
- `6c83215 Add event composer and PSE workflow`
