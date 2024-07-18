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

    private function createUser(bool $isAdmin=false)
    {
        return User::factory()->create([
            'is_admin' => $isAdmin
        ]);
    }
}
