@php
    $contact = config('baobaa.contact');
@endphp

<x-layouts.public-page
    title="Politique de confidentialité"
    eyebrow="Informations légales"
    heading="Politique de confidentialité"
    intro="Comment BAOBAA collecte, utilise et protège vos données personnelles."
>
    <div class="baobaa-prose">
        <h2>Données collectées</h2>
        <ul>
            <li>Données de compte : nom, adresse e-mail, numéro de téléphone, mot de passe chiffré.</li>
            <li>Données de réservation : dates, horaires, nombre d’invités, options choisies, messages échangés avec les partenaires.</li>
            <li>Données de paiement : référence, montant et statut des transactions. Les données de carte ou de compte mobile sont traitées par les prestataires de paiement et ne sont pas conservées par BAOBAA.</li>
            <li>Données des partenaires et prestataires : informations d’entreprise, coordonnées, documents de vérification, contenus publiés.</li>
        </ul>

        <h2>Finalités</h2>
        <ul>
            <li>Créer et gérer votre compte.</li>
            <li>Traiter les demandes de réservation, les proformas, les paiements et les reversements.</li>
            <li>Permettre les échanges entre clients, partenaires et prestataires.</li>
            <li>Prévenir la fraude et sécuriser la plateforme.</li>
            <li>Améliorer nos services et vous informer des évolutions importantes.</li>
        </ul>

        <h2>Partage des données</h2>
        <p>Vos données sont partagées uniquement avec les partenaires et prestataires concernés par votre demande, nos prestataires techniques (hébergement, stockage, paiement) et les autorités lorsque la loi l’exige. BAOBAA ne vend pas vos données personnelles.</p>

        <h2>Durée de conservation</h2>
        <p>Les données sont conservées pendant la durée de vie de votre compte, puis archivées pendant la durée nécessaire au respect de nos obligations légales et comptables.</p>

        <h2>Vos droits</h2>
        <p>Vous disposez d’un droit d’accès, de rectification, d’opposition et de suppression de vos données, conformément à la réglementation applicable en matière de protection des données personnelles. Vous pouvez modifier une partie de vos informations depuis votre espace client.</p>

        <h2>Nous contacter</h2>
        <p>
            Pour exercer vos droits ou poser une question, contactez-nous via la <a href="{{ route('pages.contact') }}">page contact</a>@if (filled($contact['email'])) ou par e-mail à <a href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a>@endif.
        </p>
    </div>
</x-layouts.public-page>
