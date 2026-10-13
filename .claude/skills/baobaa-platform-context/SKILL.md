# BAOBAA Platform Context

Use this skill whenever you work on BAOBAA domain flows: public pages, venue booking, event composer, proforma invoices, payments, dashboards, PEE/SAP/PSE portals, media storage, sponsorship, ticketing, tests, or production deployment.

This skill is a handoff document for future agents. Read it before editing code.

## Ajouts de contexte effectués pendant cette session

Cette section distingue explicitement les éléments ajoutés pendant le travail de cette session par rapport au contexte initial déjà existant dans le projet.

### 1) UX publique et landing page

- La page d’accueil a été structurée autour d’un hero premium inspiré du style Eventsinminutes, avec hiérarchie claire, CTA fort, badges de confiance et recherche plus lisible.
- Le formulaire de recherche public a été conservé dans son design initial après validation, afin de respecter l’identité visuelle déjà existante et éviter une régression de style.
- Le comportement de la recherche publique et de la réservation doit rester inchangé ; seules les améliorations visuelles doivent être appliquées avec prudence.
- La logique de recherche/suggestion doit continuer à faire appel aux composants déjà présents et aux filtres existants, sans dupliquer de code ni introduire de nouveaux points de friction.

### 2) Branding et identité visuelle

- Le logo BAOBAA a été standardisé sur l’image réelle `public/images/baobaa.jpg` dans les menus et les composants de dashboard.
- Les logos ne doivent plus être des textes ou des identités hybrides ; ils doivent rester visibles et correctement dimensionnés sans déformer les menus ni les sidebars.
- La charte visuelle publique doit rester premium, blue-first, sobre et centrée sur des conteneurs arrondis, des ombres discrètes et des cartes bien structurées.
- Les public-facing copy doivent rester en français avec accents et ne jamais exposer d’informations internes ou techniques.

### 3) SAP / listes de fournisseurs et visibilité de l’écosystème

- Le SAP a désormais une visibilité plus claire sur les fournisseurs et services publiés grâce à des pages dédiées de liste et de détail.
- La navigation SAP a été complétée pour exposer les organisations/services plus proprement dans les tableaux et vues de dashboard.
- La logique de visibilité des prestataires, services et réservations doit rester limitée aux écrans autorisés et ne pas fuir vers l’interface publique.

### 4) Dashboard et composants shell

- Les `shells` des dashboards client, propriétaire, SAP et prestataire ont été harmonisés avec le logo BAOBAA et un style visuel plus cohérent.
- Les composants de sidebar et navigation doivent rester stables, lisibles et proportionnés sur mobile et desktop.
- Les actions et liens doivent rester rôle-spécifiques et éviter toute fuite de navigation interne vers des portails non autorisés.

### 5) Parcours de réservation et de négociation

- Le parcours de réservation public a été enrichi en direction d’une expérience “Composer mon événement” plus premium.
- La logique de réservation, de devis et de négociation continue à passer par les modèles/services existants : `Booking`, `EventProject`, `EventProjectItem`, `EventProjectMessage`, `ProformaInvoice`, `BookingMessage`.
- La disponibilité concurrente est protégée par `AvailabilityHold` et `AvailabilityHoldService` : les espaces et services sélectionnés sont verrouillés pendant 10 minutes maximum au moment de la création de réservation/demande.
- Les verrous expirés ne bloquent plus les créneaux même s’ils restent en base ; les requêtes doivent toujours vérifier `expires_at > now()` pour les verrous `active`.
- Les conflits de créneaux doivent être contrôlés côté backend dans les transactions, jamais uniquement par l’interface.
- Règle métier essentielle : une réservation composée est une seule ligne métier au niveau `EventProject`; les `EventProjectItem` sont les éléments internes du dossier et ne doivent pas être présentés comme plusieurs réservations séparées.
- La négociation prestataire porte sur la réservation/le dossier `EventProject` pour le prestataire concerné, pas sur une ligne `EventProjectItem` isolée.
- Les travaux futurs doivent continuer à respecter la séparation actuelle entre public, client, propriétaire, PSE et SAP.
- Les étapes de négociation/proforma doivent rester pilotées par les services déjà créés et ne pas être réécrites en logique ad hoc dans les vues.

### 6) État de la roadmap UX / priorités restantes

- Les éléments encore à livrer sont clairement identifiés comme la suite de l'UX client pour les négociations groupées, la billetterie SAP, le catalogue public des PSE, les notifications et le paiement réel.
- Les travaux de design et d’UX doivent rester concentrés sur les points à forte valeur réelle : home, reservation flow, catalog, dashboard logic, and trust-building UI.
- Les tâches plus lourdes doivent être abordées par branches intermédiaires à partir de `develop`, après synchronisation avec `main`.

