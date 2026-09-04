<?php

namespace App\Enums;

enum UserRole: string
{
    case Sap = 'sap';
    case Admin = 'admin';
    case Owner = 'owner';
    case ServiceProvider = 'service_provider';
    case Client = 'client';
}
