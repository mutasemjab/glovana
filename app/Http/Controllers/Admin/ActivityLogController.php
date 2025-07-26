<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{

    public function index(Request $request)
    {
        $activities = Activity::with(['causer', 'subject'])
            ->when($request->model, function ($query, $model) {
                return $query->where('subject_type', $model);
            })
            ->when($request->causer, function ($query, $causer) {
                return $query->where('causer_type', $causer);
            })
            ->when($request->event, function ($query, $event) {
                return $query->where('event', $event);
            })
            ->latest()
            ->paginate(50);

        return view('admin.activity-logs.index', compact('activities'));
    }

    public function show(Activity $activity)
    {
        return view('admin.activity-logs.show', compact('activity'));
    }
}