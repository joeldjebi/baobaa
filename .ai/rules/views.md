---
paths:
  - 'resources/views/**'
---

# Views

## No demo/fallback data on public pages
Public pages must never invent content (fallback venues/partners/providers, stock Unsplash photos, fake ratings or counters). Hide empty sections instead; unknown or unpublished slugs return 404.
Venue images: use $venue->cover_image_url / publicImageUrls() (primary image first, no videos, no rejected media, SVG placeholder). Ratings: query ->withPublicRating() (approved reviews only); never display venues.average_rating/reviews_count.
Counters must come from full-dataset queries, never from the current paginated page.

## Never mix inline @php() with @php blocks
In a Blade file that contains any @php ... @endphp block, do not use the inline form @php($x = ...): Blade's block regex starts at the inline @php and swallows everything up to the next @endphp, producing "unexpected endif" parse errors far from the cause. Use block form everywhere in such files.
Also keep a space before directives in text ("total @if", not "total@if"), otherwise the directive is not compiled.
