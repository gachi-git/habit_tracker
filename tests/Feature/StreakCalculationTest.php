<?php

use App\Models\User;
use App\Models\Habits;
use App\Models\HabitRecords;
use Carbon\Carbon;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('日次習慣のストリーク計算', function () {
    it('連続完了日数を正しく計算する', function () {
        $habit = Habits::factory()->create([
            'user_id' => $this->user->id,
            'target_frequency' => 1,
            'target_unit' => 'daily'
        ]);

        // 3日連続で完了
        for ($i = 0; $i < 3; $i++) {
            HabitRecords::factory()->create([
                'habit_id' => $habit->id,
                'recorded_date' => now()->subDays($i)->format('Y-m-d'),
                'completed' => true
            ]);
        }

        $currentStreak = $habit->getCurrentStreak();
        expect($currentStreak)->toBe(3);
    });

    it('今日完了していない場合は昨日までのストリーク', function () {
        $habit = Habits::factory()->create([
            'user_id' => $this->user->id,
            'target_frequency' => 1,
            'target_unit' => 'daily'
        ]);

        // 昨日と一昨日完了、今日は未記録
        HabitRecords::factory()->create([
            'habit_id' => $habit->id,
            'recorded_date' => now()->subDay()->format('Y-m-d'),
            'completed' => true
        ]);

        HabitRecords::factory()->create([
            'habit_id' => $habit->id,
            'recorded_date' => now()->subDays(2)->format('Y-m-d'),
            'completed' => true
        ]);

        $currentStreak = $habit->getCurrentStreak();
        expect($currentStreak)->toBe(2);
    });

    it('記録がない場合はストリーク0', function () {
        $habit = Habits::factory()->create([
            'user_id' => $this->user->id,
            'target_frequency' => 1,
            'target_unit' => 'daily'
        ]);

        $currentStreak = $habit->getCurrentStreak();
        expect($currentStreak)->toBe(0);
    });

    it('未完了記録は連続を途切れさせる', function () {
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

        // 昨日未完了で連続が途切れる
        HabitRecords::factory()->create([
            'habit_id' => $habit->id,
            'recorded_date' => now()->subDay()->format('Y-m-d'),
            'completed' => false
        ]);

        // 一昨日完了
        HabitRecords::factory()->create([
            'habit_id' => $habit->id,
            'recorded_date' => now()->subDays(2)->format('Y-m-d'),
            'completed' => true
        ]);

        $currentStreak = $habit->getCurrentStreak();
        expect($currentStreak)->toBe(1); // 今日のみ
    });
});

describe('週次習慣のストリーク計算', function () {
    it('連続週数を正しく計算する', function () {
        $habit = Habits::factory()->create([
            'user_id' => $this->user->id,
            'target_frequency' => 1,
            'target_unit' => 'weekly'
        ]);

        // 3週連続で完了
        HabitRecords::factory()->create([
            'habit_id' => $habit->id,
            'recorded_date' => now()->startOfWeek()->format('Y-m-d'),
            'completed' => true
        ]);

        HabitRecords::factory()->create([
            'habit_id' => $habit->id,
            'recorded_date' => now()->subWeek()->startOfWeek()->format('Y-m-d'),
            'completed' => true
        ]);

        HabitRecords::factory()->create([
            'habit_id' => $habit->id,
            'recorded_date' => now()->subWeeks(2)->startOfWeek()->format('Y-m-d'),
            'completed' => true
        ]);

        $currentStreak = $habit->getCurrentStreak();
        expect($currentStreak)->toBe(3);
    });

    it('今週未完了の場合は先週までのストリーク', function () {
        $habit = Habits::factory()->create([
            'user_id' => $this->user->id,
            'target_frequency' => 1,
            'target_unit' => 'weekly'
        ]);

        // 先週と先々週完了、今週は未記録
        HabitRecords::factory()->create([
            'habit_id' => $habit->id,
            'recorded_date' => now()->subWeek()->startOfWeek()->format('Y-m-d'),
            'completed' => true
        ]);

        HabitRecords::factory()->create([
            'habit_id' => $habit->id,
            'recorded_date' => now()->subWeeks(2)->startOfWeek()->format('Y-m-d'),
            'completed' => true
        ]);

        $currentStreak = $habit->getCurrentStreak();
        expect($currentStreak)->toBe(2);
    });

    it('週が飛んだ場合はストリークリセット', function () {
        $habit = Habits::factory()->create([
            'user_id' => $this->user->id,
            'target_frequency' => 1,
            'target_unit' => 'weekly'
        ]);

        // 今週と先週完了
        HabitRecords::factory()->create([
            'habit_id' => $habit->id,
            'recorded_date' => now()->startOfWeek()->format('Y-m-d'),
            'completed' => true
        ]);

        HabitRecords::factory()->create([
            'habit_id' => $habit->id,
            'recorded_date' => now()->subWeek()->startOfWeek()->format('Y-m-d'),
            'completed' => true
        ]);

        // 先々週は記録なし（途切れ）

        // 3週前は完了
        HabitRecords::factory()->create([
            'habit_id' => $habit->id,
            'recorded_date' => now()->subWeeks(3)->startOfWeek()->format('Y-m-d'),
            'completed' => true
        ]);

        $currentStreak = $habit->getCurrentStreak();
        expect($currentStreak)->toBe(2); // 今週と先週のみ
    });
});