### 7) Validation de régression

- Validation ciblée exécutée après l'ajout des verrous de disponibilité, du catalogue prestataires et du filtrage public par dates :
    - `vendor/bin/pint --format agent`;
    - `php artisan view:cache --no-interaction`;
    - `php artisan test --compact tests/Feature/PortalAuthenticationTest.php --filter='public venues search excludes booked and temporarily blocked dates|public venues index lists published spaces'`;
    - `php artisan test --compact tests/Feature/EventComposerTest.php --filter='service booking blocks overlapping reservations'`;
    - `php artisan test --compact tests/Feature/BookingRequestTest.php --filter='venue booking blocks overlapping reservations'`.
- Résultat vérifié : les tests ciblés passent.
- Ne pas supposer que toute la suite complète a été relancée après chaque micro-ajustement Markdown.

### 8) Dernier commit poussé

- Branche locale de travail : `develop`.
- Dernier commit poussé vers `origin/main` : `0c1e6e5 feat: secure event availability and provider discovery`.
- Push effectué avec : `git push origin develop:main`.
- Ne pas repusher sans demande explicite de l'utilisateur.

## Product Identity

BAOBAA is a premium francophone pan-African marketplace for event venues and event services.

Baseline: `Là où chaque événement prend racine`.

Product promise:

- help clients find, compare, negotiate, reserve and pay for event venues;
- help clients compose a full event with independent service providers;
- let BAOBAA/SAP control trust, validation, commissions, subscriptions, deposits, sponsorship and ticketing;
- keep public UX modern, premium, blue-first, highly organized and not overloaded.

Design inspiration requested by the user:

- Reference platform: `https://www.eventsinminutes.com/`;
- reference venue search page: `https://www.eventsinminutes.com/venues?top_category_type=single_category_vendor&main_category=698c11484177d07fb3cbc5ea&q=conference+room&where=San+Francisco%2C+CA&radius_miles=25&apply_filter=true`;
- reference venue detail page: `https://www.eventsinminutes.com/venues/oakland-ca/modern-and-chic-conference-room-for-up-to-12-guests-400-sq-ft`;
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
- avoid internal abbreviations on public pages; use `prestataire événementiel` instead of `PSE` and `partenaire`/`propriétaire` instead of `PEE`;
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
- venue detail page should expose a clear premium CTA button to compose the client's event package from this venue;
- the client must also be able to reserve only PSE services without being forced to reserve a venue;
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
- public PSE catalog and profile detail pages exist; individual service detail pages are still missing;
- event project detail should show each provider/entity as its own negotiation/invoice lane, but each lane must remain grouped by reservation/project rather than split into one reservation row per selected service;
- notification center is missing;
- payment gateway UX is still test-only;
- SAP ticketing needs a full management UX;
- public homepage sections should eventually be driven by analytics/popularity, not random/demo logic only;
- design tokens/components should be extracted to reduce repeated Tailwind classes.

## Reference Site Analysis

The user repeatedly asked to stay close to the Eventsinminutes experience. Do not copy copyrighted assets or proprietary implementation, but use the same product logic and visual rhythm.

Homepage reference traits:

- lightweight top navigation with logo on the left and compact action/account/cart controls on the right;
- hero centered in the first viewport;
- pill-shaped trust badge above the headline;
- large but controlled headline with one blue highlighted word;
- short supporting sentence;
- large rounded search bar with grouped fields: what, where, when, action button;
- category carousel directly under hero search, with circular icons and readable labels;
- trusted/team logos or credibility strip below;
- content sections use horizontal discovery, not long vertical grids only.

Venue search reference traits:

- search intent is preserved in the URL/query string;
- filters are immediately available and scannable;
- cards highlight image, title, place, distance/context, price and trust signals;
- the UX lets users compare many options quickly.

Venue detail reference traits:

- breadcrumb, title, location and category near top;
- image gallery has one large image plus smaller images and a "view all photos" action;
- compact chips for capacity, duration, surface, location and request/payment type;
- sticky section tabs: photos, overview, included, space/amenities, add-ons, location/times, things to know;
- owner contact/action area is visible but not noisy;
- reservation/pricing card is sticky on desktop;
- long details are organized with accordions and clean tables;
- similar venues carousel appears near the bottom;
- footer closes the page with brand, links and contact.

