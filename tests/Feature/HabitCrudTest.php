<?php

use App\Models\User;
use App\Models\Habits;
use App\Models\Categories;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('習慣作成', function () {
    it('認証済みユーザーは習慣を作成できる', function () {
        $category = Categories::factory()->create(['user_id' => $this->user->id]);
        
        $response = $this->post('/habits', [
            'name' => 'テスト習慣',
            'description' => 'テスト用の習慣です',
            'category_id' => $category->id,
            'target_frequency' => 1,
            'target_unit' => 'daily'
        ]);

        $response->assertRedirect('/habits');
        $this->assertDatabaseHas('habits', [
            'user_id' => $this->user->id,
            'name' => 'テスト習慣',
            'category_id' => $category->id,
            'target_frequency' => 1,
            'target_unit' => 'daily'
        ]);
    });

    it('必須項目が不足している場合は作成できない', function () {
        $response = $this->post('/habits', [
            'description' => 'テスト用の習慣です'
            // nameが不足
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('habits', 0);
    });

    it('他のユーザーのカテゴリは使用できない', function () {
        $otherUser = User::factory()->create();
        $otherCategory = Categories::factory()->create(['user_id' => $otherUser->id]);
        
        $response = $this->post('/habits', [
            'name' => 'テスト習慣',
            'category_id' => $otherCategory->id,
            'target_frequency' => 1,
            'target_unit' => 'daily'
        ]);

        $response->assertSessionHasErrors('category_id');
    });
});

describe('習慣表示', function () {
    it('自分の習慣一覧を表示できる', function () {
        $habit = Habits::factory()->create(['user_id' => $this->user->id]);
        
        $response = $this->get('/habits');
        
        $response->assertOk();
        $response->assertSee($habit->name);
    });

    it('他のユーザーの習慣は表示されない', function () {
        $otherUser = User::factory()->create();
        $otherHabit = Habits::factory()->create(['user_id' => $otherUser->id]);
        $myHabit = Habits::factory()->create(['user_id' => $this->user->id]);
        
        $response = $this->get('/habits');
        
        $response->assertOk();
        $response->assertSee($myHabit->name);
        $response->assertDontSee($otherHabit->name);
    });

    it('習慣詳細を表示できる', function () {
        $habit = Habits::factory()->create(['user_id' => $this->user->id]);
        
        $response = $this->get("/habits/{$habit->id}");
        
        $response->assertOk();
        $response->assertSee($habit->name);
    });
});

describe('習慣更新', function () {
    it('自分の習慣を更新できる', function () {
        $habit = Habits::factory()->create(['user_id' => $this->user->id]);
        
        $response = $this->put("/habits/{$habit->id}", [
            'name' => '更新された習慣',
            'description' => '更新されました',
            'target_frequency' => 2,
            'target_unit' => 'weekly',
            'is_active' => true
        ]);

        $response->assertRedirect('/habits');
        $this->assertDatabaseHas('habits', [
            'id' => $habit->id,
            'name' => '更新された習慣',
            'target_frequency' => 2,
            'target_unit' => 'weekly'
        ]);
    });

    it('他のユーザーの習慣は更新できない', function () {
        $otherUser = User::factory()->create();
        $otherHabit = Habits::factory()->create(['user_id' => $otherUser->id]);
        
        $response = $this->put("/habits/{$otherHabit->id}", [
            'name' => '更新された習慣'
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('habits', [
            'id' => $otherHabit->id,
            'name' => '更新された習慣'
        ]);
    });
});

describe('習慣削除', function () {
    it('自分の習慣を削除できる', function () {
        $habit = Habits::factory()->create(['user_id' => $this->user->id]);
        
        $response = $this->delete("/habits/{$habit->id}");
        
        $response->assertRedirect('/habits');
        $this->assertDatabaseMissing('habits', ['id' => $habit->id]);
    });

    it('他のユーザーの習慣は削除できない', function () {
        $otherUser = User::factory()->create();
        $otherHabit = Habits::factory()->create(['user_id' => $otherUser->id]);
        
        $response = $this->delete("/habits/{$otherHabit->id}");
        
        $response->assertStatus(403);
        $this->assertDatabaseHas('habits', ['id' => $otherHabit->id]);
    });
});

describe('認証チェック', function () {
    it('未認証ユーザーは習慣にアクセスできない', function () {
        auth()->logout();
        
        $response = $this->get('/habits');
        
        $response->assertRedirect('/login');
    });
});