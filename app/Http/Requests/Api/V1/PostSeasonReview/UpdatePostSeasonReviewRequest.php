<?php

namespace App\Http\Requests\Api\V1\PostSeasonReview;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostSeasonReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled via policy
    }

    public function rules(): array
    {
        return [
            'status' => 'nullable|in:draft,submitted,approved,rejected',
            'actual_performance_summary' => 'nullable|string|max:5000',
            'yield_analysis' => 'nullable|string|max:5000',
            'quality_assessment' => 'nullable|string|max:5000',
            'resource_utilization_review' => 'nullable|string|max:5000',
            'pest_disease_review' => 'nullable|string|max:5000',
            'weather_impact_analysis' => 'nullable|string|max:5000',
            'lessons_learned' => 'nullable|string|max:5000',
            'recommendations' => 'nullable|string|max:5000',
            'next_season_improvements' => 'nullable|string|max:5000',
            'rejected_reason' => 'nullable|string|max:1000',
        ];
    }
}