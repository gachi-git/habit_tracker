<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoriesController extends Controller
{
    public function index()
    {
        $categories = Categories::where('user_id', Auth::id())
            ->withCount('habits')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        Categories::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'color' => $request->color,
        ]);

        return redirect()->route('categories.index')
            ->with('success', 'カテゴリが作成されました！');
    }

    public function show(Categories $category)
    {
        if ($category->user_id !== Auth::id()) {
            abort(403);
        }

        $habits = $category->habits()
            ->withCount('records')
            ->get();

        return view('categories.show', compact('category', 'habits'));
    }

    public function edit(Categories $category)
    {
        if ($category->user_id !== Auth::id()) {
            abort(403);
        }

        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Categories $category)
    {
        if ($category->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|max:255',
            'color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $category->update([
            'name' => $request->name,
            'color' => $request->color,
        ]);

        return redirect()->route('categories.index')
            ->with('success', 'カテゴリが更新されました！');
    }

    public function destroy(Categories $category)
    {
        if ($category->user_id !== Auth::id()) {
            abort(403);
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'カテゴリが削除されました！');
    }
}
