<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use App\Support\UniqueSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => Category::withCount('courses')->orderBy('name')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.form', ['category' => new Category]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $category = new Category($request->validated());
        $category->slug = UniqueSlug::for(Category::class, $category->name, 'category');
        $category->save();

        return redirect()->route('admin.categories.index')->with('status', "{$category->name} ангиллыг нэмлээ.");
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.form', ['category' => $category]);
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        // Хуучин холбоос ажиллах ёстой тул slug-ийг өөрчлөхгүй.
        $category->update($request->validated());

        return redirect()->route('admin.categories.index')->with('status', "{$category->name} ангиллыг хадгаллаа.");
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->courses()->exists()) {
            return back()->with('error', "{$category->name} ангилалд сургалт байгаа тул устгах боломжгүй. Эхлээд сургалтуудыг өөр ангилалд шилжүүлнэ үү.");
        }

        $name = $category->name;
        $category->delete();

        return back()->with('status', "{$name} ангиллыг устгалаа.");
    }
}
