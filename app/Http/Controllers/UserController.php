<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     * Optimized with pagination and caching.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $search = $request->input('search');
            $status = $request->input('status');

            // Validate pagination
            if ($perPage > 100) {
                return response()->json([
                    'error' => 'Maximum per_page value is 100',
                ], 422);
            }

            $cacheKey = "users_list_{$perPage}_{$search}_{$status}";

            $users = Cache::remember($cacheKey, 300, function () use ($perPage, $search, $status) {
                $query = User::query();

                // Apply search filter
                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                }

                // Apply status filter
                if ($status) {
                    $query->where('status', $status);
                }

                return $query->select(['id', 'name', 'email', 'status', 'created_at'])
                    ->orderBy('created_at', 'desc')
                    ->paginate($perPage);
            });

            return response()->json([
                'success' => true,
                'data' => $users,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching users: '.$e->getMessage());

            return response()->json([
                'error' => 'An error occurred while fetching users',
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();
            $validated['password'] = Hash::make($validated['password']);
            $validated['status'] = 'active';

            $user = User::create($validated);

            // Clear cache
            Cache::tags(['users'])->flush();

            DB::commit();

            Log::info('User created successfully', ['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $user,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating user: '.$e->getMessage());

            return response()->json([
                'error' => 'An error occurred while creating the user',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     * Optimized with caching and specific field selection.
     */
    public function show(string $id): JsonResponse
    {
        try {
            // Validate ID is numeric
            if (! is_numeric($id) || $id <= 0) {
                return response()->json([
                    'error' => 'Invalid user ID',
                ], 422);
            }

            $cacheKey = "user_{$id}";

            $user = Cache::remember($cacheKey, 300, function () use ($id) {
                return User::find($id);
            });

            if (! $user) {
                return response()->json([
                    'error' => 'User not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $user,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching user: '.$e->getMessage());

            return response()->json([
                'error' => 'An error occurred while fetching the user',
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, string $id): JsonResponse
    {
        try {
            // Validate ID is numeric
            if (! is_numeric($id) || $id <= 0) {
                return response()->json([
                    'error' => 'Invalid user ID',
                ], 422);
            }

            DB::beginTransaction();

            $user = User::find($id);

            if (! $user) {
                return response()->json([
                    'error' => 'User not found',
                ], 404);
            }

            $validated = $request->validated();

            // Hash password if provided
            if (isset($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            }

            $user->update($validated);

            // Clear cache
            Cache::forget("user_{$id}");
            Cache::tags(['users'])->flush();

            DB::commit();

            Log::info('User updated successfully', ['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $user->fresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating user: '.$e->getMessage());

            return response()->json([
                'error' => 'An error occurred while updating the user',
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            // Validate ID is numeric
            if (! is_numeric($id) || $id <= 0) {
                return response()->json([
                    'error' => 'Invalid user ID',
                ], 422);
            }

            DB::beginTransaction();

            $user = User::find($id);

            if (! $user) {
                return response()->json([
                    'error' => 'User not found',
                ], 404);
            }

            $user->delete();

            // Clear cache
            Cache::forget("user_{$id}");
            Cache::tags(['users'])->flush();

            DB::commit();

            Log::info('User deleted successfully', ['user_id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting user: '.$e->getMessage());

            return response()->json([
                'error' => 'An error occurred while deleting the user',
            ], 500);
        }
    }

    /**
     * Bulk operations for users.
     */
    public function bulkStore(Request $request): JsonResponse
    {
        $request->validate([
            'users' => 'required|array|min:1|max:100',
            'users.*.name' => 'required|string|min:2|max:255',
            'users.*.email' => 'required|email|unique:users,email',
            'users.*.password' => 'required|string|min:8',
        ]);

        try {
            DB::beginTransaction();

            $users = collect($request->users)->map(function ($userData) {
                $userData['password'] = Hash::make($userData['password']);
                $userData['status'] = 'active';
                $userData['created_at'] = now();
                $userData['updated_at'] = now();

                return $userData;
            });

            User::insert($users->toArray());

            Cache::tags(['users'])->flush();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($request->users).' users created successfully',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in bulk user creation: '.$e->getMessage());

            return response()->json([
                'error' => 'An error occurred during bulk user creation',
            ], 500);
        }
    }
}
