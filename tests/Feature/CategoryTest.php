<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_create_category_and_slug_is_generated(): void
    {
        $this->actingAs($this->admin())
            ->post('/category-list', ['name' => 'Cold Drinks', 'slug' => '', 'is_active' => 1])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('categories', ['name' => 'Cold Drinks', 'slug' => 'cold-drinks']);
    }

    public function test_duplicate_name_is_rejected(): void
    {
        Category::create(['name' => 'Food', 'slug' => 'food', 'is_active' => true]);

        $this->actingAs($this->admin())
            ->post('/category-list', ['name' => 'Food', 'slug' => 'food-2', 'is_active' => 1])
            ->assertSessionHasErrors('name');
    }

    public function test_admin_can_update_category(): void
    {
        $category = Category::create(['name' => 'Food', 'slug' => 'food', 'is_active' => true]);

        $this->actingAs($this->admin())
            ->put("/category-list/{$category->id}", ['name' => 'Snacks', 'slug' => 'snacks', 'is_active' => 0])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Snacks', 'is_active' => false]);
    }

    public function test_admin_can_delete_category(): void
    {
        $category = Category::create(['name' => 'Food', 'slug' => 'food', 'is_active' => true]);

        $this->actingAs($this->admin())->delete("/category-list/{$category->id}");

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_cashier_cannot_open_categories(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $this->actingAs($cashier)->get('/category-list')->assertForbidden();
    }
}