<?php
use App\Models\Product;

beforeEach(function() {
    $this->user = createUser(isAdmin: false);
    $this->admin = createUser(isAdmin: true);
});

test('if product empty, show not found', function () {
    $this->actingAs($this->user)
    ->get('products/all')
    ->assertStatus(200)
    ->assertSee('No product found');
});


 test('product page contain non empty table', function () {
    $product = Product::create([
        'name' => 'Product Name HAAH',
        'price' => 300
    ]);
    $this->actingAs($this->user)->get('/products/all')
        ->assertStatus(200)
        ->assertDontSee('No product found')
        ->assertSee('Product Name HAAH')
        ->assertViewHas('products', function($collection) use ($product) {
            return $collection->contains($product);
        });
 });


test(' admin create product successfully', function()
    {
    $product = [
        'name' => "Test Product",
        'price' => 324
    ];

    $this->actingAs($this->admin)
    ->post('/products/store', $product)
    ->assertStatus(302)
    ->assertRedirect('/products/all');

    $this->assertDatabaseHas('products', $product);
    
    $latestProduct = Product::latest()->first();
    $this->assertEquals($product['name'], $latestProduct['name']);
    $this->assertEquals($product['price'], $latestProduct['price']);
});