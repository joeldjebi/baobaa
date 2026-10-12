@php
    $contact = config('baobaa.contact');
@endphp

<footer {{ $attributes->merge(['class' => 'border-t border-white/80 bg-[#07152f] px-5 py-10 text-white sm:px-8']) }}>
    <div class="mx-auto grid max-w-7xl gap-8 sm:grid-cols-2 lg:grid-cols-[1.3fr_auto_auto_auto] lg:gap-12">
        <div>
            <a href="{{ url('/') }}" class="inline-flex items-center gap-3 text-white">
                <img src="{{ asset('images/baobaa.jpg') }}" alt="BAOBAA" class="h-11 w-auto rounded-2xl object-cover shadow-lg shadow-[#2f6bff]/25" loading="lazy">
            </a>
            <p class="mt-5 max-w-md text-sm font-semibold leading-7 text-white/62">Réservez des salles, jardins, rooftops, auditoriums et espaces professionnels en Afrique francophone, et composez votre événement avec des prestataires de confiance.</p>
        </div>

        <div>
            <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#8dc1ff]">Explorer</p>
            <div class="mt-4 grid gap-2 text-sm font-bold text-white/70">
                <a href="{{ route('venues.index') }}" class="hover:text-white">Espaces</a>
                <a href="{{ route('owner-profiles.index') }}" class="hover:text-white">Partenaires</a>
                <a href="{{ route('service-provider-profiles.index') }}" class="hover:text-white">Prestataires</a>
                <a href="{{ route('event-services.index') }}" class="hover:text-white">Services événementiels</a>
            </div>
        </div>

        <div>
            <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#8dc1ff]">Rejoindre BAOBAA</p>
            <div class="mt-4 grid gap-2 text-sm font-bold text-white/70">
                <a href="{{ route('portal.login', ['portal' => 'client']) }}" class="hover:text-white">Connexion client</a>
                <a href="{{ route('client.register') }}" class="hover:text-white">Créer un compte client</a>
                <a href="{{ route('venues.list-venue') }}" class="hover:text-white">Publier un espace</a>
                <a href="{{ route('service-provider.register') }}" class="hover:text-white">Devenir prestataire</a>
            </div>
        </div>

        <div>
            <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#8dc1ff]">Aide et informations</p>
            <div class="mt-4 grid gap-2 text-sm font-bold text-white/70">
                <a href="{{ route('pages.contact') }}" class="hover:text-white">Nous contacter</a>
                <a href="{{ route('pages.terms') }}" class="hover:text-white">Conditions générales d’utilisation</a>
                <a href="{{ route('pages.privacy') }}" class="hover:text-white">Politique de confidentialité</a>
                <a href="{{ route('pages.legal-notice') }}" class="hover:text-white">Mentions légales</a>
                @if (filled($contact['email']))
                    <a href="mailto:{{ $contact['email'] }}" class="hover:text-white">{{ $contact['email'] }}</a>
                @endif
                @if (filled($contact['phone']))
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $contact['phone']) }}" class="hover:text-white">{{ $contact['phone'] }}</a>
                @endif
            </div>
        </div>
    </div>

    <div class="mx-auto mt-8 flex max-w-7xl flex-wrap items-center justify-between gap-3 border-t border-white/10 pt-5 text-xs font-semibold text-white/45">
        <p>© {{ now()->year }} {{ config('baobaa.legal.company_name') }}. Tous droits réservés.</p>
        <p>Paiement sécurisé · Partenaires vérifiés · Accompagnement de A à Z</p>
    </div>
</footer>
