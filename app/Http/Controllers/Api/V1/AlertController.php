<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Alert;
use App\Services\AlertService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Alert::with(['farm', 'recipientUser']);
        $user = $request->user();

        if (!$user->isAdmin()) {
            $query->where('farm_id', $user->farm_id)
                ->where(function ($query) use ($user) {
                    $query->where('recipient_role', $user->role)
                        ->orWhere('recipient_user_id', $user->id)
                        ->orWhereNull('recipient_role');
                });
        }

        if ($request->has('status')) {
            $query->where('status', $request->string('status'));
        }

        return $this->success($query->orderByDesc('created_at')->get());
    }

    public function trigger(Request $request, AlertService $alertService): JsonResponse
    {
        $user = $request->user();
        $farmId = $user->isAdmin()
            ? ($request->has('farm_id') ? $request->integer('farm_id') : null)
            : $user->farm_id;

        $alerts = $alertService->trigger($farmId);

        return $this->success([
            'created_or_updated' => $alerts->count(),
            'alerts' => $alerts->values(),
        ], 201);
    }

    public function markRead(Request $request, int $id): JsonResponse
    {
        $alert = Alert::findOrFail($id);
        $user = $request->user();

        if (!$user->isAdmin() && $alert->farm_id !== $user->farm_id) {
            return $this->forbiddenError('You do not have permission to update this alert.');
        }

        $alert->update([
            'status' => 'read',
            'read_at' => now(),
        ]);

        return $this->success($alert->refresh());
    }
}
