<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserManagementAuditLogResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserManagementAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = (string) $request->search;
            $query->where(function ($inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        return UserResource::collection(
            $query->orderBy('created_at', 'desc')->paginate(10)
        );
    }

    public function auditLogs()
    {
        $logs = UserManagementAuditLog::query()
            ->with(['actor', 'target'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return UserManagementAuditLogResource::collection($logs);
    }

    public function show(User $user)
    {
        return new UserResource($user);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['admin', 'user'])],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        $this->logAction($request, $user, 'user_created', [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ]);

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, User $user)
    {
        $old = [
            'name' => $user->name,
            'email' => $user->email,
        ];

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if (array_key_exists('name', $validated)) {
            $user->name = $validated['name'];
        }

        if (array_key_exists('email', $validated)) {
            $user->email = $validated['email'];
        }

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        $this->logAction($request, $user, 'user_updated', [
            'old' => $old,
            'new' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
            'password_changed' => !empty($validated['password']),
        ]);

        return new UserResource($user);
    }

    public function updateRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in(['admin', 'user'])],
        ]);

        $newRole = $validated['role'];

        if ($request->user()->id === $user->id && $newRole !== 'admin') {
            abort(422, 'You cannot remove your own admin role.');
        }

        $this->ensureAtLeastOneAdmin($user, $newRole);

        $oldRole = $user->role;
        $user->role = $newRole;
        $user->save();

        $this->logAction($request, $user, 'user_role_updated', [
            'old_role' => $oldRole,
            'new_role' => $newRole,
        ]);

        return new UserResource($user);
    }

    public function destroy(Request $request, User $user)
    {
        if ($request->user()->id === $user->id) {
            abort(422, 'You cannot delete your own account.');
        }

        $this->ensureAtLeastOneAdmin($user, null);

        $this->logAction($request, $user, 'user_deleted', [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ]);

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully.',
        ]);
    }

    private function ensureAtLeastOneAdmin(User $user, ?string $newRole): void
    {
        $willLoseAdminRole = $user->role === 'admin' && $newRole !== 'admin';

        if ($willLoseAdminRole && User::where('role', 'admin')->count() <= 1) {
            abort(422, 'At least one admin must remain.');
        }
    }

    private function logAction(Request $request, User $target, string $action, array $meta = []): void
    {
        UserManagementAuditLog::create([
            'actor_user_id' => $request->user()?->id,
            'target_user_id' => $target->id,
            'action' => $action,
            'meta' => $meta,
        ]);
    }
}
