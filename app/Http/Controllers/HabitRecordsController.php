<?php

namespace App\Http\Controllers;

use App\Models\HabitRecords;
use App\Models\Habits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HabitRecordsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Habits $habit)
    {
        // 習慣の所有者チェック
        if ($habit->user_id !== Auth::id()) {
            abort(403);
        }

        $records = $habit->habitRecords()
            ->orderBy('recorded_date', 'desc')
            ->paginate(30);

        return view('habit-records.index', compact('habit', 'records'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Habits $habit)
    {
        // 習慣の所有者チェック
        if ($habit->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'recorded_date' => 'required|date',
            'completed' => 'boolean',
            'note' => 'nullable|string|max:500',
            'duration_minutes' => 'nullable|integer|min:1|max:1440',
        ]);

        // 同じ日の記録が既に存在するかチェック
        $existingRecord = HabitRecords::where('habit_id', $habit->id)
            ->where('recorded_date', $request->recorded_date)
            ->first();

        if ($existingRecord) {
            return back()->withErrors(['recorded_date' => 'この日の記録は既に存在します。']);
        }

        HabitRecords::create([
            'habit_id' => $habit->id,
            'recorded_date' => $request->recorded_date,
            'completed' => $request->has('completed'),
            'note' => $request->note,
            'duration_minutes' => $request->duration_minutes,
        ]);

        return back()->with('success', '記録を追加しました！');
    }

    /**
     * Display the specified resource.
     */
    public function show(HabitRecords $habit_records)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(HabitRecords $habit_records)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, HabitRecords $habit_records)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HabitRecords $record)
    {
        // 記録の習慣の所有者チェック
        if ($record->habit->user_id !== Auth::id()) {
            abort(403);
        }

        $record->delete();

        return back()->with('success', '記録を削除しました。');
    }
}
