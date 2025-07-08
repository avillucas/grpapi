<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pet extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'photo',
        'status',
        'age',
        'type',
        'breed',
        'size',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PetStatus::class,
            'type' => PetType::class,
            'size' => PetSize::class,
        ];
    }

    /**
     * Get the photo URL attribute.
     */
    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? 'http://localhost:3000' . $this->photo : null;
    }

    /**
     * Get the adoption requests for this pet.
     */
    public function adoptionRequests(): HasMany
    {
        return $this->hasMany(AdoptionRequest::class);
    }

    /**
     * Get the adoption offer for this pet.
     */
    public function adoptionOffer(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(AdoptionOffer::class);
    }
}
