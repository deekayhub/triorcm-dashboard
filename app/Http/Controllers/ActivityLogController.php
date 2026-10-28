<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request): Response
    {
        $activities = Activity::query()
            ->with(['causer', 'subject'])
            ->latest()
            ->paginate(50)
            ->through(function (Activity $activity): array {
                return [
                    'id' => $activity->id,
                    'log_name' => $activity->log_name,
                    'description' => $activity->description,
                    'event' => $activity->event,
                    'subject_type' => $activity->subject_type ? class_basename($activity->subject_type) : null,
                    'subject_id' => $activity->subject_id,
                    'causer' => $activity->causer ? [
                        'id' => $activity->causer->id,
                        'name' => $activity->causer->name,
                        'email' => $activity->causer->email,
                    ] : null,
                    'properties' => $activity->properties?->toArray(),
                    'created_at' => $activity->created_at?->toIso8601String(),
                ];
            });

        return Inertia::render('activity-logs/Index', [
            'activities' => $activities,
        ]);
    }
}
