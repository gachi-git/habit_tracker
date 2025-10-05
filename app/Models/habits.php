<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Habits extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'name',
        'description',
        'target_frequency',
        'target_unit',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Categories::class);
    }

    public function habitRecords(): HasMany
    {
        return $this->hasMany(HabitRecords::class, 'habit_id');
    }

    // 統計機能

    /**
     * 現在の連続実行日数を取得
     */
    public function getCurrentStreak(): int
    {
        $records = $this->habitRecords()
            ->where('completed', true)
            ->orderBy('recorded_date', 'desc')
            ->get();

        if ($records->isEmpty()) {
            return 0;
        }

        $streak = 0;
        $currentDate = Carbon::now();

        foreach ($records as $record) {
            $recordDate = Carbon::parse($record->recorded_date);
            $diffInDays = $currentDate->diffInDays($recordDate);

            if ($streak === 0 && $diffInDays <= 1) {
                // 最初の記録（今日または昨日）
                $streak = 1;
                $currentDate = $recordDate;
            } elseif ($diffInDays === 1) {
                // 連続している
                $streak++;
                $currentDate = $recordDate;
            } else {
                // 連続が途切れた
                break;
            }
        }

        return $streak;
    }

    /**
     * 最長連続記録を取得
     */
    public function getLongestStreak(): int
    {
        $records = $this->habitRecords()
            ->where('completed', true)
            ->orderBy('recorded_date', 'asc')
            ->pluck('recorded_date')
            ->map(fn($date) => Carbon::parse($date))
            ->toArray();

        if (empty($records)) {
            return 0;
        }

        $longestStreak = 1;
        $currentStreak = 1;

        for ($i = 1; $i < count($records); $i++) {
            if ($records[$i]->diffInDays($records[$i - 1]) === 1) {
                $currentStreak++;
                $longestStreak = max($longestStreak, $currentStreak);
            } else {
                $currentStreak = 1;
            }
        }

        return $longestStreak;
    }

    /**
     * 指定期間の達成率を取得
     */
    public function getCompletionRate(Carbon $startDate, Carbon $endDate): float
    {
        $totalDays = $startDate->diffInDays($endDate) + 1;
        
        if ($this->target_unit === 'daily') {
            $targetCount = $totalDays * $this->target_frequency;
        } elseif ($this->target_unit === 'weekly') {
            $targetCount = $this->target_frequency;
        } else { // monthly
            $months = $startDate->diffInMonths($endDate) + 1;
            $targetCount = $months * $this->target_frequency;
        }

        $completedCount = $this->habitRecords()
            ->where('completed', true)
            ->whereBetween('recorded_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->count();


        return $targetCount > 0 ? ($completedCount / $targetCount) * 100 : 0;
    }

    /**
     * 今週の達成率
     */
    public function getThisWeekCompletionRate(): float
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        
        return $this->getCompletionRate($startOfWeek, $endOfWeek);
    }

    /**
     * 今月の達成率
     */
    public function getThisMonthCompletionRate(): float
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        return $this->getCompletionRate($startOfMonth, $endOfMonth);
    }

    /**
     * 総記録数
     */
    public function getTotalRecords(): int
    {
        return $this->habitRecords()->where('completed', true)->count();
    }

    /**
     * 平均実施時間（分）
     */
    public function getAverageDuration(): float
    {
        return $this->habitRecords()
            ->where('completed', true)
            ->whereNotNull('duration_minutes')
            ->avg('duration_minutes') ?? 0;
    }

    /**
     * 最近7日間の実行状況
     */
    public function getRecentActivity(): array
    {
        $last7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $record = $this->habitRecords()
                ->where('recorded_date', $date->format('Y-m-d'))
                ->first();
            
            $last7Days[] = [
                'date' => $date->format('Y-m-d'),
                'day' => $date->format('n/j'),
                'completed' => $record ? $record->completed : false,
                'duration' => $record ? $record->duration_minutes : null,
            ];
        }
        
        return $last7Days;
    }
}
