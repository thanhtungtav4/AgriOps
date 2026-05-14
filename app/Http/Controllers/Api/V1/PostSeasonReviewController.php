<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\PostSeasonReview\StorePostSeasonReviewRequest;
use App\Http\Requests\Api\V1\PostSeasonReview\UpdatePostSeasonReviewRequest;
use App\Http\Responses\ApiResponse;
use App\Models\PostSeasonReview;
use App\Services\PostSeasonReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostSeasonReviewController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly PostSeasonReviewService $service
    ) {}

    /**
     * List all reviews (with optional filters)
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = PostSeasonReview::with(['productionPlan', 'submittedBy', 'approvedBy']);

        // Non-admin users can only see their farm's reviews
        if (!$user->isAdmin()) {
            $query->whereHas('productionPlan', fn($q) => $q->where('farm_id', $user->farm_id));
        }

        if ($request->filled('production_plan_id')) {
            $query->where('production_plan_id', $request->input('production_plan_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $reviews = $query->latest()->paginate($request->input('per_page', 20));

        return $this->success($reviews);
    }

    /**
     * Create a new review
     */
    public function store(StorePostSeasonReviewRequest $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->canManagePostSeasonReview()) {
            return $this->forbiddenError('You do not have permission to create post-season reviews.');
        }

        // Check that production plan belongs to user's farm
        $productionPlan = \App\Models\ProductionPlan::find($request->input('production_plan_id'));
        if (!$user->isAdmin() && $productionPlan && $productionPlan->farm_id !== $user->farm_id) {
            return $this->forbiddenError('You can only create reviews for your own farm\'s production plans.');
        }

        // Check if review already exists for this plan
        $existing = $this->service->getByPlan($request->input('production_plan_id'));
        if ($existing) {
            return response()->json([
                'error' => [
                    'code' => 'DUPLICATE_REVIEW',
                    'message' => 'Post-season review already exists for this plan.',
                    'details' => ['existing_review_id' => $existing->id],
                    'trace_id' => $request->header('X-Trace-ID') ?? uniqid(),
                ],
            ], 409);
        }

        $review = PostSeasonReview::create($request->validated());

        return $this->success(
            $review->load(['productionPlan', 'submittedBy', 'approvedBy']),
            201,
            'Post-season review created'
        );
    }

    /**
     * View single review
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $review = PostSeasonReview::with(['productionPlan', 'submittedBy', 'approvedBy'])->findOrFail($id);
        $user = $request->user();

        if (!$user->isAdmin() && $review->productionPlan->farm_id !== $user->farm_id) {
            return $this->forbiddenError('You do not have permission to view this review.');
        }

        return $this->success($review);
    }

    /**
     * Update review (draft only)
     */
    public function update(UpdatePostSeasonReviewRequest $request, int $id): JsonResponse
    {
        $review = PostSeasonReview::findOrFail($id);
        $user = $request->user();

        if (!$user->isAdmin() && $review->productionPlan->farm_id !== $user->farm_id) {
            return $this->forbiddenError('You do not have permission to update this review.');
        }

        if (!$user->canManagePostSeasonReview()) {
            return $this->forbiddenError('You do not have permission to update reviews.');
        }

        if ($review->status !== PostSeasonReview::STATUSES['draft']) {
            return $this->domainError('Only draft reviews can be updated.', [], 'INVALID_STATUS', 422);
        }

        $review->update($request->validated());

        return $this->success(
            $review->fresh()->load(['productionPlan', 'submittedBy', 'approvedBy']),
            200,
            'Post-season review updated'
        );
    }

    /**
     * Submit for approval
     */
    public function submit(int $id): JsonResponse
    {
        $review = PostSeasonReview::findOrFail($id);
        $user = auth()->user();

        if (!$user->canManagePostSeasonReview()) {
            return $this->forbiddenError('You do not have permission to submit reviews.');
        }

        // Non-admin users can only submit reviews for their own farm
        if (!$user->isAdmin() && $review->productionPlan->farm_id !== $user->farm_id) {
            return $this->forbiddenError('You can only submit reviews for your own farm.');
        }

        if ($review->status !== PostSeasonReview::STATUSES['draft']) {
            return $this->domainError('Only draft reviews can be submitted.', [], 'INVALID_STATUS', 422);
        }

        $review = $this->service->submit($review, $user->id);

        return $this->success(
            $review->load(['productionPlan', 'submittedBy', 'approvedBy']),
            200,
            'Post-season review submitted for approval'
        );
    }

    /**
     * Approve review
     */
    public function approve(int $id): JsonResponse
    {
        $review = PostSeasonReview::findOrFail($id);
        $user = auth()->user();

        if (!$user->canApprove()) {
            return $this->forbiddenError('Only approver roles can approve post-season reviews.');
        }

        // Non-admin users can only approve reviews for their own farm
        if (!$user->isAdmin() && $review->productionPlan->farm_id !== $user->farm_id) {
            return $this->forbiddenError('You can only approve reviews for your own farm.');
        }

        if ($review->status !== PostSeasonReview::STATUSES['submitted']) {
            return $this->domainError('Only submitted reviews can be approved.', [], 'INVALID_STATUS', 422);
        }

        $review = $this->service->approve($review, $user->id);

        return $this->success(
            $review->load(['productionPlan', 'submittedBy', 'approvedBy']),
            200,
            'Post-season review approved'
        );
    }

    /**
     * Reject review
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        $review = PostSeasonReview::findOrFail($id);
        $user = auth()->user();

        if (!$user->canApprove()) {
            return $this->forbiddenError('Only approver roles can reject post-season reviews.');
        }

        // Non-admin users can only reject reviews for their own farm
        if (!$user->isAdmin() && $review->productionPlan->farm_id !== $user->farm_id) {
            return $this->forbiddenError('You can only reject reviews for your own farm.');
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        if ($review->status !== PostSeasonReview::STATUSES['submitted']) {
            return $this->domainError('Only submitted reviews can be rejected.', [], 'INVALID_STATUS', 422);
        }

        $review = $this->service->reject($review, $user->id, $validated['reason']);

        return $this->success(
            $review->load(['productionPlan', 'submittedBy', 'approvedBy']),
            200,
            'Post-season review rejected'
        );
    }

    /**
     * Delete draft review
     */
    public function destroy(int $id): JsonResponse
    {
        $review = PostSeasonReview::findOrFail($id);

        if (!auth()->user()->isAdmin()) {
            return $this->forbiddenError('Only admins can delete reviews.');
        }

        if ($review->status !== PostSeasonReview::STATUSES['draft']) {
            return $this->domainError('Only draft reviews can be deleted.', [], 'INVALID_STATUS', 422);
        }

        $review->delete();

        return $this->success(null, 200, 'Post-season review deleted');
    }
}