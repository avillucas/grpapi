<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Models\PetStatus;
use Illuminate\Http\Request;
use App\Models\AdoptionOffer;
use App\Models\AdoptionOfferStatus;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Facades\Validator;

class AdoptionOfferController extends Controller
{
    /**
     * Display a listing of adoption offers.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $offers = AdoptionOffer::with('pet')->get()->map(function ($offer) {
                return [
                    'id' => $offer->id,
                    'title' => $offer->title,
                    'headline' => $offer->headline,
                    'status' => $offer->status->value,
                    'pet' => [
                        'id' => $offer->pet->id,
                        'name' => $offer->pet->name,
                        'photo_url' => $offer->pet->photo_url,
                        'status' => $offer->pet->status->value,
                        'age' => $offer->pet->age,
                        'type' => $offer->pet->type?->value,
                        'breed' => $offer->pet->breed,
                        'size' => $offer->pet->size?->value,
                    ],
                    'created_at' => $offer->created_at,
                    'updated_at' => $offer->updated_at,
                ];
            });

            return response()->json([
                'message' => 'Adoption offers retrieved successfully',
                'data' => $offers
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve adoption offers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created adoption offer in storage.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'pet_id' => 'required|exists:pets,id',
                'title' => 'required|string|max:30',
                'headline' => 'required|string|max:120',
                'status' => ['nullable', new Enum(AdoptionOfferStatus::class)],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            // Check if pet the pet is in transit 
            $pet = Pet::find($request->pet_id);
            if ($pet->status !== PetStatus::TRANSIT) {
                return response()->json([
                    'message' => 'Pet must be in transit to create an adoption offer',
                ], 400);
            }
            // Check if pet already has an adoption offer
            $existingOffer = AdoptionOffer::where('pet_id', $request->pet_id)->first();
            if ($existingOffer) {
                return response()->json([
                    'message' => 'Pet already has an adoption offer',
                ], 409);
            }

            $offer = AdoptionOffer::create([
                'pet_id' => $request->pet_id,
                'title' => $request->title,
                'headline' => $request->headline,
                'status' => $request->status ?? AdoptionOfferStatus::DRAFT->value,
            ]);

            $offer->load('pet');

            return response()->json([
                'message' => 'Adoption offer created successfully',
                'data' => [
                    'id' => $offer->id,
                    'title' => $offer->title,
                    'headline' => $offer->headline,
                    'status' => $offer->status->value,
                    'pet' => [
                        'id' => $offer->pet->id,
                        'name' => $offer->pet->name,
                        'photo_url' => $offer->pet->photo_url,
                        'status' => $offer->pet->status->value,
                        'age' => $offer->pet->age,
                        'type' => $offer->pet->type?->value,
                        'breed' => $offer->pet->breed,
                        'size' => $offer->pet->size?->value,
                    ],
                    'created_at' => $offer->created_at,
                    'updated_at' => $offer->updated_at,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create adoption offer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified adoption offer.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $offer = AdoptionOffer::with('pet')->find($id);

            if (!$offer) {
                return response()->json([
                    'message' => 'Adoption offer not found'
                ], 404);
            }

            return response()->json([
                'message' => 'Adoption offer retrieved successfully',
                'data' => [
                    'id' => $offer->id,
                    'title' => $offer->title,
                    'headline' => $offer->headline,
                    'status' => $offer->status->value,
                    'pet' => [
                        'id' => $offer->pet->id,
                        'name' => $offer->pet->name,
                        'photo_url' => $offer->pet->photo_url,
                        'status' => $offer->pet->status->value,
                        'age' => $offer->pet->age,
                        'type' => $offer->pet->type?->value,
                        'breed' => $offer->pet->breed,
                        'size' => $offer->pet->size?->value,
                    ],
                    'created_at' => $offer->created_at,
                    'updated_at' => $offer->updated_at,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve adoption offer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified adoption offer in storage.
     * 
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $offer = AdoptionOffer::find($id);

            if (!$offer) {
                return response()->json([
                    'message' => 'Adoption offer not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'pet_id' => 'sometimes|required|exists:pets,id',
                'title' => 'sometimes|required|string|max:30',
                'headline' => 'sometimes|required|string|max:120',
                'status' => ['sometimes', 'required', new Enum(AdoptionOfferStatus::class)],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if trying to change pet_id to a pet that already has an offer
            if ($request->has('pet_id') && $request->pet_id != $offer->pet_id) {
                $existingOffer = AdoptionOffer::where('pet_id', $request->pet_id)->first();
                if ($existingOffer) {
                    return response()->json([
                        'message' => 'Pet already has an adoption offer',
                    ], 409);
                }
            }

            // Update fields if provided
            if ($request->has('pet_id')) {
                $offer->pet_id = $request->pet_id;
            }
            if ($request->has('title')) {
                $offer->title = $request->title;
            }
            if ($request->has('headline')) {
                $offer->headline = $request->headline;
            }

            $offer->save();
            $offer->load('pet');

            return response()->json([
                'message' => 'Adoption offer updated successfully',
                'data' => [
                    'id' => $offer->id,
                    'title' => $offer->title,
                    'headline' => $offer->headline,
                    'pet' => [
                        'id' => $offer->pet->id,
                        'name' => $offer->pet->name,
                        'photo_url' => $offer->pet->photo_url,
                        'status' => $offer->pet->status->value,
                        'age' => $offer->pet->age,
                        'type' => $offer->pet->type?->value,
                        'breed' => $offer->pet->breed,
                        'size' => $offer->pet->size?->value,
                    ],
                    'created_at' => $offer->created_at,
                    'updated_at' => $offer->updated_at,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update adoption offer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified adoption offer from storage.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $offer = AdoptionOffer::find($id);

            if (!$offer) {
                return response()->json([
                    'message' => 'Adoption offer not found'
                ], 404);
            }

            $offer->delete();

            return response()->json([
                'message' => 'Adoption offer deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete adoption offer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display published adoption offers.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function published()
    {
        try {
            $offers = AdoptionOffer::with('pet')
                ->where('status', AdoptionOfferStatus::PUBLISHED->value)
                ->get()
                ->map(function ($offer) {
                    return [
                        'id' => $offer->id,
                        'title' => $offer->title,
                        'headline' => $offer->headline,
                        'status' => $offer->status->value,
                        'pet' => [
                            'id' => $offer->pet->id,
                            'name' => $offer->pet->name,
                            'photo_url' => $offer->pet->photo_url,
                            'status' => $offer->pet->status->value,
                            'age' => $offer->pet->age,
                            'type' => $offer->pet->type?->value,
                            'breed' => $offer->pet->breed,
                            'size' => $offer->pet->size?->value,
                        ],
                        'created_at' => $offer->created_at,
                        'updated_at' => $offer->updated_at,
                    ];
                });

            return response()->json([
                'message' => 'Published adoption offers retrieved successfully',
                'data' => $offers
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve published adoption offers',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