Important adaptation for BAOBAA:

- BAOBAA is francophone and pan-African, so all public copy must be in French with accents;
- BAOBAA has PEE, PSE and SAP ticketing flows, so the reservation experience must go beyond the reference and support event composition;
- BAOBAA must avoid showing internal platform mechanics publicly;
- payment/commission/subscription language belongs in dashboards, not in public browsing.

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
- `/espaces/{slug}/disponibilites-bloquees` JSON endpoint for blocked dates/slots.
- `/partenaires` public PEE profiles.
- `/partenaires/{ownerProfile}` public PEE detail/list of spaces.
- `/prestataires` public PSE profile catalog.
- `/prestataires/{serviceProviderProfile}` public PSE profile detail with published services and reservation CTA.
- `/services-evenementiels` dedicated PSE-only service booking/composition page.
- `/composer-mon-evenement` legacy/compatible standalone event composer route for clients who may not want a venue first.

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
- `/prestataire/reservations`
- `/prestataire/reservations/{eventProject}`
- `/prestataire/reservations/{eventProject}/messages`
- `/prestataire/reservations/{eventProject}/proforma/confirmer`
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
- `EventProjectMessage`
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
- `AvailabilityHoldService`: creates and converts 10-minute temporary availability holds for venues and event services, and prevents overlapping reservations under concurrency.
- `ProformaInvoiceService`: creates/updates proforma invoice for booking.
- `BookingDepositService`: computes deposit/acompte using SAP/PEE rules.
- `PartnerLogoService`: stores and signs PEE/PSE logos.
- `VenueMediaStorageService`: stores and signs venue media on Wasabi/S3-compatible storage.

## Availability And Anti-Overlap Rules

This area is security-sensitive. Do not rely on frontend calendars only.

Implemented model/service:

- `AvailabilityHold`
- `AvailabilityHoldStatus`
- `AvailabilityHoldService`

Hold duration:

- maximum 10 minutes;
- active holds block the resource while `status = active` and `expires_at > now()`;
- expired active holds no longer block availability;
- converted holds are attached to `Booking` and/or `EventProject`.

Resources protected:

- `Venue`
- `EventService`

Backend behavior:

- venue booking creation uses `AvailabilityHoldService` to lock the venue inside the booking transaction;
- standalone event composer uses `AvailabilityHoldService` to lock selected event services;
- overlapping confirmed/active business records must block new requests;
- conflict detection must happen server-side immediately before persisting a booking/project.

Public catalog behavior:

- `/espaces` excludes spaces already unavailable for the searched date range;
- exclusions include blocking booking statuses, non-expired pending payment bookings, active non-expired holds, and explicit unavailable owner dates;
- search suggestions also respect the selected date range so unavailable spaces are not suggested;
- date filtering uses index-friendly date bounds, not `whereDate()` on indexed columns when a range can be used.

Blocked date endpoint:

- route name: `venues.blocked-dates`;
- URL: `/espaces/{slug}/disponibilites-bloquees`;
- returns `blocked_dates` and `blocked_slots`;
- use this endpoint for future public calendar disabling/warnings on venue detail or reservation pages.

Blocking booking statuses for venue availability:

- `draft`;
- `pending_owner`;
- `pending_payment` if not expired;
- `confirmed`;
- `disputed`.

Important test coverage:

- `tests/Feature/PortalAuthenticationTest.php` checks `/espaces` excludes booked/held venues for selected dates;
- `tests/Feature/BookingRequestTest.php` checks venue booking overlap lock;
- `tests/Feature/EventComposerTest.php` checks event service overlap lock.

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

Availability note:

- `BookingController::store()` creates the booking through `AvailabilityHoldService`.
- If another client already holds or reserved the same venue/time range, the request must fail gracefully and tell the client the slot is no longer available.
- Keep this server-side guard even if the UI later disables dates visually.

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

- PSE services and ticketing are stored as project items.
- PSE-side reservation list/detail exists and is grouped by `EventProject`, not by `EventProjectItem`.
- PSE-side negotiation messages are stored in `EventProjectMessage` with `provider_type` and `provider_id`.
- PSE-side proforma confirmation applies to every `event_service` item for the same provider inside the reservation.
- `EventProjectItemMessage` exists from an earlier implementation attempt but is not the intended current flow for grouped PSE reservations.
- Client-side detail/reply/confirmation for standalone PSE reservations and independent payment per provider lane still need dedicated UI/workflow.

## Grouped Reservation Rule

This rule is important and must not be regressed.

