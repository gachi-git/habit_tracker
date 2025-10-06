<?php

use App\Models\User;
use App\Models\Habits;
use App\Models\HabitRecords;
use App\Models\Categories;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    $this->habit = Habits::factory()->create(['user_id' => $this->user->id]);
});

describe('習慣記録作成', function () {
    it('認証済みユーザーは自分の習慣を記録できる', function () {
        $response = $this->post("/habits/{$this->habit->id}/record", [
            'recorded_date' => '2025-10-05',
            'completed' => 1,
            'duration_minutes' => 30,
            'note' => 'テスト記録'
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('habit_records', [
            'habit_id' => $this->habit->id,
            'recorded_date' => '2025-10-05',
            'completed' => true,
            'duration_minutes' => 30,
            'note' => 'テスト記録'
        ]);
    });

    it('最小限の情報で記録を作成できる', function () {
        $response = $this->post("/habits/{$this->habit->id}/record", [
            'recorded_date' => '2025-10-05',
            'completed' => 1
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('habit_records', [
            'habit_id' => $this->habit->id,
            'recorded_date' => '2025-10-05',
            'completed' => true
        ]);
    });

    it('未完了でも記録を作成できる', function () {
        $response = $this->post("/habits/{$this->habit->id}/record", [
            'recorded_date' => '2025-10-05',
            // completedを送信しない = 未完了
            'note' => '今日はできなかった'
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('habit_records', [
            'habit_id' => $this->habit->id,
            'recorded_date' => '2025-10-05',
            'completed' => false,
            'note' => '今日はできなかった'
        ]);
    });

    it('同じ日の記録を重複作成できない', function () {
        // 最初の記録
        $this->post("/habits/{$this->habit->id}/record", [
            'recorded_date' => '2025-10-05',
            'completed' => 1
        ]);

        // 同じ日の記録を再度作成
        $response = $this->post("/habits/{$this->habit->id}/record", [
            'recorded_date' => '2025-10-05',
            'completed' => 1
        ]);

        // 重複作成は失敗するべき（実装によって動作を確認）
        $this->assertDatabaseCount('habit_records', 1);
    });

    it('他のユーザーの習慣には記録できない', function () {
        $otherUser = User::factory()->create();
        $otherHabit = Habits::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->post("/habits/{$otherHabit->id}/record", [
            'recorded_date' => '2025-10-05',
            'completed' => 1
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseCount('habit_records', 0);
    });

    it('必須項目が不足している場合は作成できない', function () {
        $response = $this->post("/habits/{$this->habit->id}/record", [
            'completed' => 1
            // recorded_dateが不足
        ]);

        $response->assertSessionHasErrors('recorded_date');
        $this->assertDatabaseCount('habit_records', 0);
    });
});

describe('習慣記録一覧表示', function () {
    it('自分の習慣の記録一覧を表示できる', function () {
        $records = HabitRecords::factory()->count(3)->create([
            'habit_id' => $this->habit->id
        ]);

        $response = $this->get("/habits/{$this->habit->id}/records");

        $response->assertOk();
        foreach ($records as $record) {
            $response->assertSee($record->recorded_date->format('Y年m月d日'));
        }
    });

    it('他のユーザーの習慣の記録一覧はアクセスできない', function () {
        $otherUser = User::factory()->create();
        $otherHabit = Habits::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->get("/habits/{$otherHabit->id}/records");

        $response->assertStatus(403);
    });

    it('記録一覧に完了・未完了の表示が含まれる', function () {
        HabitRecords::factory()->create([
            'habit_id' => $this->habit->id,
            'completed' => true,
            'note' => '完了した記録'
        ]);
        
        HabitRecords::factory()->create([
            'habit_id' => $this->habit->id,
            'completed' => false,
            'note' => '未完了の記録'
        ]);

        $response = $this->get("/habits/{$this->habit->id}/records");

        $response->assertOk();
        $response->assertSee('完了した記録');
        $response->assertSee('未完了の記録');
    });
});

describe('習慣記録削除', function () {
    it('自分の記録を削除できる', function () {
        $record = HabitRecords::factory()->create([
            'habit_id' => $this->habit->id
        ]);

        $response = $this->delete("/habit-records/{$record->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('habit_records', ['id' => $record->id]);
    });

    it('他のユーザーの記録は削除できない', function () {
        $otherUser = User::factory()->create();
        $otherHabit = Habits::factory()->create(['user_id' => $otherUser->id]);
        $otherRecord = HabitRecords::factory()->create([
            'habit_id' => $otherHabit->id
        ]);

        $response = $this->delete("/habit-records/{$otherRecord->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('habit_records', ['id' => $otherRecord->id]);
    });
});

describe('ワンクリック記録機能', function () {
    it('今日の日付で完了記録を作成できる', function () {
        $today = now()->format('Y-m-d');
        
        $response = $this->post("/habits/{$this->habit->id}/record", [
            'recorded_date' => $today,
            'completed' => 1
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('habit_records', [
            'habit_id' => $this->habit->id,
            'recorded_date' => $today,
            'completed' => true
        ]);
    });
});

describe('記録データの整合性', function () {
    it('記録は正しい習慣に紐づく', function () {
        $anotherHabit = Habits::factory()->create(['user_id' => $this->user->id]);
        
        $record = HabitRecords::factory()->create([
            'habit_id' => $this->habit->id
        ]);

        expect($record->habit_id)->toBe($this->habit->id);
        expect($record->habit_id)->not->toBe($anotherHabit->id);
    });

    it('記録の日付フォーマットが正しい', function () {
        $response = $this->post("/habits/{$this->habit->id}/record", [
            'recorded_date' => '2025-10-05',
            'completed' => 1
        ]);

        $record = HabitRecords::where('habit_id', $this->habit->id)->first();
        expect($record->recorded_date->format('Y-m-d'))->toBe('2025-10-05');
    });

    it('時間の記録が正しく保存される', function () {
        $this->post("/habits/{$this->habit->id}/record", [
            'recorded_date' => '2025-10-05',
            'completed' => 1,
            'duration_minutes' => 45
        ]);

        $record = HabitRecords::where('habit_id', $this->habit->id)->first();
        expect($record->duration_minutes)->toBe(45);
    });
});

describe('認証チェック', function () {
    it('未認証ユーザーは記録を作成できない', function () {
        auth()->logout();

        $response = $this->post("/habits/{$this->habit->id}/record", [
            'recorded_date' => '2025-10-05',
            'completed' => 1
        ]);

        $response->assertRedirect('/login');
        $this->assertDatabaseCount('habit_records', 0);
    });

    it('未認証ユーザーは記録一覧を閲覧できない', function () {
        auth()->logout();

        $response = $this->get("/habits/{$this->habit->id}/records");

        $response->assertRedirect('/login');
    });
});