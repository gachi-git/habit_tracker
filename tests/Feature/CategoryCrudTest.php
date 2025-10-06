<?php

use App\Models\User;
use App\Models\Categories;
use App\Models\Habits;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('カテゴリ作成', function () {
    it('認証済みユーザーはカテゴリを作成できる', function () {
        $response = $this->post('/categories', [
            'name' => 'テストカテゴリ',
            'color' => '#FF5733'
        ]);

        $response->assertRedirect('/categories');
        $this->assertDatabaseHas('categories', [
            'user_id' => $this->user->id,
            'name' => 'テストカテゴリ',
            'color' => '#FF5733'
        ]);
    });

    it('必須項目が不足している場合は作成できない', function () {
        $response = $this->post('/categories', [
            'color' => '#FF5733'
            // nameが不足
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('categories', 0);
    });

    it('無効な色形式は受け付けない', function () {
        $response = $this->post('/categories', [
            'name' => 'テストカテゴリ',
            'color' => 'invalid-color'
        ]);

        $response->assertSessionHasErrors('color');
        $this->assertDatabaseCount('categories', 0);
    });
});

describe('カテゴリ表示', function () {
    it('自分のカテゴリ一覧を表示できる', function () {
        $category = Categories::factory()->create(['user_id' => $this->user->id]);
        
        $response = $this->get('/categories');
        
        $response->assertOk();
        $response->assertSee($category->name);
    });

    it('他のユーザーのカテゴリは表示されない', function () {
        $otherUser = User::factory()->create();
        $otherCategory = Categories::factory()->create([
            'user_id' => $otherUser->id,
            'name' => 'OtherUserUniqueCategory'
        ]);
        $myCategory = Categories::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'MyUniqueCategory'
        ]);
        
        $response = $this->get('/categories');
        
        $response->assertOk();
        $response->assertSee('MyUniqueCategory');
        $response->assertDontSee('OtherUserUniqueCategory');
    });

    it('カテゴリ詳細を表示できる', function () {
        $category = Categories::factory()->create(['user_id' => $this->user->id]);
        
        $response = $this->get("/categories/{$category->id}");
        
        $response->assertOk();
        $response->assertSee($category->name);
    });

    it('他のユーザーのカテゴリ詳細はアクセスできない', function () {
        $otherUser = User::factory()->create();
        $otherCategory = Categories::factory()->create(['user_id' => $otherUser->id]);
        
        $response = $this->get("/categories/{$otherCategory->id}");
        
        $response->assertStatus(403);
    });
});

describe('カテゴリ更新', function () {
    it('自分のカテゴリを更新できる', function () {
        $category = Categories::factory()->create(['user_id' => $this->user->id]);
        
        $response = $this->put("/categories/{$category->id}", [
            'name' => '更新されたカテゴリ',
            'color' => '#00FF00'
        ]);

        $response->assertRedirect('/categories');
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => '更新されたカテゴリ',
            'color' => '#00FF00'
        ]);
    });

    it('他のユーザーのカテゴリは更新できない', function () {
        $otherUser = User::factory()->create();
        $otherCategory = Categories::factory()->create(['user_id' => $otherUser->id]);
        
        $response = $this->put("/categories/{$otherCategory->id}", [
            'name' => '更新されたカテゴリ',
            'color' => '#00FF00'
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('categories', [
            'id' => $otherCategory->id,
            'name' => '更新されたカテゴリ'
        ]);
    });
});

describe('カテゴリ削除', function () {
    it('習慣が紐づいていないカテゴリは削除できる', function () {
        $category = Categories::factory()->create(['user_id' => $this->user->id]);
        
        $response = $this->delete("/categories/{$category->id}");
        
        $response->assertRedirect('/categories');
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    });

    it('習慣が紐づいているカテゴリは削除できない', function () {
        $category = Categories::factory()->create(['user_id' => $this->user->id]);
        $habit = Habits::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id
        ]);
        
        $response = $this->delete("/categories/{$category->id}");
        
        // 習慣があるため削除されない
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    });

    it('他のユーザーのカテゴリは削除できない', function () {
        $otherUser = User::factory()->create();
        $otherCategory = Categories::factory()->create(['user_id' => $otherUser->id]);
        
        $response = $this->delete("/categories/{$otherCategory->id}");
        
        $response->assertStatus(403);
        $this->assertDatabaseHas('categories', ['id' => $otherCategory->id]);
    });
});

describe('カテゴリと習慣の連携', function () {
    it('カテゴリ詳細で所属習慣数を表示', function () {
        $category = Categories::factory()->create(['user_id' => $this->user->id]);
        $habits = Habits::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id
        ]);
        
        $response = $this->get("/categories/{$category->id}");
        
        $response->assertOk();
        foreach ($habits as $habit) {
            $response->assertSee($habit->name);
        }
    });

    it('カテゴリ一覧で習慣数をカウント表示', function () {
        $category = Categories::factory()->create(['user_id' => $this->user->id]);
        Habits::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id
        ]);
        
        $response = $this->get('/categories');
        
        $response->assertOk();
        $response->assertSee('2個の習慣');
    });
});

describe('認証チェック', function () {
    it('未認証ユーザーはカテゴリにアクセスできない', function () {
        auth()->logout();
        
        $response = $this->get('/categories');
        
        $response->assertRedirect('/login');
    });
});