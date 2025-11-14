<?php

namespace App\Services;

use App\Models\User;

class AuthFallbackService
{
    /**
     * Return an authenticated user id or a safe fallback user id for dev/test flows.
     * This centralizes the temporary fallback logic used across controllers.
     *
     * @return int|null
     */
    public static function id(): ?int
    {
        $user = auth()->user();
        if ($user) {
            return $user->id;
        }

        $fallback = User::where('email', 'system@local')->first();
        if ($fallback) {
            return $fallback->id;
        }

        $first = User::first();
        if ($first) {
            return $first->id;
        }

        try {
            if (!app()->environment('production')) {
                $created = User::create([
                    'name' => 'System (dev)',
                    'email' => 'system@local',
                    'password' => bcrypt(bin2hex(random_bytes(8))),
                ]);

                return $created->id;
            }
        } catch (\Exception $e) {
            // ignore and fall through to null
        }

        return null;
    }

    /**
     * Alias for id() method for backward compatibility
     * @return int|null
     */
    public static function getUserId(): ?int
    {
        return self::id();
    }
}
