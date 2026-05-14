<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Approval;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Approval::with(['requestedBy', 'approvedBy', 'farm']);

        if (!$user->isAdmin()) {
            $query->where('farm_id', $user->farm_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('resource_type')) {
            $query->where('resource_type', $request->input('resource_type'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        $approvals = $query->orderByDesc('created_at')->paginate($request->input('per_page', 20));

        return $this->success($approvals);
    }

    public function pending(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Approval::pending()->with(['requestedBy', 'farm']);

        if (!$user->isAdmin()) {
            $query->where('farm_id', $user->farm_id);
        }

        $approvals = $query->orderBy('priority', 'desc')->orderBy('created_at')->get();

        return $this->success($approvals);
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!$user->canApprove()) {
            return $this->forbiddenError('You do not have permission to approve.');
        }

        $approval = Approval::findOrFail($id);

        if (!$user->isAdmin() && $approval->farm_id !== $user->farm_id) {
            return $this->forbiddenError('You do not have permission to approve this request.');
        }

        if (!$approval->isPending()) {
            return $this->domainError('Only pending approvals can be approved.', [], 'INVALID_STATUS', 422);
        }

        $validated = $request->validate([
            'approval_notes' => 'nullable|string|max:1000',
        ]);

        $approval->approve($user, $validated['approval_notes'] ?? null);

        return $this->success(
            $approval->fresh()->load(['requestedBy', 'approvedBy']),
            200,
            'Approval granted'
        );
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!$user->canApprove()) {
            return $this->forbiddenError('You do not have permission to reject.');
        }

        $approval = Approval::findOrFail($id);

        if (!$user->isAdmin() && $approval->farm_id !== $user->farm_id) {
            return $this->forbiddenError('You do not have permission to reject this request.');
        }

        if (!$approval->isPending()) {
            return $this->domainError('Only pending approvals can be rejected.', [], 'INVALID_STATUS', 422);
        }

        $validated = $request->validate([
            'rejected_reason' => 'required|string|max:1000',
        ]);

        $approval->markRejected($user, $validated['rejected_reason']);

        return $this->success(
            $approval->fresh()->load(['requestedBy', 'approvedBy']),
            200,
            'Approval rejected'
        );
    }

    public function sendReminder(int $id): JsonResponse
    {
        $user = auth()->user();

        $approval = Approval::findOrFail($id);

        if (!$approval->isPending()) {
            return $this->domainError('Only pending approvals can receive reminders.', [], 'INVALID_STATUS', 422);
        }

        $approval->update([
            'reminder_count' => $approval->reminder_count + 1,
            'last_reminder_at' => now(),
        ]);

        return $this->success(
            $approval->fresh(),
            200,
            'Reminder sent'
        );
    }
}
