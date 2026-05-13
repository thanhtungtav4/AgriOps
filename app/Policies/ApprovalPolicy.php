<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class ApprovalPolicy
{
    public function approve(User $user): bool
    {
        if (!in_array($user->role, [User::ROLE_ADMIN, User::ROLE_FARM_OWNER, User::ROLE_FARM_MANAGER])) {
            throw new AuthorizationException('Only approvers can approve.');
        }
        return true;
    }
}
