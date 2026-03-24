<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of the users.
     * GET /api/users
     */
    public function index(Request $request)
    {
        $users = User::with('roles')->paginate($request->get('per_page', 10));
        return ApiResponse::paginated($users, 'Users retrieved successfully');
    }

    /**
     * Store a newly created user in storage.
     * POST /api/users
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), UserService::getCreateRules());

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        try {
            $user = $this->userService->create($request->all());
            return ApiResponse::success($user->load('roles'), 'User created successfully', 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to create user: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified user.
     * GET /api/users/{user}
     */
    public function show(User $user)
    {
        return ApiResponse::success($user->load('roles', 'permissions'), 'User retrieved successfully');
    }

    /**
     * Update the specified user in storage.
     * PUT/PATCH /api/users/{user}
     */
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), UserService::getUpdateRules($user));

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        try {
            $updatedUser = $this->userService->update($user, $request->all());
            return ApiResponse::success($updatedUser->load('roles'), 'User updated successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to update user: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified user from storage.
     * DELETE /api/users/{user}
     */
    public function destroy(User $user)
    {
        try {
            $this->userService->delete($user);
            return ApiResponse::success(null, 'User deleted successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to delete user: ' . $e->getMessage(), 500);
        }
    }
}