describe('月次習慣のストリーク計算', function () {
    it('連続月数を正しく計算する', function () {
        $habit = Habits::factory()->create([
            'user_id' => $this->user->id,
            'target_frequency' => 5,
            'target_unit' => 'monthly'
        ]);

        // 3ヶ月連続で完了
        HabitRecords::factory()->create([
            'habit_id' => $habit->id,
            'recorded_date' => now()->startOfMonth()->format('Y-m-d'),
            'completed' => true
        ]);

        HabitRecords::factory()->create([
            'habit_id' => $habit->id,
            'recorded_date' => now()->subMonth()->startOfMonth()->format('Y-m-d'),
            'completed' => true
        ]);

        HabitRecords::factory()->create([
            'habit_id' => $habit->id,
            'recorded_date' => now()->subMonths(2)->startOfMonth()->format('Y-m-d'),
            'completed' => true
        ]);

        $currentStreak = $habit->getCurrentStreak();
        expect($currentStreak)->toBe(3);
    });

    it('今月未完了の場合は先月までのストリーク', function () {
        $habit = Habits::factory()->create([
            'user_id' => $this->user->id,
            'target_frequency' => 5,
            'target_unit' => 'monthly'
        ]);

        // 先月と先々月完了、今月は未記録
        HabitRecords::factory()->create([
            'habit_id' => $habit->id,
            'recorded_date' => now()->subMonth()->startOfMonth()->format('Y-m-d'),
            'completed' => true
        ]);

        HabitRecords::factory()->create([
            'habit_id' => $habit->id,
            'recorded_date' => now()->subMonths(2)->startOfMonth()->format('Y-m-d'),
            'completed' => true
        ]);

        $currentStreak = $habit->getCurrentStreak();
        expect($currentStreak)->toBe(2);
    });

    it('月が飛んだ場合はストリークリセット', function () {
        $habit = Habits::factory()->create([
            'user_id' => $this->user->id,
            'target_frequency' => 5,
            'target_unit' => 'monthly'
        ]);

        // 今月と先月完了
        HabitRecords::factory()->create([
            'habit_id' => $habit->id,
            'recorded_date' => now()->startOfMonth()->format('Y-m-d'),
            'completed' => true
        ]);

        HabitRecords::factory()->create([
            'habit_id' => $habit->id,
            'recorded_date' => now()->subMonth()->startOfMonth()->format('Y-m-d'),
            'completed' => true
        ]);

        // 先々月は記録なし（途切れ）

        // 3ヶ月前は完了
        HabitRecords::factory()->create([
            'habit_id' => $habit->id,
            'recorded_date' => now()->subMonths(3)->startOfMonth()->format('Y-m-d'),
            'completed' => true
        ]);

        $currentStreak = $habit->getCurrentStreak();
        expect($currentStreak)->toBe(2); // 今月と先月のみ
    });
});

