<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Read-only headline metric for the Main LGU SSO hub dashboard.
 * Auth: Authorization: Bearer <SSO_SHARED_SECRET> (same secret used for SSO).
 */
class StatsController extends Controller
{
    public function index(Request $request)
    {
        $secret = (string) config('services.sso.secret');
        $token = (string) $request->bearerToken();

        if (!hash_equals($secret, $token)) {
            return response()->json(['error' => 'unauthorized'], 403);
        }

        $count = DB::table('maintenance')->count();

        return response()->json(['count' => $count, 'label' => 'Maintenance Records']);
    }
}
