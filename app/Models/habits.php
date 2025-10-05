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
     * 現在の連続実行期間を取得
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

        return match($this->target_unit) {
            'daily' => $this->calculateDailyStreak($records),
            'weekly' => $this->calculateWeeklyStreak($records),
            'monthly' => $this->calculateMonthlyStreak($records),
            default => $this->calculateDailyStreak($records),
        };
    }

    /**
     * 日次習慣のストリーク計算
     */
    private function calculateDailyStreak($records): int
    {
        $streak = 0;
        $expectedDate = Carbon::now();
        
        foreach ($records as $record) {
            $recordDate = Carbon::parse($record->recorded_date);
            
            if ($streak === 0) {
                // 最初の記録は今日または昨日まで許可
                $daysDiff = $expectedDate->diffInDays($recordDate);
                if ($daysDiff <= 1) {
                    $streak = 1;
                    $expectedDate = $recordDate->copy()->subDay();
                } else {
                    break;
                }
            } else {
                // 連続する前日である必要がある
                if ($recordDate->format('Y-m-d') === $expectedDate->format('Y-m-d')) {
                    $streak++;
                    $expectedDate = $recordDate->copy()->subDay();
                } else {
                    break;
                }
            }
        }
        
        return $streak;
    }

    /**
     * 週次習慣のストリーク計算
     */
    private function calculateWeeklyStreak($records): int
    {
        $streak = 0;
        $expectedWeekStart = Carbon::now()->startOfWeek();
        
        foreach ($records as $record) {
            $recordDate = Carbon::parse($record->recorded_date);
            $recordWeekStart = $recordDate->copy()->startOfWeek();
            
            if ($streak === 0) {
                // 最初の記録は今週または先週まで許可
                if ($recordWeekStart->diffInWeeks($expectedWeekStart) <= 1) {
                    $streak = 1;
                    $expectedWeekStart = $recordWeekStart->copy()->subWeek();
                } else {
                    break;
                }
            } else {
                // 連続する前週である必要がある
                if ($recordWeekStart->format('Y-m-d') === $expectedWeekStart->format('Y-m-d')) {
                    $streak++;
                    $expectedWeekStart = $recordWeekStart->copy()->subWeek();
                } else {
                    break;
                }
            }
        }
        
        return $streak;
    }

    /**
     * 月次習慣のストリーク計算
     */
    private function calculateMonthlyStreak($records): int
    {
        $streak = 0;
        $expectedMonthStart = Carbon::now()->startOfMonth();
        
        foreach ($records as $record) {
            $recordDate = Carbon::parse($record->recorded_date);
            $recordMonthStart = $recordDate->copy()->startOfMonth();
            
            if ($streak === 0) {
                // 最初の記録は今月または先月まで許可
                if ($recordMonthStart->diffInMonths($expectedMonthStart) <= 1) {
                    $streak = 1;
                    $expectedMonthStart = $recordMonthStart->copy()->subMonth();
                } else {
                    break;
                }
            } else {
                // 連続する前月である必要がある
                if ($recordMonthStart->format('Y-m') === $expectedMonthStart->format('Y-m')) {
                    $streak++;
                    $expectedMonthStart = $recordMonthStart->copy()->subMonth();
                } else {
                    break;
                }
            }
        }
        
        return $streak;
    }

    /**
     * 現在の期間を取得
     */
    private function getCurrentPeriod(): string
    {
        $now = Carbon::now();
        
        return match($this->target_unit) {
            'daily' => $now->format('Y-m-d'),
            'weekly' => $now->startOfWeek()->format('Y-m-d'),
            'monthly' => $now->format('Y-m'),
            default => $now->format('Y-m-d'),
        };
    }

    /**
     * 指定日付の期間を取得
     */
    private function getPeriodForDate(Carbon $date): string
    {
        return match($this->target_unit) {
            'daily' => $date->format('Y-m-d'),
            'weekly' => $date->startOfWeek()->format('Y-m-d'),
            'monthly' => $date->format('Y-m'),
            default => $date->format('Y-m-d'),
        };
    }

    /**
     * 現在または直近の期間かチェック
     */
    private function isCurrentOrRecentPeriod(string $recordPeriod, string $currentPeriod): bool
    {
        if ($recordPeriod === $currentPeriod) {
            return true;
        }

        return match($this->target_unit) {
            'daily' => Carbon::parse($recordPeriod)->diffInDays(Carbon::parse($currentPeriod)) <= 1,
            'weekly' => Carbon::parse($recordPeriod)->diffInWeeks(Carbon::parse($currentPeriod)) <= 1,
            'monthly' => Carbon::createFromFormat('Y-m', $recordPeriod)->diffInMonths(Carbon::createFromFormat('Y-m', $currentPeriod)) <= 1,
            default => false,
        };
    }

    /**
     * 連続する期間かチェック
     */
    private function isConsecutivePeriod(string $recordPeriod, string $currentPeriod): bool
    {
        return match($this->target_unit) {
            'daily' => Carbon::parse($currentPeriod)->diffInDays(Carbon::parse($recordPeriod)) === 1,
            'weekly' => Carbon::parse($currentPeriod)->diffInWeeks(Carbon::parse($recordPeriod)) === 1,
            'monthly' => Carbon::createFromFormat('Y-m', $currentPeriod)->diffInMonths(Carbon::createFromFormat('Y-m', $recordPeriod)) === 1,
            default => false,
        };
    }

    /**
     * 最長連続記録を取得
     */
    public function getLongestStreak(): int
    {
        $records = $this->habitRecords()
            ->where('completed', true)
            ->orderBy('recorded_date', 'asc')
            ->get();

        if ($records->isEmpty()) {
            return 0;
        }

        return match($this->target_unit) {
            'daily' => $this->calculateLongestDailyStreak($records),
            'weekly' => $this->calculateLongestWeeklyStreak($records),
            'monthly' => $this->calculateLongestMonthlyStreak($records),
            default => $this->calculateLongestDailyStreak($records),
        };
    }

    /**
     * 日次習慣の最長ストリーク計算
     */
    private function calculateLongestDailyStreak($records): int
    {
        if ($records->count() === 0) {
            return 0;
        }
        
        $longestStreak = 1;
        $currentStreak = 1;
        $previousDate = null;

        foreach ($records as $record) {
            $recordDate = Carbon::parse($record->recorded_date);
            
            if ($previousDate !== null) {
                // 前の日の翌日（連続）かチェック
                if ($previousDate->copy()->addDay()->format('Y-m-d') === $recordDate->format('Y-m-d')) {
                    $currentStreak++;
                    $longestStreak = max($longestStreak, $currentStreak);
                } else {
                    $currentStreak = 1;
                }
            }
            
            $previousDate = $recordDate;
        }

        return $longestStreak;
    }

    /**
     * 週次習慣の最長ストリーク計算
     */
    private function calculateLongestWeeklyStreak($records): int
    {
        $uniqueWeeks = $records->map(fn($record) => 
            Carbon::parse($record->recorded_date)->startOfWeek()->format('Y-m-d')
        )->unique()->values();

        if ($uniqueWeeks->count() <= 1) {
            return $uniqueWeeks->count();
        }

        $longestStreak = 1;
        $currentStreak = 1;

        for ($i = 1; $i < $uniqueWeeks->count(); $i++) {
            $currentWeek = Carbon::parse($uniqueWeeks[$i]);
            $previousWeek = Carbon::parse($uniqueWeeks[$i-1]);
            
            // 前の週の翌週（連続）かチェック
            if ($previousWeek->copy()->addWeek()->format('Y-m-d') === $currentWeek->format('Y-m-d')) {
                $currentStreak++;
                $longestStreak = max($longestStreak, $currentStreak);
            } else {
                $currentStreak = 1;
            }
        }

        return $longestStreak;
    }

    /**
     * 月次習慣の最長ストリーク計算
     */
    private function calculateLongestMonthlyStreak($records): int
    {
        $uniqueMonths = $records->map(fn($record) => 
            Carbon::parse($record->recorded_date)->format('Y-m')
        )->unique()->values();

        if ($uniqueMonths->count() <= 1) {
            return $uniqueMonths->count();
        }

        $longestStreak = 1;
        $currentStreak = 1;

        for ($i = 1; $i < $uniqueMonths->count(); $i++) {
            $currentMonth = Carbon::createFromFormat('Y-m', $uniqueMonths[$i]);
            $previousMonth = Carbon::createFromFormat('Y-m', $uniqueMonths[$i-1]);
            
            if ($currentMonth->diffInMonths($previousMonth) === 1) {
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
            // Fix: Calculate months by normalizing to start of months first
            $startMonth = $startDate->copy()->startOfMonth();
            $endMonth = $endDate->copy()->startOfMonth();
            $months = $startMonth->diffInMonths($endMonth) + 1;
            $targetCount = $months * $this->target_frequency;
        }

        $completedCount = $this->habitRecords()
            ->where('completed', true)
            ->whereBetween('recorded_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->count();

        // Fix: Handle target_frequency = 0 case (return 100% since there's no target to miss)
        if ($this->target_frequency == 0) {
            return 100.0;
        }

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
     * 今年の達成率
     */
    public function getThisYearCompletionRate(): float
    {
        $startOfYear = Carbon::now()->startOfYear();
        $endOfYear = Carbon::now()->endOfYear();
        
        return $this->getCompletionRate($startOfYear, $endOfYear);
    }

    /**
     * 今日の達成率
     */
    public function getTodayCompletionRate(): float
    {
        $today = Carbon::now();
        
        return $this->getCompletionRate($today, $today);
    }

    /**
     * target_unitに応じた主要な達成率を取得
     */
    public function getPrimaryCompletionRate(): array
    {
        return match($this->target_unit) {
            'daily' => [
                'label' => '今日の達成率',
                'rate' => $this->getTodayCompletionRate(),
                'period' => 'day'
            ],
            'weekly' => [
                'label' => '今週の達成率', 
                'rate' => $this->getThisWeekCompletionRate(),
                'period' => 'week'
            ],
            'monthly' => [
                'label' => '今月の達成率',
                'rate' => $this->getThisMonthCompletionRate(), 
                'period' => 'month'
            ],
            default => [
                'label' => '今日の達成率',
                'rate' => $this->getTodayCompletionRate(),
                'period' => 'day'
            ]
        };
    }

    /**
     * target_unitに応じた詳細統計を取得
     */
    public function getDetailedStats(): array
    {
        return match($this->target_unit) {
            'daily' => [
                ['label' => '今日', 'rate' => $this->getTodayCompletionRate()],
            ],
            'weekly' => [
                ['label' => '今週', 'rate' => $this->getThisWeekCompletionRate()],
            ],
            'monthly' => [
                ['label' => '今月', 'rate' => $this->getThisMonthCompletionRate()],
            ],
            default => [
                ['label' => '今日', 'rate' => $this->getTodayCompletionRate()],
            ]
        };
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
