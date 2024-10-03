<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Exceptions;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Tests\TestCase;
use Database\Seeders\SupplierSeeder;

use App\Models\Supplier;
use App\Models\User;

class SupplierTest extends TestCase
{
    // Create the database and run the migrations in each test
    use RefreshDatabase; 

    private $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SupplierSeeder::class);

        $user = User::factory()->create();
        $this->token = $user->createToken('MyApp')->plainTextToken;
    }

    public function test_supplier_index(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->getJson(route('suppliers.index'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'address',
                    'phone',
                    'email',
                    'created_at',
                    'updated_at',
                ]
            ]
        ]);
        $success = $response->json('success');
        $message = $response->json('message');
        $suppliers = $response->json('data');

        $this->assertEquals($success, true);
        $this->assertEquals($message, 'Suppliers retrieved successfully.');
        $this->assertCount(10, $suppliers);
    }

    public function test_supplier_show(): void
    {
        $supplier = Supplier::factory()->create();
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->getJson(route('suppliers.show', $supplier->id));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'name',
                'address',
                'phone',
                'email',
                'created_at',
                'updated_at',
            ]
        ]);

        $success = $response->json('success');
        $message = $response->json('message');
        $name = $response->json('data.name');
        $address = $response->json('data.address');
        $phone = $response->json('data.phone');
        $email = $response->json('data.email');

        $this->assertEquals($success, true);
        $this->assertEquals($message, 'Supplier retrieved successfully.');
        $this->assertEquals($name, $supplier->name);
        $this->assertEquals($address, $supplier->address);
        $this->assertEquals($phone, $supplier->phone);
        $this->assertEquals($email, $supplier->email);

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id
        ]);
    }

    public function test_supplier_show_not_found_error(): void
    {
        $missing_supplier_id = mt_rand();
        while(Supplier::where('id', $missing_supplier_id)->count() > 0) {
                $missing_supplier_id = mt_rand();
        }
        
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->getJson(route('suppliers.show', $missing_supplier_id));

        $response->assertStatus(404);
        $response->assertJsonStructure([
            'message',
            'success'
        ]);

        $success = $response->json('success');
        $message = $response->json('message');
        
        $this->assertEquals($success, false);
        $this->assertEquals($message, 'Supplier not found.');

        $this->assertDatabaseMissing('suppliers', [
            'id' => $missing_supplier_id
        ]);
    }

    public function test_supplier_store(): void
    {
        $supplier = Supplier::factory()->make();
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->postJson(route('suppliers.store'), $supplier->toArray());

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'name',
                'address',
                'phone',
                'email',
                'created_at',
                'updated_at',
            ]
        ]);

        $success = $response->json('success');
        $message = $response->json('message');
        $name = $response->json('data.name');
        $address = $response->json('data.address');
        $phone = $response->json('data.phone');
        $email = $response->json('data.email');

        $this->assertEquals($success, true);
        $this->assertEquals($message, 'Supplier created successfully.');
        $this->assertEquals($name, $supplier->name);
        $this->assertEquals($address, $supplier->address);
        $this->assertEquals($phone, $supplier->phone);
        $this->assertEquals($email, $supplier->email);

        $this->assertDatabaseHas('suppliers', [
            'name' => $supplier->name
        ]);
    }

    public function test_supplier_store_validation_error(): void
    {
        $supplier = Supplier::factory()->make();
        $supplier->name = '';
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->postJson(route('suppliers.store'), $supplier->toArray());

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

        $this->assertDatabaseMissing('suppliers', [
            'name' => $supplier->name
        ]);
    }

    public function test_supplier_update(): void
    {
        $supplier = Supplier::factory()->create();
        $updatedSupplier = Supplier::factory()->make();
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->putJson(route('suppliers.update', $supplier->id), $updatedSupplier->toArray());

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'name',
                'address',
                'phone',
                'email',
                'created_at',
                'updated_at',
            ]
        ]);

        $success = $response->json('success');
        $message = $response->json('message');
        $name = $response->json('data.name');
        $address = $response->json('data.address');
        $phone = $response->json('data.phone');
        $email = $response->json('data.email');

        $this->assertEquals($success, true);
        $this->assertEquals($message, 'Supplier updated successfully.');
        $this->assertEquals($name, $updatedSupplier->name);
        $this->assertEquals($address, $updatedSupplier->address);
        $this->assertEquals($phone, $updatedSupplier->phone);
        $this->assertEquals($email, $updatedSupplier->email);

        $this->assertDatabaseHas('suppliers', [
            'name' => $updatedSupplier->name
        ]);
    }

    public function test_supplier_update_validation_error(): void
    {
        $supplier = Supplier::factory()->create();
        $updatedSupplier = Supplier::factory()->make();
        $updatedSupplier->name = '';
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->putJson(route('suppliers.update', $supplier->id), $updatedSupplier->toArray());

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

        $this->assertDatabaseMissing('suppliers', [
            'name' => $updatedSupplier->name
        ]);
        $this->assertDatabaseHas('suppliers', [
            'name' => $supplier->name
        ]);
    }

    public function test_supplier_update_not_found_error(): void
    {
        $updatedSupplier = Supplier::factory()->make();
        $missing_supplier_id = mt_rand();
        while(Supplier::where('id', $missing_supplier_id)->count() > 0) {
                $missing_supplier_id = mt_rand();
        }
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->putJson(route('suppliers.update', $missing_supplier_id), $updatedSupplier->toArray());

        $response->assertStatus(404);
        $response->assertJsonStructure([
            'message',
            'success'
        ]);

        $success = $response->json('success');
        $message = $response->json('message');
        
        $this->assertEquals($success, false);
        $this->assertEquals($message, 'Supplier not found.');

        $this->assertDatabaseMissing('suppliers', [
            'id' => $missing_supplier_id
        ]);
    }

    public function test_supplier_destroy(): void
    {
        $supplier = Supplier::factory()->create();
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->deleteJson(route('suppliers.destroy', $supplier->id));
        
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
        $this->assertEquals($message, 'Supplier deleted successfully.');
        $this->assertEmpty($data);

        $this->assertDatabaseMissing('suppliers', [
            'id' => $supplier->id,
        ]);
    }

    public function test_supplier_destroy_not_found_error(): void
    {
        $updatedSupplier = Supplier::factory()->make();
        $missing_supplier_id = mt_rand();
        while(Supplier::where('id', $missing_supplier_id)->count() > 0) {
                $missing_supplier_id = mt_rand();
        }
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->deleteJson(route('suppliers.destroy', $missing_supplier_id));

        $response->assertStatus(404);
        $response->assertJsonStructure([
            'message',
            'success'
        ]);

        $success = $response->json('success');
        $message = $response->json('message');
        
        $this->assertEquals($success, false);
        $this->assertEquals($message, 'Supplier not found.');

        $this->assertDatabaseMissing('suppliers', [
            'id' => $missing_supplier_id
        ]);
    }
}
