<?php 
namespace App\Models;
enum PetStatus: string
{
    case TRANSIT = 'transit';
    case ADOPTED = 'adopted';
    case DECEASED = 'deceased';
}
