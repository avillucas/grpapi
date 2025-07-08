<?php

namespace App\Models;

enum PetSize: string
{
    case SMALL = 'small';
    case MEDIUM = 'medium';
    case LARGE = 'large';
}
