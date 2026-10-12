@php
    $contact = config('baobaa.contact');
    $whatsappNumber = filled($contact['whatsapp']) ? preg_replace('/[^0-9]/', '', $contact['whatsapp']) : null;
@endphp

<x-layouts.public-page
    title="Contact"
    eyebrow="Aide"
    heading="Contactez l’équipe BAOBAA"
    intro="Une question sur une réservation, un paiement ou la publication de votre espace ? Notre équipe vous répond."
    active="contact"
>
    @if (blank($contact['email']) && blank($contact['phone']) && blank($whatsappNumber) && blank($contact['address']))
        <p class="text-sm font-semibold leading-6 text-[#6f7890]">Nos coordonnées seront publiées très prochainement. En attendant, les clients peuvent nous écrire depuis la messagerie de leur réservation.</p>
    @endif

    <div class="grid gap-4 sm:grid-cols-2">
        @if (filled($contact['email']))
            <a href="mailto:{{ $contact['email'] }}" class="rounded-2xl border border-[#dce6f7] bg-[#f7faff] p-5 transition hover:-translate-y-0.5 hover:border-[#2f6bff]">
                <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#2f6bff]">E-mail</p>
                <p class="mt-2 break-all text-base font-extrabold text-[#151821]">{{ $contact['email'] }}</p>
            </a>
        @endif

        @if (filled($contact['phone']))
            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $contact['phone']) }}" class="rounded-2xl border border-[#dce6f7] bg-[#f7faff] p-5 transition hover:-translate-y-0.5 hover:border-[#2f6bff]">
                <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#2f6bff]">Téléphone</p>
                <p class="mt-2 text-base font-extrabold text-[#151821]">{{ $contact['phone'] }}</p>
            </a>
        @endif

        @if ($whatsappNumber)
            <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener" class="rounded-2xl border border-[#dce6f7] bg-[#f7faff] p-5 transition hover:-translate-y-0.5 hover:border-[#2f6bff]">
                <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#2f6bff]">WhatsApp</p>
                <p class="mt-2 text-base font-extrabold text-[#151821]">{{ $contact['whatsapp'] }}</p>
            </a>
        @endif

        @if (filled($contact['address']))
            <div class="rounded-2xl border border-[#dce6f7] bg-[#f7faff] p-5">
                <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-[#2f6bff]">Adresse</p>
                <p class="mt-2 text-base font-extrabold text-[#151821]">{{ $contact['address'] }}</p>
            </div>
        @endif
    </div>

    @if (filled($contact['hours']))
        <p class="mt-5 text-sm font-semibold text-[#6f7890]">Horaires : {{ $contact['hours'] }}</p>
    @endif

    <div class="mt-8 grid gap-4 border-t border-[#edf2fb] pt-6 sm:grid-cols-2">
        <div>
            <p class="text-sm font-extrabold text-[#151821]">Vous avez déjà une réservation ?</p>
            <p class="mt-1 text-sm font-semibold leading-6 text-[#6f7890]">Échangez directement avec le partenaire depuis la messagerie de votre réservation, dans votre espace client.</p>
            <a href="{{ route('client.reservations') }}" class="mt-3 inline-flex rounded-full bg-[#2f6bff] px-5 py-2.5 text-sm font-extrabold text-white shadow-lg shadow-[#2f6bff]/25">Mes réservations</a>
        </div>
        <div>
            <p class="text-sm font-extrabold text-[#151821]">Vous souhaitez publier un espace ?</p>
            <p class="mt-1 text-sm font-semibold leading-6 text-[#6f7890]">Créez votre compte partenaire : notre équipe valide votre profil avant la mise en ligne.</p>
            <a href="{{ route('venues.list-venue') }}" class="mt-3 inline-flex rounded-full border border-[#dce6f7] px-5 py-2.5 text-sm font-extrabold text-[#151821] transition hover:border-[#2f6bff] hover:text-[#2f6bff]">Devenir partenaire</a>
        </div>
    </div>
</x-layouts.public-page>
