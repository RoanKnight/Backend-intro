<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\SupplierSeeder;
use Database\Seeders\UserSeeder;

class FactoryAndSeederTest extends TestCase
{
    use RefreshDatabase;
    
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_user_factory()
    {
        $user = User::factory()->make();

        $this->assertInstanceOf(User::class, $user);
        $this->assertNotNull($user->name);
        $this->assertNotNull($user->email);
        $this->assertNotNull($user->email_verified_at);
        $this->assertNotNull($user->password);
        $this->assertNotNull($user->remember_token);

        $user = User::factory()->make();
        $this->assertDatabaseMissing('users', ['email' => $user->email]);
        
        $user = User::factory()->create();
        $this->assertDatabaseHas('users', ['email' => $user->email]);
    }

    public function test_user_seeder()
    {
        $this->seed(UserSeeder::class);
        $this->assertDatabaseCount('users', 10);
    }

    public function test_supplier_factory()
    {
        $supplier = Supplier::factory()->make();

        $this->assertInstanceOf(Supplier::class, $supplier);
        $this->assertNotNull($supplier->name);
        $this->assertNotNull($supplier->address);
        $this->assertNotNull($supplier->phone);
        $this->assertNotNull($supplier->email);

        $supplier = Supplier::factory()->make();
        $this->assertDatabaseMissing('suppliers', ['name' => $supplier->name]);

        $supplier = Supplier::factory()->create();
        $this->assertDatabaseHas('suppliers', ['name' => $supplier->name]);
    }

    public function test_supplier_seeder()
    {
        $this->seed(SupplierSeeder::class);
        $this->assertDatabaseCount('suppliers', 10);
    }
}
