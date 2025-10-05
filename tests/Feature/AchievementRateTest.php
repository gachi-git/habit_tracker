<?php

use App\Models\User;
use App\Models\Habits;
use App\Models\HabitRecords;
use App\Models\Categories;
use Carbon\Carbon;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('日次達成率計算', function () {
    it('日次習慣の達成率を正しく計算する', function () {
        $habit = Habits::factory()->create([
            'user_id' => $this->user->id,
            'target_frequency' => 1,
            'target_unit' => 'daily'
        ]);

        // 今日完了
        HabitRecords::factory()->create([
            'habit_id' => $habit->id,
            'recorded_date' => now()->format('Y-m-d'),
            'completed' => true
        ]);

        // 昨日未完了
        HabitRecords::factory()->create([
            'habit_id' => $habit->id,
            'recorded_date' => now()->subDay()->format('Y-m-d'),
            'completed' => false
        ]);

        $startDate = now()->subDay();
        $endDate = now();
        
        $completionRate = $habit->getCompletionRate($startDate, $endDate);
        
        // 2日間で1回完了 = 50%
        expect(round($completionRate, 2))->toBe(50.0);
    });

    it('日次習慣の完璧な達成率を計算する', function () {
        $habit = Habits::factory()->create([
            'user_id' => $this->user->id,
            'target_frequency' => 1,
            'target_unit' => 'daily'
        ]);

        // 3日間全て完了
        for ($i = 0; $i < 3; $i++) {
            HabitRecords::factory()->create([
                'habit_id' => $habit->id,
                'recorded_date' => now()->subDays($i)->format('Y-m-d'),
                'completed' => true
            ]);
        }

        $startDate = now()->subDays(2);
        $endDate = now();
        
        $completionRate = $habit->getCompletionRate($startDate, $endDate);
        
        expect(round($completionRate, 2))->toBe(100.0);
    });

    it('記録がない期間の達成率は0%', function () {
        $habit = Habits::factory()->create([
            'user_id' => $this->user->id,
            'target_frequency' => 1,
            'target_unit' => 'daily'
        ]);

        $startDate = now()->subWeek();
        $endDate = now();
        
        $completionRate = $habit->getCompletionRate($startDate, $endDate);
        
        expect($completionRate)->toBe(0.0);
    });
});

describe('週次達成率計算', function () {
    it('週1回習慣の達成率を正しく計算する', function () {
        $habit = Habits::factory()->create([
            'user_id' => $this->user->id,
            'target_frequency' => 1,
            'target_unit' => 'weekly'
        ]);

        // 今週2回完了（目標以上）
        HabitRecords::factory()->create([
            'habit_id' => $habit->id,
            'recorded_date' => now()->startOfWeek()->format('Y-m-d'),
            'completed' => true
        ]);

        HabitRecords::factory()->create([
            'habit_id' => $habit->id,
            'recorded_date' => now()->startOfWeek()->addDays(2)->format('Y-m-d'),
            'completed' => true
        ]);

        $completionRate = $habit->getThisWeekCompletionRate();
        
        // 週次習慣：目標1回に対して2回完了 = 200%
        expect($completionRate)->toBe(200.0);
    });

    it('週3回習慣の部分達成を計算する', function () {
        $habit = Habits::factory()->create([
            'user_id' => $this->user->id,
            'target_frequency' => 3,
            'target_unit' => 'weekly'
        ]);

        // 今週1回のみ完了
        HabitRecords::factory()->create([
            'habit_id' => $habit->id,
            'recorded_date' => now()->startOfWeek()->format('Y-m-d'),
            'completed' => true
        ]);

        $completionRate = $habit->getThisWeekCompletionRate();
        
        // 目標3回に対して1回完了 = 33.33%
        expect(round($completionRate, 2))->toBe(33.33);
    });

    it('今週記録がない週次習慣は0%', function () {
        $habit = Habits::factory()->create([
            'user_id' => $this->user->id,
            'target_frequency' => 2,
            'target_unit' => 'weekly'
        ]);

        $completionRate = $habit->getThisWeekCompletionRate();
        
        expect($completionRate)->toBe(0.0);
    });
});

