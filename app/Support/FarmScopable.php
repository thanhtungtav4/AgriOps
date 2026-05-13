<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait FarmScopable
{
    protected function applyFarmScope(Request $request, Builder $query): Builder
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where('farm_id', $user->farm_id);
    }

    protected function getFarmScope(Request $request): ?int
    {
        return $request->attributes->get('farm_scope');
    }
}
