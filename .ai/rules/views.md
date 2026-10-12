---
paths:
  - 'resources/views/**'
---

# Views

## No demo/fallback data on public pages
Public pages must never invent content (fallback venues/partners/providers, stock Unsplash photos, fake ratings or counters). Hide empty sections instead; unknown or unpublished slugs return 404.
Venue images: use $venue->cover_image_url / publicImageUrls() (primary image first, no videos, no rejected media, SVG placeholder). Ratings: query ->withPublicRating() (approved reviews only); never display venues.average_rating/reviews_count.
Counters must come from full-dataset queries, never from the current paginated page.
