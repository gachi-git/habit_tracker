<?php

namespace App\Http\Controllers;

use App\Models\Habits;
use App\Models\Categories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HabitsController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        $categoryFilter = $request->get('category');
        
        $activeHabitsQuery = $user->habits()->where('is_active', true)->with(['category', 'habitRecords']);
        if ($categoryFilter && $categoryFilter !== 'all') {
            if ($categoryFilter === 'none') {
                $activeHabitsQuery->whereNull('category_id');
            } else {
                $activeHabitsQuery->where('category_id', $categoryFilter);
            }
        }
        $activeHabits = $activeHabitsQuery->get();
        
        $totalHabits = $user->habits()->count();
        $recentHabits = $user->habits()->latest()->take(5)->with('category')->get();
        $categories = $user->categories()->withCount('habits')->get();
        
        // 今日の記録状況を取得
        $today = now()->format('Y-m-d');
        $todayRecords = [];
        foreach ($activeHabits as $habit) {
            $record = $habit->habitRecords()->where('recorded_date', $today)->first();
            $todayRecords[$habit->id] = $record;
        }
        
        // 統計情報を取得
        $totalCurrentStreak = $activeHabits->sum(fn($habit) => $habit->getCurrentStreak());
        $totalRecordsThisWeek = 0;
        $avgWeeklyCompletionRate = 0;
        
        if ($activeHabits->count() > 0) {
            $weeklyRates = $activeHabits->map(fn($habit) => $habit->getThisWeekCompletionRate());
            $avgWeeklyCompletionRate = $weeklyRates->avg();
            
            foreach ($activeHabits as $habit) {
                $totalRecordsThisWeek += $habit->habitRecords()
                    ->where('completed', true)
                    ->whereBetween('recorded_date', [
                        now()->startOfWeek()->format('Y-m-d'),
                        now()->endOfWeek()->format('Y-m-d')
                    ])
                    ->count();
            }
        }
        
        // 過去7日間のグラフデータを生成
        $chartData = $this->generateWeeklyChartData($activeHabits);
        
        return view('dashboard', compact(
            'activeHabits', 
            'totalHabits', 
            'recentHabits', 
            'todayRecords',
            'totalCurrentStreak',
            'totalRecordsThisWeek',
            'avgWeeklyCompletionRate',
            'categories',
            'categoryFilter',
            'chartData'
        ));
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

    private function generateWeeklyChartData($activeHabits)
    {
        $labels = [];
        $data = [];
        
        // 過去7日間のデータを生成
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('n/j'); // 月/日形式
            
            // その日に完了した習慣の数をカウント
            $completedCount = 0;
            foreach ($activeHabits as $habit) {
                $record = $habit->habitRecords()
                    ->where('recorded_date', $date->format('Y-m-d'))
                    ->where('completed', true)
                    ->first();
                
                if ($record) {
                    $completedCount++;
                }
            }
            
            $data[] = $completedCount;
        }
        
        return [
            'labels' => $labels,
            'data' => $data,
            'totalHabits' => $activeHabits->count()
        ];
    }
}
