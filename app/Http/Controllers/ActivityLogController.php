<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;

class ActivityLogController extends Controller
{
    public function index(Request $request): Response|JsonResponse
    {
        if ($request->ajax() && $request->has('draw')) {
            $query = Activity::query()->with(['causer', 'subject'])->latest();

            return DataTables::of($query)
                ->addColumn('created_at', function (Activity $activity) {
                    return $activity->created_at?->format('d M Y H:i:s');
                })
                ->addColumn('causer', function (Activity $activity) {
                    if (! $activity->causer) {
                        return null;
                    }

                    return [
                        'name' => $activity->causer->name,
                        'email' => $activity->causer->email,
                    ];
                })
                ->addColumn('event', function (Activity $activity) {
                    return $activity->event;
                })
                ->addColumn('description', function (Activity $activity) {
                    return $activity->description;
                })
                ->addColumn('subject', function (Activity $activity) {
                    if (! $activity->subject_type) {
                        return null;
                    }

                    return [
                        'type' => class_basename($activity->subject_type),
                        'id' => $activity->subject_id,
                    ];
                })
                ->addColumn('ip_address', function (Activity $activity) {
                    $event = strtolower((string) $activity->event);
                    if ($event === 'login' || $event === 'logout') {
                        $properties = $activity->properties?->toArray() ?? [];

                        return $properties['ip'] ?? $properties['ip_address'] ?? $properties['ipAddress'] ?? null;
                    }

                    return null;
                })
                ->addColumn('browser', function (Activity $activity) {
                    $event = strtolower((string) $activity->event);
                    if ($event === 'login' || $event === 'logout') {
                        $properties = $activity->properties?->toArray() ?? [];

                        return $properties['user_agent'] ?? $properties['userAgent'] ?? $properties['browser'] ?? null;
                    }

                    return null;
                })
                ->filterColumn('causer', function ($query, $keyword) {
                    $query->whereHas('causer', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('description', function ($query, $keyword) {
                    $query->where('description', 'like', "%{$keyword}%");
                })
                ->filterColumn('event', function ($query, $keyword) {
                    $query->where('event', 'like', "%{$keyword}%");
                })
                ->orderColumn('created_at', function ($query, $order) {
                    $query->orderBy('created_at', $order);
                })
                ->rawColumns(['causer', 'event', 'subject', 'ip_address', 'browser'])
                ->make(true);
        }

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
                    'created_at' => $activity->created_at?->format('d M Y H:i:s'),
                ];
            });

        return Inertia::render('activity-logs/Index', [
            'activities' => $activities,
        ]);
    }
}
