<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    private $category1;

    private $category2;

    private $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->category1 = Category::factory()->create(['name' => 'Electronics']);
        $this->category2 = Category::factory()->create(['name' => 'Books']);

        Product::factory()->create([
            'name' => 'iPhone',
            'price' => 1000,
            'stock' => 50,
            'category_id' => $this->category1->id,
        ]);

        Product::factory()->create([
            'name' => 'Samsung Galaxy',
            'price' => 900,
            'stock' => 30,
            'category_id' => $this->category1->id,
        ]);

        Product::factory()->create([
            'name' => 'Laravel For Dummies',
            'price' => 20,
            'stock' => 100,
            'category_id' => $this->category2->id,
        ]);
    }

    public function test_can_list_products(): void
    {
        $response = $this->actingAs($this->user)->get('/api/products');

        $response->assertStatus(200)
            ->assertExactJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'price',
                        'stock',
                        'created_at',
                        'category' => [
                            'id',
                            'name',
                        ],
                    ],
                ],
                'links' => ['first', 'last', 'prev', 'next'],
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'links' => [
                        '*' => [
                            'url',
                            'label',
                            'active',
                        ],
                    ],
                    'path',
                    'per_page',
                    'to',
                    'total',
                ],
            ]);
    }

    public function test_can_filter_by_product_name()
    {
        $response = $this->actingAs($this->user)->json('GET', '/api/products', [
            'product_name' => 'iPhone',
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'name' => 'iPhone',
                'price' => 1000,
                'stock' => 50,
            ]);
    }

    public function test_can_filter_by_price_range()
    {
        $response = $this->actingAs($this->user)->json('GET', '/api/products', [
            'price_from' => 100,
            'price_to' => 1000,
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['name' => 'iPhone', 'stock' => 50, 'price' => 1000])
            ->assertJsonFragment(['name' => 'Samsung Galaxy', 'stock' => 30, 'price' => 900]);
    }

    public function test_can_filter_by_stock_range()
    {
        $response = $this->actingAs($this->user)->json('GET', '/api/products', [
            'stock_from' => 25,
            'stock_to' => 45,
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['name' => 'Samsung Galaxy', 'stock' => 30, 'price' => 900]);
    }

    public function test_can_filter_by_category_id()
    {
        $response = $this->actingAs($this->user)->json('GET', '/api/products', [
            'category_id' => $this->category2->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['name' => 'Laravel For Dummies', 'price' => 20, 'stock' => 100]);
    }
}
