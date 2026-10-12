@php
    $legal = config('baobaa.legal');
    $contact = config('baobaa.contact');
    $publisherDetails = collect([
        'Raison sociale' => $legal['company_name'],
        'Forme juridique' => $legal['legal_form'],
        'Immatriculation (RCCM)' => $legal['registration_number'],
        'Numéro fiscal' => $legal['tax_number'],
        'Siège social' => $legal['head_office'],
        'Directeur de la publication' => $legal['publication_director'],
        'E-mail' => $contact['email'],
        'Téléphone' => $contact['phone'],
    ])->filter(fn ($value): bool => filled($value));
@endphp

<x-layouts.public-page
    title="Mentions légales"
    eyebrow="Informations légales"
    heading="Mentions légales"
    intro="Informations relatives à l’éditeur et à l’hébergement du site BAOBAA."
>
    <div class="baobaa-prose">
        <h2>Éditeur du site</h2>
        <dl class="mt-4 divide-y divide-[#edf2fb] rounded-2xl border border-[#edf2fb]">
            @foreach ($publisherDetails as $label => $value)
                <div class="grid gap-1 px-4 py-3 sm:grid-cols-[220px_1fr]">
                    <dt class="text-sm font-extrabold text-[#151821]">{{ $label }}</dt>
                    <dd class="text-sm font-semibold text-[#4d5872]">{{ $value }}</dd>
                </div>
            @endforeach
        </dl>

        @if (filled($legal['host']))
            <h2>Hébergement</h2>
            <p>{{ $legal['host'] }}</p>
        @endif

        <h2>Propriété intellectuelle</h2>
        <p>L’ensemble des éléments du site (marque, logo, textes, interface) est la propriété de {{ $legal['company_name'] }} ou de ses partenaires. Toute reproduction sans autorisation est interdite. Les photos et descriptions des annonces sont fournies par les partenaires et prestataires, qui en garantissent les droits.</p>

        <h2>Données personnelles</h2>
        <p>Le traitement de vos données est décrit dans notre <a href="{{ route('pages.privacy') }}">politique de confidentialité</a>. L’utilisation du site est régie par les <a href="{{ route('pages.terms') }}">conditions générales d’utilisation</a>.</p>
    </div>
</x-layouts.public-page>
