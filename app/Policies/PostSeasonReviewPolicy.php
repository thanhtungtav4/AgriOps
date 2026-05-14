<?php

namespace App\Policies;

use App\Models\PostSeasonReview;
use App\Models\User;

class PostSeasonReviewPolicy
{
    /**
     * View list - admin, farm_manager, technician, warehouse
     * Note: agronomist = farm_manager, warehouse_leader = warehouse
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'farm_manager', 'technician', 'warehouse']);
    }

    /**
     * View detail - same as viewAny
     */
    public function view(User $user, PostSeasonReview $postSeasonReview): bool
    {
        return in_array($user->role, ['admin', 'farm_manager', 'technician', 'warehouse']);
    }

    /**
     * Create/Update - admin, farm_manager, technician
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'farm_manager', 'technician']);
    }

    public function update(User $user, PostSeasonReview $postSeasonReview): bool
    {
        if (!in_array($user->role, ['admin', 'farm_manager', 'technician'])) {
            return false;
        }
        // Only draft can be updated
        return $postSeasonReview->status === PostSeasonReview::STATUSES['draft'];
    }

    /**
     * Submit - farm_manager or technician who authored the review
     */
    public function submit(User $user, PostSeasonReview $postSeasonReview): bool
    {
        return in_array($user->role, ['admin', 'farm_manager', 'technician'])
            && $postSeasonReview->status === PostSeasonReview::STATUSES['draft'];
    }

    /**
     * Approve/Reject - admin, farm_owner (owner can approve)
     */
    public function approve(User $user, PostSeasonReview $postSeasonReview): bool
    {
        return in_array($user->role, ['admin', 'farm_owner', 'farm_manager'])
            && $postSeasonReview->status === PostSeasonReview::STATUSES['submitted'];
    }

    /**
     * Delete - only admin, and only if draft
     */
    public function delete(User $user, PostSeasonReview $postSeasonReview): bool
    {
        return $user->role === 'admin'
            && $postSeasonReview->status === PostSeasonReview::STATUSES['draft'];
    }
}