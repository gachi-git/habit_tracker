<?php

namespace App\Http\Controllers;

use App\Models\Habits;
use App\Models\Categories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HabitsController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $activeHabits = $user->habits()->where('is_active', true)->with(['category', 'habitRecords'])->get();
        $totalHabits = $user->habits()->count();
        $recentHabits = $user->habits()->latest()->take(5)->with('category')->get();
        
        // 今日の記録状況を取得
        $today = now()->format('Y-m-d');
        $todayRecords = [];
        foreach ($activeHabits as $habit) {
            $record = $habit->habitRecords()->where('recorded_date', $today)->first();
            $todayRecords[$habit->id] = $record;
        }
        
        return view('dashboard', compact('activeHabits', 'totalHabits', 'recentHabits', 'todayRecords'));
    }

    public function index()
    {
        $habits = Auth::user()->habits()->with('category')->get();
        return view('habits.index', compact('habits'));
    }

    public function create()
    {
        $categories = Auth::user()->categories;
        return view('habits.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'target_frequency' => 'required|integer|min:1',
            'target_unit' => 'required|in:daily,weekly,monthly'
        ]);

        Auth::user()->habits()->create($request->all());

        return redirect()->route('habits.index')
            ->with('success', '習慣を作成しました。');
    }

    public function show(Habits $habit)
    {
        return view('habits.show', compact('habit'));
    }

    public function edit(Habits $habit)
    {
        $categories = Auth::user()->categories;
        return view('habits.edit', compact('habit', 'categories'));
    }

    public function update(Request $request, Habits $habit)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'target_frequency' => 'required|integer|min:1',
            'target_unit' => 'required|in:daily,weekly,monthly',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active') ? true : false;
        
        $habit->update($data);

        return redirect()->route('habits.index')
            ->with('success', '習慣を更新しました。');
    }

    public function destroy(Habits $habit)
    {
        $habit->delete();

        return redirect()->route('habits.index')
            ->with('success', '習慣を削除しました。');
    }
}
