<?php

namespace App\Models;

enum AdoptionOfferStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case CLOSED = 'closed';
}