describe('最長ストリーク計算', function () {
    it('日次習慣の最長ストリークを計算する', function () {
        $habit = Habits::factory()->create([
            'user_id' => $this->user->id,
            'target_frequency' => 1,
            'target_unit' => 'daily'
        ]);

        // 5日連続 → 2日空く → 3日連続
        $dates = [];
        
        // 最初の5日連続
        for ($i = 10; $i >= 6; $i--) {
            $dates[] = now()->subDays($i)->format('Y-m-d');
        }
        
        // 2日空く（subDays(5), subDays(4)）
        
        // 3日連続  
        for ($i = 3; $i >= 1; $i--) {
            $dates[] = now()->subDays($i)->format('Y-m-d');
        }

        foreach ($dates as $date) {
            HabitRecords::factory()->create([
                'habit_id' => $habit->id,
                'recorded_date' => $date,
                'completed' => true
            ]);
        }

        $longestStreak = $habit->getLongestStreak();
        expect($longestStreak)->toBe(5); // 最初の5日連続が最長
    });

    it('週次習慣の最長ストリークを計算する', function () {
        $habit = Habits::factory()->create([
            'user_id' => $this->user->id,
            'target_frequency' => 1,
            'target_unit' => 'weekly'
        ]);

        // 4週連続 → 1週空く → 2週連続
        HabitRecords::factory()->create([
            'habit_id' => $habit->id,
            'recorded_date' => now()->subWeeks(7)->startOfWeek()->format('Y-m-d'),
            'completed' => true
        ]);
        HabitRecords::factory()->create([
            'habit_id' => $habit->id,
            'recorded_date' => now()->subWeeks(6)->startOfWeek()->format('Y-m-d'),
            'completed' => true
        ]);
        HabitRecords::factory()->create([
            'habit_id' => $habit->id,
            'recorded_date' => now()->subWeeks(5)->startOfWeek()->format('Y-m-d'),
            'completed' => true
        ]);
        HabitRecords::factory()->create([
            'habit_id' => $habit->id,
            'recorded_date' => now()->subWeeks(4)->startOfWeek()->format('Y-m-d'),
            'completed' => true
        ]);
        // subWeeks(3) は空く
        HabitRecords::factory()->create([
            'habit_id' => $habit->id,
            'recorded_date' => now()->subWeeks(2)->startOfWeek()->format('Y-m-d'),
            'completed' => true
        ]);
        HabitRecords::factory()->create([
            'habit_id' => $habit->id,
            'recorded_date' => now()->subWeeks(1)->startOfWeek()->format('Y-m-d'),
            'completed' => true
        ]);

        $longestStreak = $habit->getLongestStreak();
        expect($longestStreak)->toBe(4); // 最初の4週連続が最長
    });

    it('記録がない場合の最長ストリークは0', function () {
        $habit = Habits::factory()->create([
            'user_id' => $this->user->id,
            'target_frequency' => 1,
            'target_unit' => 'daily'
        ]);

        $longestStreak = $habit->getLongestStreak();
        expect($longestStreak)->toBe(0);
    });
});

describe('ストリーク計算のエッジケース', function () {
    it('同じ期間に複数記録がある場合', function () {
        $habit = Habits::factory()->create([
            'user_id' => $this->user->id,
            'target_frequency' => 1,
            'target_unit' => 'daily'
        ]);

        // 今日の記録
        HabitRecords::factory()->create([
            'habit_id' => $habit->id,
            'recorded_date' => now()->format('Y-m-d'),
            'completed' => true
        ]);

        // 昨日の記録
        HabitRecords::factory()->create([
            'habit_id' => $habit->id,
            'recorded_date' => now()->subDay()->format('Y-m-d'),
            'completed' => true
        ]);

        // 同じ日に重複記録があっても重複制約で作成されないが、ストリーク計算は正常に動作する
        $currentStreak = $habit->getCurrentStreak();
        expect($currentStreak)->toBe(2);
    });

    it('未完了記録は無視される', function () {
        $habit = Habits::factory()->create([
            'user_id' => $this->user->id,
            'target_frequency' => 1,
            'target_unit' => 'daily'
        ]);

        // 完了記録の間に未完了記録があるケース
        HabitRecords::factory()->create([
            'habit_id' => $habit->id,
            'recorded_date' => now()->format('Y-m-d'),
            'completed' => true
        ]);

        HabitRecords::factory()->create([
            'habit_id' => $habit->id,
            'recorded_date' => now()->subDay()->format('Y-m-d'),
            'completed' => false // 未完了
        ]);

        HabitRecords::factory()->create([
            'habit_id' => $habit->id,
            'recorded_date' => now()->subDays(2)->format('Y-m-d'),
            'completed' => true
        ]);

        $currentStreak = $habit->getCurrentStreak();
        expect($currentStreak)->toBe(1); // 今日のみ（昨日の未完了で途切れる）
    });
});