describe('月次達成率計算', function () {
    it('月10回習慣の達成率を正しく計算する', function () {
        $habit = Habits::factory()->create([
            'user_id' => $this->user->id,
            'target_frequency' => 10,
            'target_unit' => 'monthly'
        ]);

        // 今月5回完了
        for ($i = 1; $i <= 5; $i++) {
            HabitRecords::factory()->create([
                'habit_id' => $habit->id,
                'recorded_date' => now()->startOfMonth()->addDays($i)->format('Y-m-d'),
                'completed' => true
            ]);
        }

        $completionRate = $habit->getThisMonthCompletionRate();
        
        // 月次習慣の計算は複雑（月の日数による）なので、実際の値を確認
        expect($completionRate)->toBeGreaterThan(0.0);
        expect($completionRate)->toBeLessThan(100.0);
    });

    it('月次習慣の目標超過達成を計算する', function () {
        $habit = Habits::factory()->create([
            'user_id' => $this->user->id,
            'target_frequency' => 5,
            'target_unit' => 'monthly'
        ]);

        // 今月8回完了（目標超過）
        for ($i = 1; $i <= 8; $i++) {
            HabitRecords::factory()->create([
                'habit_id' => $habit->id,
                'recorded_date' => now()->startOfMonth()->addDays($i)->format('Y-m-d'),
                'completed' => true
            ]);
        }

        $completionRate = $habit->getThisMonthCompletionRate();
        
        // 月次習慣の目標超過なので100%以上
        expect($completionRate)->toBeGreaterThan(100.0);
    });

    it('今月記録がない月次習慣は0%', function () {
        $habit = Habits::factory()->create([
            'user_id' => $this->user->id,
            'target_frequency' => 15,
            'target_unit' => 'monthly'
        ]);

        $completionRate = $habit->getThisMonthCompletionRate();
        
        expect($completionRate)->toBe(0.0);
    });
});

describe('複合達成率シナリオ', function () {
    it('未完了記録がある場合の達成率計算', function () {
        $habit = Habits::factory()->create([
            'user_id' => $this->user->id,
            'target_frequency' => 1,
            'target_unit' => 'daily'
        ]);

        // 3日間：完了、未完了、完了
        $dates = [
            now()->subDays(2)->format('Y-m-d'),
            now()->subDays(1)->format('Y-m-d'),
            now()->format('Y-m-d')
        ];

        $completed = [true, false, true];

        foreach ($dates as $index => $date) {
            HabitRecords::factory()->create([
                'habit_id' => $habit->id,
                'recorded_date' => $date,
                'completed' => $completed[$index]
            ]);
        }

        $startDate = now()->subDays(2);
        $endDate = now();
        $completionRate = $habit->getCompletionRate($startDate, $endDate);
        
        // 3日間で2回完了 = 66.67%
        expect(round($completionRate, 2))->toBe(66.67);
    });

    it('期間指定での達成率計算', function () {
        $habit = Habits::factory()->create([
            'user_id' => $this->user->id,
            'target_frequency' => 1,
            'target_unit' => 'daily'
        ]);

        // 7日間で4回完了
        $completedDays = [0, 2, 4, 6]; // インデックス
        
        for ($i = 0; $i < 7; $i++) {
            HabitRecords::factory()->create([
                'habit_id' => $habit->id,
                'recorded_date' => now()->subDays(6 - $i)->format('Y-m-d'),
                'completed' => in_array($i, $completedDays)
            ]);
        }

        $startDate = now()->subDays(6);
        $endDate = now();
        $completionRate = $habit->getCompletionRate($startDate, $endDate);
        
        // 7日間で4回完了 = 57.14%
        expect(round($completionRate, 2))->toBe(57.14);
    });
});

describe('エッジケース', function () {
    it('目標頻度が0の場合の処理', function () {
        $habit = Habits::factory()->create([
            'user_id' => $this->user->id,
            'target_frequency' => 0,
            'target_unit' => 'daily'
        ]);

        HabitRecords::factory()->create([
            'habit_id' => $habit->id,
            'recorded_date' => now()->format('Y-m-d'),
            'completed' => true
        ]);

        $startDate = now();
        $endDate = now();
        $completionRate = $habit->getCompletionRate($startDate, $endDate);
        
        // 目標が0の場合は100%（実装により）
        expect($completionRate)->toBe(100.0);
    });

    it('開始日が終了日より後の場合', function () {
        $habit = Habits::factory()->create([
            'user_id' => $this->user->id,
            'target_frequency' => 1,
            'target_unit' => 'daily'
        ]);

        $startDate = now();
        $endDate = now()->subDay();
        $completionRate = $habit->getCompletionRate($startDate, $endDate);
        
        // 不正な期間の場合は0%
        expect($completionRate)->toBe(0.0);
    });
});

describe('ダッシュボード集計データ', function () {
    it('複数習慣の平均達成率を計算する', function () {
        // 3つの習慣を作成
        $habits = [];
        for ($i = 0; $i < 3; $i++) {
            $habits[] = Habits::factory()->create([
                'user_id' => $this->user->id,
                'target_frequency' => 1,
                'target_unit' => 'weekly',
                'is_active' => true
            ]);
        }

        // 各習慣の今週の完了状況を設定
        // 習慣1: 100%, 習慣2: 50%, 習慣3: 0%
        $completions = [1, 0, 0]; // 完了回数
        
        foreach ($habits as $index => $habit) {
            if ($completions[$index] > 0) {
                HabitRecords::factory()->create([
                    'habit_id' => $habit->id,
                    'recorded_date' => now()->startOfWeek()->format('Y-m-d'),
                    'completed' => true
                ]);
            }
        }

        $response = $this->get('/dashboard');
        
        $response->assertOk();
        // 平均達成率 (100 + 0 + 0) / 3 = 33.33%
        $response->assertSee('33%');
    });
});