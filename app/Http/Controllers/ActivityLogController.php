<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityLog;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = ActivityLog::orderBy('id', 'desc')->paginate(50);
        return view('activity_log', compact('logs'));
    }
}
