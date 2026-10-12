<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Contact public
    |--------------------------------------------------------------------------
    |
    | Coordonnées affichées dans le pied de page, la page contact et les pages
    | légales. Une valeur vide masque simplement l'information correspondante.
    |
    */

    'contact' => [
        'email' => env('BAOBAA_CONTACT_EMAIL'),
        'phone' => env('BAOBAA_CONTACT_PHONE'),
        'whatsapp' => env('BAOBAA_CONTACT_WHATSAPP'),
        'address' => env('BAOBAA_CONTACT_ADDRESS'),
        'hours' => env('BAOBAA_CONTACT_HOURS', 'Du lundi au vendredi, de 8 h à 18 h (GMT)'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Éditeur du site
    |--------------------------------------------------------------------------
    |
    | Informations obligatoires des mentions légales (raison sociale, forme
    | juridique, immatriculation, siège, directeur de publication, hébergeur).
    |
    */

    'legal' => [
        'company_name' => env('BAOBAA_LEGAL_COMPANY_NAME', 'BAOBAA'),
        'legal_form' => env('BAOBAA_LEGAL_FORM'),
        'registration_number' => env('BAOBAA_LEGAL_RCCM'),
        'tax_number' => env('BAOBAA_LEGAL_TAX_NUMBER'),
        'head_office' => env('BAOBAA_LEGAL_HEAD_OFFICE'),
        'publication_director' => env('BAOBAA_LEGAL_PUBLICATION_DIRECTOR'),
        'host' => env('BAOBAA_LEGAL_HOST'),
        'terms_updated_at' => env('BAOBAA_TERMS_UPDATED_AT', '2026-10-12'),
    ],

];
