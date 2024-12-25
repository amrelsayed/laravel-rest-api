<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_it_requires_products_array()
    {
        $response = $this->postJson('/api/orders', []);

        $response->assertStatus(422)->assertJsonValidationErrors(['products']);
    }

    public function test_it_requires_valid_product_id_and_quantity()
    {
        $response = $this->postJson('/api/orders', [
            'products' => [
                ['id' => 999, 'quantity' => 1],
                ['id' => 1, 'quantity' => 0],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['products.0.id', 'products.1.quantity']);
    }

    public function test_it_creates_order_successfully_with_valid_data()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 100,
            'stock' => 10,
        ]);

        $response = $this->postJson('/api/orders', [
            'products' => [
                ['id' => $product->id, 'quantity' => 2],
            ],
        ]);

        $response->assertStatus(200)
            ->assertExactJsonStructure([
                'message',
                'data' => [
                    'id',
                    'total_price',
                    'status',
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'created_at',
                    ],
                    'products' => [
                        ['id', 'name', 'price', 'stock', 'created_at'],
                    ],
                ],
            ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => auth()->id(),
            'total_price' => 200,
            'status' => Order::STATUS['Placed'],
        ]);

        $product->refresh();

        $this->assertEquals(8, $product->stock);
    }

    public function test_it_does_not_create_order_on_exception()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category,
            'price' => 100,
            'stock' => 10,
        ]);

        $response = $this->postJson('/api/orders', [
            'products' => [
                ['id' => $product->id, 'quantity' => 11],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Order failed']);

        $this->assertDatabaseMissing('orders', [
            'user_id' => auth()->id(),
        ]);

        $product->refresh();

        $this->assertEquals(10, $product->stock);
    }
}