- A client reservation is a single business object represented by `EventProject`.
- `EventProjectItem` rows are internal line items: venue, service, ticketing, modules or provider-specific selections.
- Do not display `/prestataire/reservations` as one row per `EventProjectItem`; display one row per `EventProject`.
- A reservation row can summarize multiple services selected from the same PSE.
- PSE negotiation operates on the provider's lane inside the `EventProject`: one message thread, one global proposed amount, one provider-side confirmation.
- When a PSE proposes a global amount, distribute that amount across that PSE's service items only for calculation consistency, then refresh project totals through `EventProjectService::refreshTotals()`.
- Counts and dashboard metrics should count distinct `event_project_id` for reservation volume.
- Route model binding for PSE reservation detail uses `{eventProject}`.

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

- public PSE profile catalog/detail pages exist, but there is no individual public service detail page yet;
- PSE-side negotiation and provider-side proforma confirmation exist at grouped `EventProject` level for the provider's services;
- client-side service item detail/reply/confirmation still needs a dedicated UI;
- independent payment per provider lane is not implemented yet.

Availability note:

- `EventComposerController::store()` locks selected `EventService` records through `AvailabilityHoldService`;
- if one selected service is already held or reserved for the requested period, the project should not be created and the user should choose another slot/service;
- service availability must remain protected even if future UI adds richer calendars.

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
- `/prestataire/reservations`
- `/prestataire/reservations/{eventProject}`
- `/prestataire/reservations/{eventProject}/messages`
- `/prestataire/reservations/{eventProject}/proforma/confirmer`
- `/prestataire/parametres`

PSE can:

- create services;
- select existing SAP-defined service type;
- define name, descriptions, location, service area, pricing unit, starting price, deposit amount, attributes and availability notes;
- publish/unpublish services;
- delete services;
- update logo/profile.
- view service reservations from `/prestataire/reservations`;
- open a grouped reservation detail page containing all of this PSE's services for the client event;
- negotiate one global provider-side amount through `EventProjectMessage`;
- confirm the grouped service proforma from the provider side.

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
- public labels must use marketing language, not internal wording; prefer `Services additionnels`, `Options du lieu`, `Prestations disponibles`, `partenaire`, `équipe` or `expert`, and avoid visible labels such as `modules`, `fournisseur`, `PSE`, `PEE` or `SAP`;
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

## Public PSE Profile Pages

Routes:

- `/prestataires`
- `/prestataires/{serviceProviderProfile}`

Implementation:

- controller: `PublicServiceProviderProfileController`;
- views: `resources/views/service-provider-profiles/index.blade.php` and `resources/views/service-provider-profiles/show.blade.php`;
- menu label: `Prestataires`, never visible `PSE`;
- list only verified profiles with at least one published service;
- detail lists only published services for that provider;
- reservation CTA links to `/services-evenementiels?prestataire={public_uuid}` so the booking/composer page opens with the selected provider.

Public wording rule:

- use `prestataires`, `équipes`, `experts`, `services événementiels` and `services disponibles`;
- avoid public labels like `PSE`, `fournisseur`, `provider`, internal status names or database terms.

## Reservation Entry Modes

The platform must support two public client entry modes.

Mode 1: venue-first event package:
- entry point: venue detail page `/espaces/{slug}`;
- primary CTA: `Composer mon événement`;
- client starts from a selected venue;
- modal/stepper lets the client reserve or negotiate the venue, then add PEE modules, PSE services and optional SAP ticketing;
- saving creates a `Booking` for the venue and an `EventProject` containing related `EventProjectItem` rows.

Mode 2: PSE-only service booking:
- discovery entry point: `/prestataires`;
- provider detail entry point: `/prestataires/{serviceProviderProfile}`;
- booking/composition entry point: `/services-evenementiels`;
- compatible legacy entry point: `/composer-mon-evenement`;
- main public menu should link to `/prestataires`, not directly to `/services-evenementiels`;
- future entry points can include individual service detail pages;
- client does not have to select or reserve a venue;
- client can choose one or many PSE services, negotiate if needed, then get proforma/payment flow per provider in a later implementation;
- saving creates an `EventProject` with `event_service` items and no required `venue_booking` item.

Important UX rule:
- never force a client who only needs a provider service, e.g. sound, lighting, podium, camera, photo, furniture, decoration, to reserve a venue first.

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

- production path used by the user previously: `/home/baobaa/app`;
- production Linux user used previously: `baobaa`;
- branch used previously: `main`;
- run migrations after pulling code;
- run Composer install/update when dependencies changed;
- build frontend assets after Blade/CSS/JS changes;
- ensure storage/cache directories are writable by the app user.

