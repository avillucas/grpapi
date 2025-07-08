<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Models\PetStatus;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PetController extends Controller
{
    /**
     * Display a listing of pets.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $pets = Pet::all()->map(function ($pet) {
                return [
                    'id' => $pet->id,
                    'name' => $pet->name,
                    'photo' => $pet->photo,
                    'photo_url' => $pet->photo_url,
                    'status' => $pet->status->value,
                    'created_at' => $pet->created_at,
                    'updated_at' => $pet->updated_at,
                ];
            });

            return response()->json([
                'message' => 'Pets retrieved successfully',
                'data' => $pets
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve pets',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created pet in storage.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // 2MB max
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $petData = [
                'name' => $request->name,
                'status' => $request->status ?? PetStatus::TRANSIT->value,
            ];

            // Handle photo upload
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('pets', 'public');
                $petData['photo'] = $photoPath;
            }

            $pet = Pet::create($petData);

            return response()->json([
                'message' => 'Pet created successfully',
                'data' => [
                    'id' => $pet->id,
                    'name' => $pet->name,
                    'photo' => $pet->photo,
                    'photo_url' => $pet->photo_url,
                    'status' => $pet->status->value,
                    'created_at' => $pet->created_at,
                    'updated_at' => $pet->updated_at,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create pet',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified pet.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $pet = Pet::find($id);

            if (!$pet) {
                return response()->json([
                    'message' => 'Pet not found'
                ], 404);
            }

            return response()->json([
                'message' => 'Pet retrieved successfully',
                'data' => [
                    'id' => $pet->id,
                    'name' => $pet->name,
                    'photo' => $pet->photo,
                    'photo_url' => $pet->photo_url,
                    'status' => $pet->status->value,
                    'created_at' => $pet->created_at,
                    'updated_at' => $pet->updated_at,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve pet',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified pet in storage.
     * 
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $pet = Pet::find($id);

            if (!$pet) {
                return response()->json([
                    'message' => 'Pet not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'photo' => 'sometimes|nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update fields if provided
            if ($request->has('name')) {
                $pet->name = $request->name;
            }
            $pet->status = PetStatus::TRANSIT;
            // Handle photo upload
            if ($request->hasFile('photo')) {
                // Delete old photo if exists
                if ($pet->photo) {
                    Storage::disk('public')->delete($pet->photo);
                }
                
                $photoPath = $request->file('photo')->store('pets', 'public');
                $pet->photo = $photoPath;
            }

            $pet->save();

            return response()->json([
                'message' => 'Pet updated successfully',
                'data' => [
                    'id' => $pet->id,
                    'name' => $pet->name,
                    'photo' => $pet->photo,
                    'photo_url' => $pet->photo_url,
                    'status' => $pet->status->value,
                    'created_at' => $pet->created_at,
                    'updated_at' => $pet->updated_at,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update pet',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified pet from storage.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $pet = Pet::find($id);

            if (!$pet) {
                return response()->json([
                    'message' => 'Pet not found'
                ], 404);
            }

            // Delete photo if exists
            if ($pet->photo) {
                Storage::disk('public')->delete($pet->photo);
            }

            $pet->delete();

            return response()->json([
                'message' => 'Pet deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete pet',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
