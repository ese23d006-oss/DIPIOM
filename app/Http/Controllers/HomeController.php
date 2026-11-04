<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('home', [
            'categories' => Category::withCount(['courses' => fn (Builder $query) => $query->published()])
                ->orderBy('name')
                ->get(),
            'featuredCourses' => Course::published()
                ->with('category')
                ->withCount('publishedLessons')
                ->latest()
                ->limit(3)
                ->get(),
        ]);
    }
}
