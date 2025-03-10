<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $admin;

    protected function setUp():void
    {
        parent::setUp();
        $this->user = $this->createUser();
        $this->admin = $this->createUser(isAdmin: true);

    }
    public function test_product_page_contain_empty_table()
    {

        $response = $this->actingAs($this->user)->get('/products/all');
        $response->assertStatus(200);
        $response->assertSee('No product found');

    }

    public function test_product_page_contain_non_empty_table()
    {

        $product = Product::create([
            'name' => 'Product Name HAAH',
            'price' => 300
        ]);
        $response = $this->actingAs($this->user)->get('/products/all');
        $response->assertStatus(200);
        $response->assertDontSee('No product found');
        $response->assertSee('Product Name HAAH');
        $response->assertViewHas('products', function($collection) use ($product) {
            return $collection->contains($product);
        });
    }

    public function test_paginated_products_notContain_11th_product()
    {


        $products = Product::factory(11)->create();
        $latestProduct = $products->last();
        $response = $this->actingAs($this->user)->get('/products/all');
        $response->assertStatus(200);
        $response->assertViewHas('products', function($collection) use ($latestProduct) {
            return !$collection->contains($latestProduct);
        });
    }

    public function test_admin_can_see_product_create_button()
    {
        $response = $this->actingAs($this->admin)->get('/products/all');
        $response->assertStatus(200);
        $response->assertSee('Add new product');
    }

    public function test_non_admin_cannot_see_product_create_button()
    {
        $response = $this->actingAs($this->user)->get('/products/all');
        $response->assertStatus(200);
        $response->assertDontSee('Add new product');
    }

    public function test_admin_can_access_produt_create_page()
    {
        $response = $this->actingAs($this->admin)->get('/products/create');
        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_access_product_create_page()
    {
        $response = $this->actingAs($this->user)->get('/products/create');
        $response->assertStatus(403);
    }

    public function test_admin_create_product_successfully()
    {
        $product = [
            'name' => "Test Product",
            'price' => 324
        ];

        $response = $this->actingAs($this->admin)->post('/products/store', $product);
        $response->assertStatus(302);
        $response->assertRedirect('/products/all');
        $this->assertDatabaseHas('products', $product);
        
        $latestProduct = Product::latest()->first();
        $this->assertEquals($product['name'], $latestProduct['name']);
        $this->assertEquals($product['price'], $latestProduct['price']);
    }

    public function test_product_edit_page_contains_correct_values()
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->admin)->get('/products/' . $product->id . '/edit');
        $response->assertStatus(200);
        $response->assertSee('value="' . $product->name . '"', false);
        $response->assertSee('value="' . $product->price . '"', false);
        $response->assertViewHas('product', $product);
    }

    public function test_admin_update_product_successfully()
    {
        $product = Product::factory()->create();
        $updateProduct = [
            'name' => "Test Product",
            'price' => 324
        ];

        $response = $this->actingAs($this->admin)->post('/products/' . $product->id , $updateProduct);
        $response->assertStatus(302);
        $response->assertRedirect('/products/all');
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => $updateProduct['name'],
            'price' => $updateProduct['price']
        ]);
    }

    public function test_update_validation_works_successfully()
    {
        $product = Product::factory()->create();
        
        $response = $this->actingAs($this->admin)->post('/products/' . $product->id, [
            'name' => '',
            'price' => ''
        ]);
        $response->assertStatus(302);
        $response->assertInvalid(['name', 'price']);
    }

    public function test_product_delete_successful()
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->admin)->post('/products/' . $product->id . '/delete');
        $response->assertStatus(302);
        $response->assertRedirect('/products/all');
        $this->assertDatabaseMissing('products', $product->toArray());
        $this->assertDatabaseCount('products', 0);
    }


    // api tests 
    public function test_api_return_json_of_products()
    {
        $product = Product::factory()->create();
        $response = $this->getJson('api/products');
        $response->assertStatus(200);
        $response->assertJson([$product->toArray()]);
    }

    public function test_api_product_store_successful()
    {
        $product = [
            'name' => 'Api Product',
            'price' => 231
        ];
        $response = $this->postJson('api/products', $product);
        $response->assertStatus(201);
        $response->assertJson($product);
    }

    public function test_api_product_store_validation_errors_work()
    {
        $product = [
            'name' => '',
            'price' => 231
        ];
        $response = $this->postJson('api/products', $product);
        $response->assertStatus(422);
    }

    private function createUser(bool $isAdmin=false)
    {
        return User::factory()->create([
            'is_admin' => $isAdmin
        ]);
    }
}
