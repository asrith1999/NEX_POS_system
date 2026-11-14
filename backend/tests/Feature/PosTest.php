<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PosTest extends TestCase
{
    use RefreshDatabase;

    private function cashier(): User
    {
        return User::factory()->create(['password' => Hash::make('test-password')]);
    }

    public function test_api_requires_authentication(): void
    {
        $this->getJson('/api/products')->assertUnauthorized();
        $this->postJson('/api/orders', ['items' => []])->assertUnauthorized();
    }

    public function test_login_persists_a_session_and_logout_revokes_it(): void
    {
        $user = $this->cashier();
        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'wrong'])->assertUnauthorized();
        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'test-password'])->assertOk();
        $this->getJson('/api/me')->assertOk()->assertJsonPath('id', $user->id);
        $this->postJson('/api/logout')->assertOk();
        $this->assertGuest();
        $this->getJson('/api/me')->assertUnauthorized();
    }

    public function test_checkout_uses_server_prices_and_updates_stock(): void
    {
        $product = Product::create(['name' => 'Mouse', 'price' => '25.99', 'stock' => 3]);
        $this->actingAs($this->cashier())->postJson('/api/orders', ['items' => [
            ['product_id' => $product->id, 'quantity' => 2, 'price' => 0.01],
        ]])->assertOk();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 1]);
        $this->assertEquals('51.98', Order::first()->total);
        $this->assertDatabaseHas('order_items', ['product_id' => $product->id, 'quantity' => 2, 'price' => 25.99]);
    }

    public function test_insufficient_stock_rolls_back_the_whole_checkout(): void
    {
        $first = Product::create(['name' => 'Mouse', 'price' => 25, 'stock' => 3]);
        $second = Product::create(['name' => 'Keyboard', 'price' => 50, 'stock' => 0]);
        $this->actingAs($this->cashier())->postJson('/api/orders', ['items' => [
            ['product_id' => $first->id, 'quantity' => 1],
            ['product_id' => $second->id, 'quantity' => 1],
        ]])->assertStatus(400);
        $this->assertDatabaseHas('products', ['id' => $first->id, 'stock' => 3]);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_empty_carts_and_duplicate_products_are_rejected(): void
    {
        $product = Product::create(['name' => 'Mouse', 'price' => 25, 'stock' => 3]);
        $this->actingAs($this->cashier())->postJson('/api/orders', ['items' => []])->assertUnprocessable();
        $this->postJson('/api/orders', ['items' => [
            ['product_id' => $product->id, 'quantity' => 1],
            ['product_id' => $product->id, 'quantity' => 1],
        ]])->assertUnprocessable();
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_repeat_seeding_preserves_existing_users_and_stock(): void
    {
        $user = User::factory()->create(['email' => 'admin@gmail.com']);
        $hash = $user->password;
        $product = Product::create(['name' => 'Wireless Mouse', 'price' => 25.99, 'stock' => 7]);
        $this->seed();
        $this->seed();
        $this->assertEquals($hash, $user->fresh()->password);
        $this->assertEquals(7, $product->fresh()->stock);
        $this->assertDatabaseCount('products', 8);
    }
}
