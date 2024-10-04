<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Exceptions;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Tests\TestCase;
use Database\Seeders\SupplierSeeder;
use Database\Seeders\ProductSeeder;

use App\Models\Product;
use App\Models\User;

class ProductTest extends TestCase
{
    // Create the database and run the migrations in each test
    use RefreshDatabase;

    private $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SupplierSeeder::class);
        $this->seed(ProductSeeder::class);

        $user = User::factory()->create();
        $this->token = $user->createToken('MyApp')->plainTextToken;
    }

    public function test_product_index(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson(route('products.index'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'description',
                    'price',
                    'supplier_id',
                    'created_at',
                    'updated_at',
                ]
            ]
        ]);
        $success = $response->json('success');
        $message = $response->json('message');
        $products = $response->json('data');

        $this->assertEquals($success, true);
        $this->assertEquals($message, 'Products retrieved successfully.');
        $this->assertCount(100, $products);
    }

    public function test_product_show(): void
    {
        $Product = Product::factory()->create();
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson(route('products.show', $Product->id));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'name',
                'description',
                'price',
                'supplier_id',
                'created_at',
                'updated_at',
            ]
        ]);

        $success = $response->json('success');
        $message = $response->json('message');
        $name = $response->json('data.name');
        $description = $response->json('data.description');
        $price = $response->json('data.price');
        $supplier_id = $response->json('data.supplier_id');

        $this->assertEquals($success, true);
        $this->assertEquals($message, 'Product retrieved successfully.');
        $this->assertEquals($name, $Product->name);
        $this->assertEquals($description, $Product->description);
        $this->assertEquals($price, $Product->price);
        $this->assertEquals($supplier_id, $Product->supplier_id);

        $this->assertDatabaseHas('products', [
            'id' => $Product->id
        ]);
    }

    public function test_product_show_not_found_error(): void
    {
        $missing_Product_id = mt_rand();
        while (Product::where('id', $missing_Product_id)->count() > 0) {
            $missing_Product_id = mt_rand();
        }

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson(route('products.show', $missing_Product_id));

        $response->assertStatus(404);
        $response->assertJsonStructure([
            'message',
            'success'
        ]);

        $success = $response->json('success');
        $message = $response->json('message');

        $this->assertEquals($success, false);
        $this->assertEquals($message, 'Product not found.');

        $this->assertDatabaseMissing('products', [
            'id' => $missing_Product_id
        ]);
    }

    public function test_product_store(): void
    {
        $product = Product::factory()->make();
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson(route('products.store'), $product->toArray());

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'name',
                'description',
                'price',
                'supplier_id',
                'created_at',
                'updated_at',
            ]
        ]);

        $success = $response->json('success');
        $message = $response->json('message');
        $name = $response->json('data.name');
        $description = $response->json('data.description');
        $price = $response->json('data.price');
        $supplier_id = $response->json('data.supplier_id');

        $this->assertEquals($success, true);
        $this->assertEquals($message, 'Product created successfully.');
        $this->assertEquals($name, $product->name);
        $this->assertEquals($description, $product->description);
        $this->assertEquals($price, $product->price);
        $this->assertEquals($supplier_id, $product->supplier_id);

        $this->assertDatabaseHas('products', [
            'name' => $product->name
        ]);
    }

    public function test_product_store_validation_error(): void
    {
        $product = Product::factory()->make();
        $product->name = '';
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson(route('products.store'), $product->toArray());

        $response->assertStatus(404);
        $response->assertJsonStructure([
            'data',
            'message',
            'success'
        ]);

        $success = $response->json('success');
        $message = $response->json('message');

        $this->assertEquals($success, false);
        $this->assertEquals($message, 'Validation Error.');

        $this->assertDatabaseMissing('products', [
            'name' => $product->name
        ]);
    }

    public function test_product_update(): void
    {
        $product = Product::factory()->create();
        $updatedProduct = Product::factory()->make();
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->putJson(route('products.update', $product->id), $updatedProduct->toArray());

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'name',
                'description',
                'price',
                'supplier_id',
                'created_at',
                'updated_at',
            ]
        ]);

        $success = $response->json('success');
        $message = $response->json('message');
        $name = $response->json('data.name');
        $description = $response->json('data.description');
        $price = $response->json('data.price');
        $supplier_id = $response->json('data.supplier_id');

        $this->assertEquals($success, true);
        $this->assertEquals($message, 'Product updated successfully.');
        $this->assertEquals($name, $updatedProduct->name);
        $this->assertEquals($description, $updatedProduct->description);
        $this->assertEquals($price, $updatedProduct->price);
        $this->assertEquals($supplier_id, $updatedProduct->supplier_id);

        $this->assertDatabaseHas('products', [
            'name' => $updatedProduct->name
        ]);
    }

    public function test_product_update_validation_error(): void
    {
        $product = Product::factory()->create();
        $updatedProduct = Product::factory()->make();
        $updatedProduct->name = '';
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson(route('products.update', $product->id), $updatedProduct->toArray());

        $response->assertStatus(404);
        $response->assertJsonStructure([
            'data',
            'message',
            'success'
        ]);

        $success = $response->json('success');
        $message = $response->json('message');

        $this->assertEquals($success, false);
        $this->assertEquals($message, 'Validation Error.');

        $this->assertDatabaseMissing('products', [
            'name' => $updatedProduct->name
        ]);
        $this->assertDatabaseHas('products', [
            'name' => $product->name
        ]);
    }

    public function test_product_update_not_found_error(): void
    {
        $updatedProduct = Product::factory()->make();
        $missing_product_id = mt_rand();
        while (Product::where('id', $missing_product_id)->count() > 0) {
            $missing_product_id = mt_rand();
        }
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson(route('products.update', $missing_product_id), $updatedProduct->toArray());

        $response->assertStatus(404);
        $response->assertJsonStructure([
            'message',
            'success'
        ]);

        $success = $response->json('success');
        $message = $response->json('message');

        $this->assertEquals($success, false);
        $this->assertEquals($message, 'Product not found.');

        $this->assertDatabaseMissing('products', [
            'id' => $missing_product_id
        ]);
    }

    public function test_product_destroy(): void
    {
        $product = Product::factory()->create();
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson(route('products.destroy', $product->id));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data'
        ]);

        $success = $response->json('success');
        $message = $response->json('message');
        $data = $response->json('data');

        $this->assertEquals($success, true);
        $this->assertEquals($message, 'Product deleted successfully.');
        $this->assertEmpty($data);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    public function test_product_destroy_not_found_error(): void
    {
        $missing_product_id = mt_rand();
        while (Product::where('id', $missing_product_id)->count() > 0) {
            $missing_product_id = mt_rand();
        }
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson(route('products.destroy', $missing_product_id));

        $response->assertStatus(404);
        $response->assertJsonStructure([
            'message',
            'success'
        ]);

        $success = $response->json('success');
        $message = $response->json('message');

        $this->assertEquals($success, false);
        $this->assertEquals($message, 'Product not found.');

        $this->assertDatabaseMissing('products', [
            'id' => $missing_product_id
        ]);
    }
}
