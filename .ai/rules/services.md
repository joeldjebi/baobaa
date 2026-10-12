---
paths:
  - 'app/Services/**'
---

# Services

## Marketplace stock, deposit and provider visibility rules
Rental availability = stock − rented quantities of overlapping order items whose order is Paid/Preparing/Delivered (ProductInventoryService); sales decrement stock_quantity at checkout and are restored on cancel. Checkout (CheckoutService) re-checks availability with lockForUpdate and creates one order + payment per provider.
Deposit (caution) is Held at payment and must be Released/Retained (reason required) after the Returned status before an order can be Completed (OrderFulfillmentService).
Anything shown to or bookable by clients (EventService, Product) must use the publiclyAvailable() scope: published + provider verified + provider account active.
