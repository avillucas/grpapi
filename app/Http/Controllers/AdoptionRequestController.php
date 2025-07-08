<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AdoptionRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\AdoptionRequestStatus;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Facades\Validator;

class AdoptionRequestController extends Controller
{
    /**
     * Display a listing of adoption requests.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $adoptionRequests = AdoptionRequest::with(['pet', 'user'])->get()->map(function ($request) {
                return [
                    'id' => $request->id,
                    'address' => $request->address,
                    'phone' => $request->phone,
                    'application' => $request->application,
                    'status' => $request->status->value,
                    'pet' => [
                        'id' => $request->pet->id,
                        'name' => $request->pet->name,
                        'status' => $request->pet->status->value,
                        'photo_url' => $request->pet->photo_url,
                    ],
                    'user' => [
                        'id' => $request->user->id,
                        'name' => $request->user->name,
                        'email' => $request->user->email,
                    ],
                    'created_at' => $request->created_at,
                    'updated_at' => $request->updated_at,
                ];
            });

            return response()->json([
                'message' => 'Adoption requests retrieved successfully',
                'data' => $adoptionRequests
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve adoption requests',
                'error' => $e->getMessage()
            ], 500);
        }
    }


  /**
     * Create a new adoption request for the authenticated user.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function myself(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'pet_id' => 'required|exists:pets,id',
                'address' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'application' => 'required|string',
                'status' => ['nullable', new Enum(AdoptionRequestStatus::class)],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            $user_id = Auth::id();
            // Check if user already has a pending request for this pet
            $existingRequest = AdoptionRequest::where('pet_id', $request->pet_id)
                ->where('user_id', $user_id)
                ->where('status', AdoptionRequestStatus::PENDING->value)
                ->first();

            if ($existingRequest) {
                return response()->json([
                    'message' => 'User already has a pending adoption request for this pet'
                ], 409);
            }

            $adoptionRequest = AdoptionRequest::create([
                'pet_id' => $request->pet_id,
                'user_id' => $user_id,
                'address' => $request->address,
                'phone' => $request->phone,
                'application' => $request->application,
                'status' => $request->status ?? AdoptionRequestStatus::PENDING->value,
            ]);

            $adoptionRequest->load(['pet', 'user']);

            return response()->json([
                'message' => 'Adoption request created successfully',
                'data' => [
                    'id' => $adoptionRequest->id,
                    'address' => $adoptionRequest->address,
                    'phone' => $adoptionRequest->phone,
                    'application' => $adoptionRequest->application,
                    'status' => $adoptionRequest->status->value,
                    'pet' => [
                        'id' => $adoptionRequest->pet->id,
                        'name' => $adoptionRequest->pet->name,
                        'status' => $adoptionRequest->pet->status->value,
                    ],
                    'user' => [
                        'id' => $adoptionRequest->user->id,
                        'name' => $adoptionRequest->user->name,
                        'email' => $adoptionRequest->user->email,
                    ],
                    'created_at' => $adoptionRequest->created_at,
                    'updated_at' => $adoptionRequest->updated_at,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create adoption request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created adoption request in storage.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'pet_id' => 'required|exists:pets,id',
                'user_id' => 'required|exists:users,id',
                'address' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'application' => 'required|string',
                'status' => ['nullable', new Enum(AdoptionRequestStatus::class)],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if user already has a pending request for this pet
            $existingRequest = AdoptionRequest::where('pet_id', $request->pet_id)
                ->where('user_id', $request->user_id)
                ->where('status', AdoptionRequestStatus::PENDING->value)
                ->first();

            if ($existingRequest) {
                return response()->json([
                    'message' => 'User already has a pending adoption request for this pet'
                ], 409);
            }

            $adoptionRequest = AdoptionRequest::create([
                'pet_id' => $request->pet_id,
                'user_id' => $request->user_id,
                'address' => $request->address,
                'phone' => $request->phone,
                'application' => $request->application,
                'status' => $request->status ?? AdoptionRequestStatus::PENDING->value,
            ]);

            $adoptionRequest->load(['pet', 'user']);

            return response()->json([
                'message' => 'Adoption request created successfully',
                'data' => [
                    'id' => $adoptionRequest->id,
                    'address' => $adoptionRequest->address,
                    'phone' => $adoptionRequest->phone,
                    'application' => $adoptionRequest->application,
                    'status' => $adoptionRequest->status->value,
                    'pet' => [
                        'id' => $adoptionRequest->pet->id,
                        'name' => $adoptionRequest->pet->name,
                        'status' => $adoptionRequest->pet->status->value,
                    ],
                    'user' => [
                        'id' => $adoptionRequest->user->id,
                        'name' => $adoptionRequest->user->name,
                        'email' => $adoptionRequest->user->email,
                    ],
                    'created_at' => $adoptionRequest->created_at,
                    'updated_at' => $adoptionRequest->updated_at,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create adoption request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified adoption request.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $adoptionRequest = AdoptionRequest::with(['pet', 'user'])->find($id);

            if (!$adoptionRequest) {
                return response()->json([
                    'message' => 'Adoption request not found'
                ], 404);
            }

            return response()->json([
                'message' => 'Adoption request retrieved successfully',
                'data' => [
                    'id' => $adoptionRequest->id,
                    'address' => $adoptionRequest->address,
                    'phone' => $adoptionRequest->phone,
                    'application' => $adoptionRequest->application,
                    'status' => $adoptionRequest->status->value,
                    'pet' => [
                        'id' => $adoptionRequest->pet->id,
                        'name' => $adoptionRequest->pet->name,
                        'status' => $adoptionRequest->pet->status->value,
                        'photo_url' => $adoptionRequest->pet->photo_url,
                    ],
                    'user' => [
                        'id' => $adoptionRequest->user->id,
                        'name' => $adoptionRequest->user->name,
                        'email' => $adoptionRequest->user->email,
                    ],
                    'created_at' => $adoptionRequest->created_at,
                    'updated_at' => $adoptionRequest->updated_at,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve adoption request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified adoption request in storage.
     * 
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $adoptionRequest = AdoptionRequest::find($id);

            if (!$adoptionRequest) {
                return response()->json([
                    'message' => 'Adoption request not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'pet_id' => 'sometimes|required|exists:pets,id',
                'user_id' => 'sometimes|required|exists:users,id',
                'address' => 'sometimes|required|string|max:255',
                'phone' => 'sometimes|required|string|max:20',
                'application' => 'sometimes|required|string',
                'status' => ['sometimes', 'required', new Enum(AdoptionRequestStatus::class)],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update fields if provided
            if ($request->has('pet_id')) {
                $adoptionRequest->pet_id = $request->pet_id;
            }

            if ($request->has('user_id')) {
                $adoptionRequest->user_id = $request->user_id;
            }

            if ($request->has('address')) {
                $adoptionRequest->address = $request->address;
            }

            if ($request->has('phone')) {
                $adoptionRequest->phone = $request->phone;
            }

            if ($request->has('application')) {
                $adoptionRequest->application = $request->application;
            }

            if ($request->has('status')) {
                $adoptionRequest->status = $request->status;
            }

            $adoptionRequest->save();
            $adoptionRequest->load(['pet', 'user']);

            return response()->json([
                'message' => 'Adoption request updated successfully',
                'data' => [
                    'id' => $adoptionRequest->id,
                    'address' => $adoptionRequest->address,
                    'phone' => $adoptionRequest->phone,
                    'application' => $adoptionRequest->application,
                    'status' => $adoptionRequest->status->value,
                    'pet' => [
                        'id' => $adoptionRequest->pet->id,
                        'name' => $adoptionRequest->pet->name,
                        'status' => $adoptionRequest->pet->status->value,
                    ],
                    'user' => [
                        'id' => $adoptionRequest->user->id,
                        'name' => $adoptionRequest->user->name,
                        'email' => $adoptionRequest->user->email,
                    ],
                    'created_at' => $adoptionRequest->created_at,
                    'updated_at' => $adoptionRequest->updated_at,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update adoption request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified adoption request from storage.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $adoptionRequest = AdoptionRequest::find($id);

            if (!$adoptionRequest) {
                return response()->json([
                    'message' => 'Adoption request not found'
                ], 404);
            }

            $adoptionRequest->delete();

            return response()->json([
                'message' => 'Adoption request deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete adoption request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve an adoption request.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function approve($id)
    {
        try {
            $adoptionRequest = AdoptionRequest::with(['pet'])->find($id);

            if (!$adoptionRequest) {
                return response()->json([
                    'message' => 'Adoption request not found'
                ], 404);
            }

            $adoptionRequest->status = AdoptionRequestStatus::APPROVED;
            $adoptionRequest->save();

            // Optionally update pet status to adopted
            // $adoptionRequest->pet->status = PetStatus::ADOPTED;
            // $adoptionRequest->pet->save();

            return response()->json([
                'message' => 'Adoption request approved successfully',
                'data' => [
                    'id' => $adoptionRequest->id,
                    'status' => $adoptionRequest->status->value,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to approve adoption request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject an adoption request.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function reject($id)
    {
        try {
            $adoptionRequest = AdoptionRequest::find($id);

            if (!$adoptionRequest) {
                return response()->json([
                    'message' => 'Adoption request not found'
                ], 404);
            }

            $adoptionRequest->status = AdoptionRequestStatus::REJECTED;
            $adoptionRequest->save();

            return response()->json([
                'message' => 'Adoption request rejected successfully',
                'data' => [
                    'id' => $adoptionRequest->id,
                    'status' => $adoptionRequest->status->value,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to reject adoption request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display adoption requests for the authenticated user.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function myRequests()
    {
        try {
            $userId = Auth::id();
            
            $adoptionRequests = AdoptionRequest::with(['pet'])
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($request) {
                    return [
                        'id' => $request->id,
                        'address' => $request->address,
                        'phone' => $request->phone,
                        'application' => $request->application,
                        'status' => $request->status->value,
                        'pet' => [
                            'id' => $request->pet->id,
                            'name' => $request->pet->name,
                            'status' => $request->pet->status->value,
                            'photo_url' => $request->pet->photo_url,
                            'age' => $request->pet->age,
                            'type' => $request->pet->type?->value,
                            'breed' => $request->pet->breed,
                            'size' => $request->pet->size?->value,
                        ],
                        'created_at' => $request->created_at,
                        'updated_at' => $request->updated_at,
                    ];
                });

            return response()->json([
                'message' => 'My adoption requests retrieved successfully',
                'data' => $adoptionRequests
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve adoption requests',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new adoption request for the authenticated user.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function mine(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'pet_id' => 'required|exists:pets,id',
                'address' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'application' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userId = Auth::id();

            // Check if user already has a pending request for this pet
            $existingRequest = AdoptionRequest::where('pet_id', $request->pet_id)
                ->where('user_id', $userId)
                ->where('status', AdoptionRequestStatus::PENDING->value)
                ->first();

            if ($existingRequest) {
                return response()->json([
                    'message' => 'User already has a pending adoption request for this pet'
                ], 409);
            }

            $adoptionRequest = AdoptionRequest::create([
                'pet_id' => $request->pet_id,
                'user_id' => $userId,
                'address' => $request->address,
                'phone' => $request->phone,
                'application' => $request->application,
                'status' => AdoptionRequestStatus::PENDING->value,
            ]);

            $adoptionRequest->load(['pet']);

            return response()->json([
                'message' => 'Adoption request created successfully',
                'data' => [
                    'id' => $adoptionRequest->id,
                    'address' => $adoptionRequest->address,
                    'phone' => $adoptionRequest->phone,
                    'application' => $adoptionRequest->application,
                    'status' => $adoptionRequest->status->value,
                    'pet' => [
                        'id' => $adoptionRequest->pet->id,
                        'name' => $adoptionRequest->pet->name,
                        'status' => $adoptionRequest->pet->status->value,
                        'photo_url' => $adoptionRequest->pet->photo_url,
                        'age' => $adoptionRequest->pet->age,
                        'type' => $adoptionRequest->pet->type?->value,
                        'breed' => $adoptionRequest->pet->breed,
                        'size' => $adoptionRequest->pet->size?->value,
                    ],
                    'created_at' => $adoptionRequest->created_at,
                    'updated_at' => $adoptionRequest->updated_at,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create adoption request',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
