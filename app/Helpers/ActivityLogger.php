<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public static function log($action_type, $description, $user_id = null, $username = null)
    {
        try {
            $user = Auth::user();
            if (!$user_id) {
                $user_id = $user ? $user->id : 0;
            }
            if (!$username) {
                $username = $user ? ($user->firstname . ' ' . $user->lastname) : 'System';
            }

            $ip_address = request()->ip() ?? '127.0.0.1';

            DB::table('activity_logs')->insert([
                'user_id' => $user_id,
                'username' => $username,
                'action_type' => $action_type,
                'description' => $description,
                'ip_address' => $ip_address,
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Silently fail if table or column issue to avoid breaking transaction
        }
    }
}
