---
paths:
  - 'app/**'
---

# App

## Shared enums and services for venue options
Payment methods, price units and client types live in App\Enums\PaymentMethod / PriceUnit / ClientType (with French label()); never re-declare label arrays in views.
Country/city/district lists come from LocationOptionsService (SAP-managed ManagedLocation). Partner commission/plan texts come from PartnerConditionsService; deposit wording from BookingDepositService::describe().
Video and 360° tour links must go through MediaEmbedService (HTTPS + host allowlist) before being rendered in an iframe.
