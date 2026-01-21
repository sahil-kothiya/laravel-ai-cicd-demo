<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of the users.
     */
    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->get('search'),
            'status' => $request->get('status'),
        ];

        // dd($filters);

        $users = $this->userService->getAllUsers($filters, $request->get('per_page', 15));

        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        return view('users.create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        try {
            $this->userService->createUser($request->validated());

            return redirect()->route('users.index')
                ->with('success', 'User created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error creating user: '.$e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified user.
     */
    public function show(int $id): View
    {
        $user = $this->userService->getUserById($id);

        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(int $id): View
    {
        $user = $this->userService->getUserById($id);

        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UpdateUserRequest $request, int $id): RedirectResponse
    {
        try {
            $this->userService->updateUser($id, $request->validated());

            return redirect()->route('users.index')
                ->with('success', 'User updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating user: '.$e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $this->userService->deleteUser($id);

            return redirect()->route('users.index')
                ->with('success', 'User deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error deleting user: '.$e->getMessage());
        }
    }

    /**
     * Toggle user status.
     */
    public function toggleStatus(int $id): RedirectResponse
    {
        try {
            $this->userService->toggleUserStatus($id);

            return redirect()->route('users.index')
                ->with('success', 'User status updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating user status: '.$e->getMessage());
        }
    }
}
// Feature: Added email validation - 01/21/2026 12:03:27
