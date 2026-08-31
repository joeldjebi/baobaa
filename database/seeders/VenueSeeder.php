<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VenueStatus;
use App\Enums\VerificationStatus;
use App\Models\Booking;
use App\Models\OwnerProfile;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenueCategory;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class VenueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $owners = OwnerProfile::query()->get()->keyBy('slug');
        $categories = VenueCategory::query()->get()->keyBy('slug');

        $venues = [
            [
                'owner' => 'baobaa-signature-events',
                'category' => 'salles',
                'name' => 'Auditorium premium avec scène et régie',
                'slug' => 'auditorium-premium-avec-scene-et-regie',
                'city' => 'Abidjan',
                'country_code' => 'CI',
                'district' => 'Plateau',
                'min_capacity' => 80,
                'max_capacity' => 450,
                'surface_area' => 650,
                'starting_price' => 350000,
                'reservation_amount' => 100000,
                'rating' => 4.9,
                'reviews' => 38,
                'image' => 'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?auto=format&fit=crop&w=1200&q=85',
                'description' => 'Un auditorium central pensé pour conférences, lancements de produits, concerts intimistes et cérémonies premium.',
            ],
            [
                'owner' => 'baobaa-signature-events',
                'category' => 'mariages',
                'name' => 'Salle de réception lumineuse pour mariage',
                'slug' => 'salle-de-reception-lumineuse-pour-mariage',
                'city' => 'Abidjan',
                'country_code' => 'CI',
                'district' => 'Cocody',
                'min_capacity' => 120,
                'max_capacity' => 700,
                'surface_area' => 1100,
                'starting_price' => 500000,
                'reservation_amount' => 150000,
                'rating' => 5.0,
                'reviews' => 52,
                'image' => 'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=1200&q=85',
                'description' => 'Une grande salle lumineuse avec terrasse, parking et espace traiteur pour réceptions, mariages et grands dîners.',
            ],
            [
                'owner' => 'kora-prestige-venues',
                'category' => 'conferences',
                'name' => 'Salle de conférence équipée avec terrasse',
                'slug' => 'conference-room-equipee-avec-terrasse',
                'city' => 'Dakar',
                'country_code' => 'SN',
                'district' => 'Almadies',
                'min_capacity' => 12,
                'max_capacity' => 90,
                'surface_area' => 240,
                'starting_price' => 85000,
                'reservation_amount' => 35000,
                'rating' => 4.8,
                'reviews' => 24,
                'image' => 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1200&q=85',
                'description' => 'Un espace professionnel lumineux avec terrasse, Wi-Fi haut débit, mobilier modulable et équipements de présentation.',
            ],
            [
                'owner' => 'azalai-garden-collection',
                'category' => 'jardins',
                'name' => 'Jardin événementiel privatisable',
                'slug' => 'jardin-evenementiel-privatisable',
                'city' => 'Cotonou',
                'country_code' => 'BJ',
                'district' => 'Fidjrossè',
                'min_capacity' => 60,
                'max_capacity' => 400,
                'surface_area' => 1500,
                'starting_price' => 220000,
                'reservation_amount' => 80000,
                'rating' => 4.9,
                'reviews' => 31,
                'image' => 'https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?auto=format&fit=crop&w=1200&q=85',
                'description' => 'Un jardin privatisable pour réceptions, cocktails, cérémonies en plein air et événements de marque.',
            ],
            [
                'owner' => 'kora-prestige-venues',
                'category' => 'rooftops',
                'name' => 'Rooftop panoramique pour cocktail privé',
                'slug' => 'rooftop-panoramique-pour-cocktail-prive',
                'city' => 'Dakar',
                'country_code' => 'SN',
                'district' => 'Plateau',
                'min_capacity' => 40,
                'max_capacity' => 180,
                'surface_area' => 320,
                'starting_price' => 180000,
                'reservation_amount' => 60000,
                'rating' => 4.7,
                'reviews' => 19,
                'image' => 'https://images.unsplash.com/photo-1519671482749-fd09be7ccebf?auto=format&fit=crop&w=1200&q=85',
                'description' => 'Un rooftop urbain avec vue dégagée, idéal pour cocktails, afterworks, anniversaires et lancements confidentiels.',
            ],
            [
                'owner' => 'azalai-garden-collection',
                'category' => 'hotels',
                'name' => 'Salon hôtelier modulable avec service premium',
                'slug' => 'salon-hotelier-modulable-avec-service-premium',
                'city' => 'Cotonou',
                'country_code' => 'BJ',
                'district' => 'Haie Vive',
                'min_capacity' => 30,
                'max_capacity' => 220,
                'surface_area' => 410,
                'starting_price' => 260000,
                'reservation_amount' => 90000,
                'rating' => 4.8,
                'reviews' => 27,
                'image' => 'https://images.unsplash.com/photo-1517457373958-b7bdd4587205?auto=format&fit=crop&w=1200&q=85',
                'description' => 'Un salon hôtelier élégant avec accueil, restauration, sonorisation et configuration flexible pour séminaires premium.',
            ],
            [
                'owner' => 'pee-demo-prestige',
                'category' => 'mariages',
                'name' => 'Villa événementielle lagune avec jardin privé',
                'slug' => 'villa-evenementielle-lagune-avec-jardin-prive',
                'city' => 'Abidjan',
                'country_code' => 'CI',
                'district' => 'Cocody',
                'min_capacity' => 80,
                'max_capacity' => 350,
                'surface_area' => 980,
                'starting_price' => 420000,
                'reservation_amount' => 120000,
                'rating' => 4.9,
                'reviews' => 18,
                'image' => 'https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?auto=format&fit=crop&w=1200&q=85',
                'description' => 'Une villa élégante avec jardin, terrasse et vue lagune pour mariages, cocktails privés et célébrations premium.',
            ],
            [
                'owner' => 'pee-demo-prestige',
                'category' => 'conferences',
                'name' => 'Studio conférence exécutif avec lounge privé',
                'slug' => 'studio-conference-executif-avec-lounge-prive',
                'city' => 'Abidjan',
                'country_code' => 'CI',
                'district' => 'Plateau',
                'min_capacity' => 20,
                'max_capacity' => 120,
                'surface_area' => 360,
                'starting_price' => 180000,
                'reservation_amount' => 60000,
                'rating' => 4.8,
                'reviews' => 12,
                'image' => 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1200&q=85',
                'description' => 'Un studio professionnel avec lounge, écran de présentation, Wi-Fi haut débit et configuration modulable pour réunions stratégiques.',
            ],
            [
                'owner' => 'pee-demo-prestige',
                'category' => 'salles',
                'name' => 'Maison Bleue Signature pour événements privés',
                'slug' => 'maison-bleue-signature-evenements-prives',
                'city' => 'Abidjan',
                'country_code' => 'CI',
                'district' => 'Riviera Golf',
                'address' => 'Boulevard Mitterrand, Riviera Golf, Abidjan',
                'latitude' => 5.3621120,
                'longitude' => -3.9518020,
                'min_capacity' => 40,
                'max_capacity' => 280,
                'surface_area' => 720,
                'starting_price' => 380000,
                'reservation_amount' => 125000,
                'rating' => 4.95,
                'reviews' => 6,
                'image' => 'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=1600&q=85',
                'description' => 'Une adresse premium pensée pour les mariages élégants, cocktails de marque, anniversaires privés et séminaires direction. La Maison Bleue Signature combine un grand salon climatisé, une terrasse couverte, un jardin éclairé et une logistique événementielle haut de gamme.',
                'highlights' => [
                    'Adresse vérifiée à Riviera Golf',
                    'Réponse propriétaire sous 2 heures',
                    'Salon, terrasse et jardin privatisables',
                    'Stationnement sécurisé',
                    'Équipe d’accueil disponible',
                ],
                'included_items' => [
                    'Wi-Fi haut débit',
                    'Climatisation du salon principal',
                    'Tables rondes et rectangulaires',
                    'Chaises premium pour 120 invités',
                    'Éclairage architectural',
                    'Parking sécurisé',
                    'Toilettes invités et accès PMR',
                    'Espace traiteur séparé',
                    'Présence d’un régisseur sur place',
                ],
                'space_details' => [
                    'amenities' => [
                        ['name' => 'Espace et aménagement', 'detail' => 'Salon intérieur, terrasse couverte, jardin privatif et zone cocktail modulable.'],
                        ['name' => 'Mobilier et sièges', 'detail' => 'Chaises premium, tables rondes, tables buffet et possibilité de réaménagement avant événement.'],
                        ['name' => 'Audiovisuel et technologie', 'detail' => 'Wi-Fi, prises murales, écran mobile, régie audio disponible en module complémentaire.'],
                        ['name' => 'Climat et confort', 'detail' => 'Salon climatisé, ventilation naturelle sur terrasse et éclairage extérieur scénarisé.'],
                        ['name' => 'Cuisine et traiteur', 'detail' => 'Office traiteur indépendant, arrivée d’eau, zone de dressage et accès fournisseur.'],
                        ['name' => 'Sécurité et accès', 'detail' => 'Gardiennage, parking surveillé, accès invité séparé et entrée fournisseur.'],
                    ],
                ],
                'house_rules' => [
                    'Installation autorisée jusqu’à 3 heures avant le début de l’événement.',
                    'Musique amplifiée autorisée jusqu’à 23h30 selon le format validé.',
                    'Traiteur externe accepté après validation du propriétaire.',
                    'Feux d’artifice, fumigènes et flammes nues non autorisés.',
                    'Remise en état obligatoire à la fin de l’événement.',
                ],
                'location_details' => [
                    'public_note' => 'L’adresse exacte est communiquée après validation de la demande. Le quartier est accessible depuis Cocody, Angré et le Plateau.',
                    'parking' => 'Parking intérieur sécurisé pour 45 véhicules, avec stationnement complémentaire à proximité.',
                    'access' => 'Accès invité principal et accès fournisseur séparé pour préserver la fluidité de l’événement.',
                ],
                'availability_notes' => [
                    'minimum_notice_hours' => 48,
                    'confirmation_delay' => 'Réponse sous 2 heures ouvrées.',
                    'visit_policy' => 'Visite sur rendez-vous après présélection.',
                ],
                'gallery' => [
                    'https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?auto=format&fit=crop&w=1600&q=85',
                    'https://images.unsplash.com/photo-1517457373958-b7bdd4587205?auto=format&fit=crop&w=1600&q=85',
                    'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&w=1600&q=85',
                    'https://images.unsplash.com/photo-1527529482837-4698179dc6ce?auto=format&fit=crop&w=1600&q=85',
                ],
                'rates' => [
                    ['name' => 'Demi-journée premium', 'rate_type' => 'half_day', 'price' => 380000, 'min_hours' => 4, 'max_hours' => 6, 'min_guests' => 40, 'max_guests' => 120, 'is_default' => true, 'conditions' => 'Convient aux réunions, cocktails courts et événements presse.'],
                    ['name' => 'Journée complète', 'rate_type' => 'day', 'price' => 650000, 'min_hours' => 8, 'max_hours' => 12, 'min_guests' => 80, 'max_guests' => 220, 'is_default' => false, 'conditions' => 'Inclut l’accès salon, terrasse et jardin sur la journée.'],
                    ['name' => 'Soirée privée', 'rate_type' => 'evening', 'price' => 850000, 'min_hours' => 6, 'max_hours' => 10, 'min_guests' => 100, 'max_guests' => 280, 'is_default' => false, 'conditions' => 'Recommandé pour mariages, anniversaires et lancements de marque.'],
                ],
                'configurations' => [
                    ['name' => 'Cocktail debout', 'capacity' => 280, 'icon' => 'users'],
                    ['name' => 'Banquet assis', 'capacity' => 180, 'icon' => 'utensils'],
                    ['name' => 'Conférence', 'capacity' => 140, 'icon' => 'presentation'],
                    ['name' => 'Cérémonie extérieure', 'capacity' => 220, 'icon' => 'sparkles'],
                ],
                'add_ons' => [
                    ['name' => 'Sonorisation premium', 'description' => 'Micros, enceintes, console et technicien pendant l’événement.', 'price' => 150000],
                    ['name' => 'Service café et thé', 'description' => 'Pause chaude avec café, thé, eau et petits fours secs.', 'price' => 50000],
                    ['name' => 'Mobilier supplémentaire', 'description' => 'Chaises et tables additionnelles selon configuration retenue.', 'price' => 75000],
                    ['name' => 'Accueil protocolaire', 'description' => 'Deux hôtesses pour orientation, émargement et gestion des invités.', 'price' => 95000],
                    ['name' => 'Éclairage ambiance jardin', 'description' => 'Mise en lumière décorative de la terrasse et du jardin.', 'price' => 120000],
                ],
                'policies' => [
                    ['policy_type' => 'annulation', 'title' => 'Annulation', 'summary' => 'Annulation gratuite jusqu’à 7 jours avant la date validée.', 'content' => 'Entre 7 jours et 48 heures avant l’événement, le montant de réservation reste acquis au propriétaire. À moins de 48 heures, la réservation est non remboursable.'],
                    ['policy_type' => 'reservation', 'title' => 'Demande de réservation', 'summary' => 'Le propriétaire confirme chaque demande avant paiement final.', 'content' => 'La demande est étudiée selon le format, les horaires, le nombre d’invités et les besoins logistiques. Une réponse est envoyée sous 2 heures ouvrées.'],
                    ['policy_type' => 'restauration', 'title' => 'Restauration', 'summary' => 'Traiteur externe accepté après validation.', 'content' => 'Le traiteur doit respecter les horaires de livraison, utiliser l’espace prévu et laisser les zones techniques propres après intervention.'],
                    ['policy_type' => 'son', 'title' => 'Musique et sonorisation', 'summary' => 'Musique amplifiée autorisée selon horaires.', 'content' => 'La musique amplifiée est autorisée jusqu’à 23h30. Au-delà, le volume doit être réduit conformément aux règles du quartier.'],
                    ['policy_type' => 'securite', 'title' => 'Sécurité', 'summary' => 'Gardiennage obligatoire pour les événements de plus de 150 invités.', 'content' => 'Le propriétaire peut imposer un dispositif de sécurité adapté au format de l’événement, notamment pour les soirées privées et événements de marque.'],
                ],
                'faqs' => [
                    ['question' => 'Peut-on visiter l’espace avant de réserver ?', 'answer' => 'Oui, une visite peut être programmée après présélection de la date et du format d’événement.'],
                    ['question' => 'Le traiteur est-il imposé ?', 'answer' => 'Non, vous pouvez venir avec votre traiteur après validation des conditions d’accès fournisseur.'],
                    ['question' => 'Le mobilier inclus suffit-il pour un mariage ?', 'answer' => 'Le mobilier inclus couvre 120 invités. Des tables et chaises supplémentaires peuvent être ajoutées en module.'],
                    ['question' => 'L’adresse exacte est-elle visible publiquement ?', 'answer' => 'Non, seule la zone est affichée. L’adresse complète est communiquée après validation de la demande.'],
                    ['question' => 'Puis-je réserver pour un événement d’entreprise ?', 'answer' => 'Oui, l’espace convient aux cocktails corporate, lancements de produit, séminaires direction et dîners privés.'],
                ],
                'review_items' => [
                    ['name' => 'Aminata Coulibaly', 'email' => 'aminata.client@baobaa.local', 'rating' => 5, 'title' => 'Très premium', 'comment' => 'L’espace est élégant, bien situé et l’équipe a été très réactive pendant toute la préparation.'],
                    ['name' => 'Marc-André Kouamé', 'email' => 'marc.client@baobaa.local', 'rating' => 5, 'title' => 'Parfait pour un lancement', 'comment' => 'La terrasse et le jardin ont donné beaucoup de cachet à notre lancement de marque.'],
                    ['name' => 'Nadia Traoré', 'email' => 'nadia.client@baobaa.local', 'rating' => 5, 'title' => 'Organisation fluide', 'comment' => 'Parking, accueil, traiteur, tout était bien coordonné. Je recommande pour les événements privés.'],
                ],
            ],
        ];

        foreach ($venues as $venueData) {
            $venue = Venue::query()->updateOrCreate(
                ['slug' => $venueData['slug']],
                [
                    'owner_profile_id' => $owners->get($venueData['owner'])?->id,
                    'venue_category_id' => $categories->get($venueData['category'])?->id,
                    'name' => $venueData['name'],
                    'short_description' => $venueData['description'],
                    'description' => $venueData['description'],
                    'status' => VenueStatus::Published,
                    'verification_status' => VerificationStatus::Verified,
                    'booking_mode' => 'request',
                    'country_code' => $venueData['country_code'],
                    'city' => $venueData['city'],
                    'district' => $venueData['district'],
                    'address' => $venueData['address'] ?? null,
                    'latitude' => $venueData['latitude'] ?? null,
                    'longitude' => $venueData['longitude'] ?? null,
                    'min_capacity' => $venueData['min_capacity'],
                    'max_capacity' => $venueData['max_capacity'],
                    'surface_area' => $venueData['surface_area'],
                    'currency' => 'XOF',
                    'starting_price' => $venueData['starting_price'],
                    'reservation_amount' => $venueData['reservation_amount'],
                    'highlights' => $venueData['highlights'] ?? [
                        'Lieu vérifié',
                        'Réponse rapide',
                        'Demande de réservation',
                        'Sur place',
                    ],
                    'included_items' => $venueData['included_items'] ?? [
                        'Wi-Fi',
                        'Parking à proximité',
                        'Toilettes accessibles',
                        'Éclairage de base',
                    ],
                    'space_details' => $venueData['space_details'] ?? [
                        'amenities' => [
                            ['name' => 'Espace et aménagement', 'detail' => 'Configuration modulable selon le format de l’événement.'],
                            ['name' => 'Accès et confort', 'detail' => 'Accès invité fluide et commodités principales disponibles.'],
                        ],
                    ],
                    'house_rules' => $venueData['house_rules'] ?? [
                        'Respect des horaires validés avec le propriétaire.',
                        'Remise en état obligatoire après l’événement.',
                    ],
                    'location_details' => $venueData['location_details'] ?? [
                        'public_note' => 'L’adresse exacte est communiquée après validation de la demande.',
                    ],
                    'availability_notes' => $venueData['availability_notes'] ?? [
                        'confirmation_delay' => 'Réponse selon disponibilité du propriétaire.',
                    ],
                    'average_rating' => $venueData['rating'],
                    'reviews_count' => $venueData['reviews'],
                    'published_at' => now()->subDays(7),
                    'approved_at' => now()->subDays(7),
                ],
            );

            $mediaItems = array_merge([$venueData['image']], $venueData['gallery'] ?? []);
            foreach ($mediaItems as $mediaIndex => $image) {
                $venue->media()->updateOrCreate(
                    ['sort_order' => $mediaIndex + 1],
                    [
                        'type' => 'image',
                        'disk' => 'public',
                        'path' => $image,
                        'alt_text' => $venueData['name'],
                        'is_primary' => $mediaIndex === 0,
                        'moderation_status' => 'approved',
                    ],
                );
            }

            foreach ($venueData['rates'] ?? [] as $index => $rate) {
                $venue->rates()->updateOrCreate(
                    ['name' => $rate['name']],
                    [
                        'rate_type' => $rate['rate_type'],
                        'price' => $rate['price'],
                        'currency' => 'XOF',
                        'min_hours' => $rate['min_hours'],
                        'max_hours' => $rate['max_hours'],
                        'min_guests' => $rate['min_guests'],
                        'max_guests' => $rate['max_guests'],
                        'is_default' => $rate['is_default'],
                        'is_active' => true,
                        'conditions' => $rate['conditions'],
                    ],
                );
            }

            foreach ($venueData['configurations'] ?? [] as $index => $configuration) {
                $venue->configurations()->updateOrCreate(
                    ['name' => $configuration['name']],
                    [
                        'capacity' => $configuration['capacity'],
                        'icon' => $configuration['icon'] ?? null,
                        'is_active' => true,
                        'sort_order' => $index + 1,
                    ],
                );
            }

            foreach ($venueData['add_ons'] ?? [] as $index => $addOn) {
                $venue->addOns()->updateOrCreate(
                    ['name' => $addOn['name']],
                    [
                        'description' => $addOn['description'],
                        'price' => $addOn['price'],
                        'currency' => 'XOF',
                        'is_available' => true,
                        'sort_order' => $index + 1,
                    ],
                );
            }

            foreach ($venueData['policies'] ?? [] as $index => $policy) {
                $venue->policies()->updateOrCreate(
                    ['policy_type' => $policy['policy_type']],
                    [
                        'title' => $policy['title'],
                        'summary' => $policy['summary'],
                        'content' => $policy['content'],
                        'is_highlighted' => $index < 3,
                        'sort_order' => $index + 1,
                    ],
                );
            }

            foreach ($venueData['faqs'] ?? [] as $index => $faq) {
                $venue->faqs()->updateOrCreate(
                    ['question' => $faq['question']],
                    [
                        'answer' => $faq['answer'],
                        'is_active' => true,
                        'sort_order' => $index + 1,
                    ],
                );
            }

            foreach ($venueData['review_items'] ?? [] as $index => $reviewItem) {
                $client = User::query()->updateOrCreate(
                    ['email' => $reviewItem['email']],
                    [
                        'name' => $reviewItem['name'],
                        'role' => UserRole::Client,
                        'portal_roles' => [UserRole::Client->value],
                        'status' => UserStatus::Active,
                        'email_verified_at' => now(),
                        'password' => Hash::make('password'),
                    ],
                );
                $booking = Booking::query()->updateOrCreate(
                    ['reference' => 'BKB-FULL-'.($index + 1)],
                    [
                        'client_id' => $client->id,
                        'owner_profile_id' => $venue->owner_profile_id,
                        'venue_id' => $venue->id,
                        'venue_rate_id' => $venue->rates()->where('is_default', true)->value('id'),
                        'status' => BookingStatus::Completed,
                        'booking_mode' => 'request',
                        'event_type' => 'evenement-prive',
                        'event_date' => now()->subDays(20 + $index)->toDateString(),
                        'starts_at' => '16:00:00',
                        'ends_at' => '23:00:00',
                        'guests_count' => 120 + ($index * 20),
                        'currency' => 'XOF',
                        'total_amount' => 650000,
                        'reservation_amount' => 125000,
                        'client_notes' => 'Réservation de démonstration pour alimenter les avis publics.',
                        'confirmed_at' => now()->subDays(24 + $index),
                    ],
                );

                $venue->reviews()->updateOrCreate(
                    ['client_id' => $client->id],
                    [
                        'booking_id' => $booking->id,
                        'rating' => $reviewItem['rating'],
                        'title' => $reviewItem['title'],
                        'comment' => $reviewItem['comment'],
                        'status' => 'approved',
                        'approved_at' => now()->subDays(10 + $index),
                    ],
                );
            }

            foreach (range(1, 21) as $day) {
                $venue->availabilities()->updateOrCreate(
                    [
                        'available_date' => CarbonImmutable::today()->addDays($day)->toDateString(),
                        'starts_at' => '08:00:00',
                        'ends_at' => '22:00:00',
                    ],
                    [
                        'status' => 'available',
                        'metadata' => ['source' => 'seed'],
                    ],
                );
            }
        }
    }
}
