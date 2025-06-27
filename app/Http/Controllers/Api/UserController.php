<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Requests\UserFilterRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    protected UserService $userService;
    
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * List users with pagination and filters
     *
     * @param UserFilterRequest $request
     * @return UserCollection
     */
    public function index(UserFilterRequest $request): UserCollection
    {
        $data = $request->validated();
        $orderBy = $data['order_by'] ?? 'created_at';
        $orderDirection = $data['order_direction'] ?? 'desc';
        $perPage = $data['per_page'] ?? 15;
        
        $users = $this->userService->getPaginatedUsers(
            $request,
            $orderBy,
            $orderDirection,
            $perPage
        );
        
        return new UserCollection($users);
    }

    /**
     * Create a new user
     *
     * @param UserStoreRequest $request
     * @return UserResource
     */
    public function store(UserStoreRequest $request): UserResource
    {
        $user = $this->userService->createUser($request->validated());
        return new UserResource($user);
    }

    /**
     * Get a specific user
     *
     * @param string $id
     * @return UserResource
     */
    public function show(string $id): UserResource
    {
        $user = $this->userService->findUserById($id);
        return new UserResource($user);
    }

    /**
     * Update a user
     *
     * @param UserUpdateRequest $request
     * @param string $id
     * @return UserResource
     */
    public function update(UserUpdateRequest $request, string $id): UserResource
    {
        $user = $this->userService->findUserById($id);
        $user = $this->userService->updateUser($user, $request->validated());
        
        return new UserResource($user);
    }

    /**
     * Delete a user
     *
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        $user = $this->userService->findUserById($id);
        $this->userService->deleteUser($user);
        
        return response()->json([
            'message' => __('messages.deleted_successfully', ['resource' => __('messages.User')])
        ], 200);
    }
    
    /**
     * Toggle user active status
     *
     * @param string $id
     * @param Request $request
     * @return UserResource
     */
    public function toggleStatus(string $id, Request $request): UserResource
    {
        $data = $request->validate([
            'is_active' => 'required|boolean',
        ]);
        
        $user = $this->userService->findUserById($id);

        $user = $this->userService->setUserActiveStatus($user, $data['is_active']);
        
        return new UserResource($user);
    }
    
    /**
     * Restore a deleted user
     *
     * @param string $id
     * @return JsonResponse
     */
    public function restore(string $id): JsonResponse
    {
        $this->userService->restoreUser($id);
        return response()->json([
            'message' => __('messages.restored_successfully', ['resource' => __('messages.User')])
        ], 200);
    }

    /**
     * Bulk delete multiple users
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|string|exists:users,id'
        ]);
        
        $deletedCount = $this->userService->bulkDeleteUsers($request->input('ids'));
        
        return response()->json([
            'message' => __('messages.bulk_deleted_successfully', [
                'count' => $deletedCount,
                'resource' => __('messages.users')
            ]),
            'deleted_count' => $deletedCount
        ], 200);
    }

    /**
     * Bulk restore multiple users
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkRestore(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|string'
        ]);
        
        $restoredCount = $this->userService->bulkRestoreUsers($request->input('ids'));
        
        return response()->json([
            'message' => __('messages.bulk_restored_successfully', [
                'count' => $restoredCount,
                'resource' => __('messages.users')
            ]),
            'restored_count' => $restoredCount
        ], 200);
    }

    /**
     * Bulk toggle status for multiple users
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkToggleStatus(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|string|exists:users,id',
            'is_active' => 'required|boolean'
        ]);
        
        $updatedCount = $this->userService->bulkToggleUsersStatus(
            $request->input('ids'),
            $request->boolean('is_active')
        );
        
        return response()->json([
            'message' => __('messages.bulk_status_updated_successfully', [
                'count' => $updatedCount,
                'resource' => __('messages.users')
            ]),
            'updated_count' => $updatedCount
        ], 200);
    }
}
