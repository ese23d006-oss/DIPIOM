<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Reports\ReportBuilder;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    public function stats(ReportBuilder $builder): JsonResponse
    {
        return response()->json([
            'stats' => $builder->build('stats')['rows']->map(fn (array $row) => ['label' => $row[0], 'value' => $row[1]])->values(),
        ]);
    }

    public function users(): JsonResponse
    {
        return response()->json(
            User::latest()->paginate(30, ['id', 'name', 'email', 'role', 'is_active', 'created_at'])
        );
    }
}
