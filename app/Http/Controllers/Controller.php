<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;

abstract class Controller
{
    /**
     * Ensure the authenticated actor exists, matches an optional model type,
     * and holds the required permission on its own guard.
     *
     * @param  class-string<Authenticatable>|null  $expectedModel
     */
    protected function ensureAuthorized(
        ?Authenticatable $actor,
        string $permission,
        ?string $expectedModel = null,
        ?bool $isNeedAdmin = false
    ): ?JsonResponse {
        if (! $actor) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated.',
                'data' => null,
            ], 401);
        }

        if ($expectedModel !== null && ! ($actor instanceof $expectedModel)) {
            return response()->json([
                'status' => false,
                'message' => 'Forbidden: tipe actor invalid untuk resource ini.',
                'data' => null,
            ], 403);
        }

        // Determine guard explicitly by model type
        $guard = match (true) {
            $actor instanceof Admin => 'admin',
            $actor instanceof User => 'user',
            default => null,
        };

        $hasPermission = $guard
            ? $actor->hasPermissionTo($permission, $guard)
            : $actor->can($permission);

        if (! $hasPermission) {
            return response()->json([
                'status' => false,
                'message' => "Forbidden: anda tidak punya izin [{$permission}].",
                'data' => null,
            ], 403);
        }

        if ($isNeedAdmin) {
            if (! $actor->hasRole('admin', 'admin')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Forbidden: membutuhkan role admin.',
                    'data' => null,
                ], 403);
            }
        }

        return null; // Authorized
    }
}
