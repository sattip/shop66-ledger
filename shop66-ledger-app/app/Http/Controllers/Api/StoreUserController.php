<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Api\Concerns\AuthorizesStoreAccess;
use App\Http\Controllers\Controller;
use App\Http\Resources\StoreUserResource;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StoreUserController extends Controller
{
    use AuthorizesStoreAccess;

    public function index(Request $request, Store $store)
    {
        $this->authorizeStore($request, $store);
        $users = $store->users()->with('roles')->paginate();

        return StoreUserResource::collection($users);
    }

    public function store(Request $request, Store $store): StoreUserResource
    {
        $this->authorizeStore($request, $store);
        $data = $this->validateRequest($request);
        $user = User::findOrFail($data['user_id']);

        $store->users()->syncWithoutDetaching([
            $user->id => ['role' => $data['role']],
        ]);

        $user->assignRole($data['role']);

        return new StoreUserResource($user->fresh(['roles', 'stores']));
    }

    public function update(Request $request, Store $store, User $user): StoreUserResource
    {
        $this->authorizeStore($request, $store);
        $data = $this->validateRequest($request);

        $store->users()->updateExistingPivot($user->id, ['role' => $data['role']]);
        $user->syncRoles([$data['role']]);

        return new StoreUserResource($user->fresh(['roles', 'stores']));
    }

    public function destroy(Request $request, Store $store, User $user): JsonResponse
    {
        $this->authorizeStore($request, $store);
        $pivotRole = $store->users()->where('user_id', $user->id)->first()?->pivot?->role;

        $store->users()->detach($user->id);

        if ($pivotRole) {
            $user->removeRole($pivotRole);
        }

        return response()->json(['message' => 'User detached from store']);
    }

    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role' => ['required', Rule::in(array_map(fn (UserRole $role) => $role->value, UserRole::cases()))],
        ]);
    }
}
