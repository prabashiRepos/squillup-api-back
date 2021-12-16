<?php

namespace App\Api\V1\Controllers\SuperAdmin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Activity;

class ActivityController extends Controller
{
    public function index()
    {
        $activity = Activity::all();

        return response()->json($activity, 201);

    }
}