Recommended production deployment flow:

```bash
cd /home/baobaa/app

sudo -u baobaa -H git status --short --branch
sudo -u baobaa -H git fetch origin
sudo -u baobaa -H git pull --ff-only origin main

sudo -u baobaa -H composer install --no-dev --optimize-autoloader
sudo -u baobaa -H php artisan optimize:clear
sudo -u baobaa -H php artisan migrate --force
sudo -u baobaa -H npm ci
sudo -u baobaa -H npm run build
sudo -u baobaa -H php artisan config:cache
sudo -u baobaa -H php artisan route:cache
sudo -u baobaa -H php artisan view:cache
```

If the server does not build assets directly, build locally and deploy the generated `public/build` according to the server strategy.

After deployment, verify:

```bash
sudo -u baobaa -H php artisan route:list --except-vendor
sudo -u baobaa -H php artisan baobaa:diagnose-wasabi-images
tail -50 storage/logs/laravel.log
```

If the project uses PHP-FPM/queue workers, restart/reload them according to the server setup after cache rebuilds.

Server file ownership and writable folders:

```bash
sudo chown -R baobaa:www-data storage bootstrap/cache
sudo chmod -R ug+rwX storage bootstrap/cache
```

Production `.env` checklist:

- `APP_ENV=production`;
- `APP_DEBUG=false`;
- `APP_URL` must match the real domain;
- database credentials must be production values;
- Wasabi variables must be present with production values;
- mail/payment provider variables must be production-ready before real launch;
- never commit `.env`.

Wasabi production checklist:

- install/keep the S3 adapter dependency required by Laravel Flysystem;
- confirm bucket, endpoint, region and URL match Wasabi;
- confirm uploaded objects are readable through signed URLs generated by the app;
- run `php artisan baobaa:diagnose-wasabi-images` after deployment;
- if images open directly but not in the app, inspect signed URL generation, CORS, mixed-content HTTPS, cached config and storage disk config.

Common deployment issues already seen:

- dirty server worktree from `.gitignore` files under `storage` and `bootstrap/cache`;
- untracked `error_log`;
- missing S3/Flysystem adapter causing `PortableVisibilityConverter` errors;
- stale config/view cache after changing `.env`, filesystem disk config, routes or Blade views;
- frontend not updated because `npm run build` was not executed.

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
- `resources/views/service-provider-profiles/index.blade.php`
- `resources/views/service-provider-profiles/show.blade.php`
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
- `PublicOwnerProfileController`
- `PublicServiceProviderProfileController`
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

- Do not rely on a hardcoded total because tests are evolving quickly.
- For the availability/provider discovery work, the latest verified focused commands are:

```bash
vendor/bin/pint --format agent
php artisan view:cache --no-interaction
php artisan test --compact tests/Feature/PortalAuthenticationTest.php --filter='public venues search excludes booked and temporarily blocked dates|public venues index lists published spaces'
php artisan test --compact tests/Feature/EventComposerTest.php --filter='service booking blocks overlapping reservations'
php artisan test --compact tests/Feature/BookingRequestTest.php --filter='venue booking blocks overlapping reservations'
```

If only editing Markdown, tests are not required, but still check for secrets before commit.

## Current Known Limitations

These are intentional unfinished areas, not bugs unless the user asks for them now:

- reservation form should be moved to a modal/stepper for premium UX;
- client-side UI for grouped PSE negotiation/confirmation still needs to be completed;
- PSE services do not yet have independent proforma/payment records per provider lane;
- SAP ticketing request is stored, but SAP does not yet manage ticketing packages/forms/proformas;
- individual public service detail pages are not yet implemented;
- notifications are not yet fully implemented;
- real payment gateway is not integrated; current payment is test flow.

## Recommended Next Steps

Strongest next implementation path:

1. Replace venue detail reservation sidebar form with compact CTA plus modal stepper.
2. Complete client-side grouped PSE negotiation and confirmation UI using `EventProjectMessage`.
3. Add proforma/payment records per provider lane or a polymorphic invoice source, while keeping `EventProjectItem` as internal line items.
4. Build SAP ticketing management: ticket categories, fee rule, commission or fixed BAOBAA fee.
5. Build individual public service detail pages.
6. Add notification events for booking/proforma/payment/portal validation.
7. Extend the public calendars to consume `/espaces/{slug}/disponibilites-bloquees` and visually disable/warn blocked dates.

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

- `0c1e6e5 feat: secure event availability and provider discovery`
