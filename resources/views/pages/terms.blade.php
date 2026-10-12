@php
    $legal = config('baobaa.legal');
    $updatedAt = \Carbon\CarbonImmutable::parse($legal['terms_updated_at'])->locale('fr')->isoFormat('D MMMM YYYY');
@endphp

<x-layouts.public-page
    title="Conditions générales d’utilisation"
    eyebrow="Informations légales"
    heading="Conditions générales d’utilisation"
    :intro="'Dernière mise à jour : '.$updatedAt.'. Ces conditions encadrent l’utilisation de BAOBAA par les clients, les propriétaires d’espaces et les prestataires événementiels.'"
>
    <div class="baobaa-prose">
        <h2>1. Objet</h2>
        <p>BAOBAA est une plateforme en ligne qui met en relation des clients souhaitant organiser un événement avec des propriétaires d’espaces événementiels (« partenaires ») et des prestataires de services événementiels (« prestataires »). Les présentes conditions définissent les règles d’accès et d’utilisation de la plateforme.</p>

        <h2>2. Acceptation des conditions</h2>
        <p>La création d’un compte et l’envoi d’une demande de réservation impliquent l’acceptation pleine et entière des présentes conditions et de la <a href="{{ route('pages.privacy') }}">politique de confidentialité</a>. Si vous n’acceptez pas ces conditions, vous ne devez pas utiliser les services nécessitant un compte.</p>

        <h2>3. Comptes utilisateurs</h2>
        <ul>
            <li>Les informations fournies lors de l’inscription doivent être exactes et tenues à jour.</li>
            <li>Vous êtes responsable de la confidentialité de vos identifiants et de toute activité réalisée depuis votre compte.</li>
            <li>Les comptes partenaires et prestataires sont soumis à une validation préalable par BAOBAA avant toute publication.</li>
            <li>BAOBAA peut suspendre un compte en cas d’utilisation frauduleuse, d’informations inexactes ou de manquement aux présentes conditions.</li>
        </ul>

        <h2>4. Rôle de BAOBAA</h2>
        <p>BAOBAA agit en qualité d’intermédiaire technique. Les espaces et les prestations sont proposés par les partenaires et prestataires, qui restent seuls responsables de l’exactitude de leurs annonces, de la conformité de leurs lieux et de la bonne exécution de leurs prestations.</p>

        <h2>5. Demandes de réservation, proforma et acompte</h2>
        <ul>
            <li>Le client envoie une demande précisant la date, les horaires, le nombre d’invités, les options et, le cas échéant, une proposition de prix.</li>
            <li>Le créneau demandé est bloqué temporairement pendant la création de la demande afin d’éviter les doubles réservations.</li>
            <li>Une proforma récapitule le prix convenu, les options et l’acompte. La réservation n’est confirmée qu’après validation de la proforma par les deux parties et paiement de l’acompte.</li>
            <li>Le montant de l’acompte est défini selon les règles applicables au partenaire et affiché avant le paiement.</li>
            <li>Le solde est réglé selon les modalités indiquées sur la proforma.</li>
        </ul>

        <h2>6. Prix et paiement</h2>
        <p>Les prix affichés sont indiqués dans la devise de l’annonce, à titre de prix de départ. Le prix final dépend de la date, de la durée, des options choisies et de l’éventuelle négociation avec le partenaire. Les moyens de paiement acceptés sont affichés sur chaque annonce et lors de la réservation.</p>

        <h2>7. Annulation et remboursement</h2>
        <p>Les conditions d’annulation propres à chaque espace sont indiquées dans la rubrique « À savoir avant de réserver » de l’annonce et rappelées sur la proforma. À défaut de conditions particulières, toute demande d’annulation doit être adressée via votre espace client ; BAOBAA accompagne les parties dans le traitement des remboursements éventuels.</p>

        <h2>8. Avis clients</h2>
        <p>Seuls les clients ayant effectivement réservé un espace peuvent publier un avis. Les avis sont publiés après modération. Les contenus injurieux, diffamatoires, hors sujet ou trompeurs sont refusés.</p>

        <h2>9. Obligations des partenaires et prestataires</h2>
        <ul>
            <li>Publier des informations exactes : photos réelles, capacité, prix, options et conditions.</li>
            <li>Tenir leur calendrier de disponibilités à jour.</li>
            <li>Honorer les réservations confirmées et respecter la réglementation applicable à leur activité.</li>
            <li>Accepter les commissions et conditions communiquées par BAOBAA lors de leur inscription.</li>
        </ul>

        <h2>10. Responsabilité</h2>
        <p>BAOBAA met en œuvre les moyens raisonnables pour assurer le bon fonctionnement de la plateforme, sans garantir une disponibilité ininterrompue. La responsabilité de BAOBAA ne saurait être engagée pour les dommages résultant de l’exécution d’une prestation par un partenaire ou un prestataire.</p>

        <h2>11. Propriété intellectuelle</h2>
        <p>La marque BAOBAA, le site et ses contenus sont protégés. Les partenaires et prestataires garantissent disposer des droits sur les photos, vidéos et textes qu’ils publient et autorisent BAOBAA à les diffuser pour promouvoir leurs annonces.</p>

        <h2>12. Modification des conditions</h2>
        <p>BAOBAA peut faire évoluer les présentes conditions. La version applicable est celle en vigueur à la date de la demande de réservation. Les utilisateurs sont informés de toute modification importante.</p>

        <h2>13. Contact</h2>
        <p>Pour toute question relative à ces conditions, rendez-vous sur la <a href="{{ route('pages.contact') }}">page contact</a>.</p>
    </div>
</x-layouts.public-page>
