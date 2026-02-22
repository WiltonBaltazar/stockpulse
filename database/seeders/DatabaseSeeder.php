<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['manage users', 'manage ingredients', 'manage recipes', 'manage inventory', 'manage finances', 'manage sales'] as $permissionName) {
            Permission::query()->firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        $adminRole = Role::query()->firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $userRole = Role::query()->firstOrCreate([
            'name' => 'user',
            'guard_name' => 'web',
        ]);

        $adminRole->syncPermissions(['manage users', 'manage ingredients', 'manage recipes', 'manage inventory', 'manage finances', 'manage sales']);
        $userRole->syncPermissions(['manage ingredients', 'manage recipes', 'manage inventory', 'manage finances', 'manage sales']);

        $admin = User::query()->updateOrCreate([
            'email' => 'admin@bakeryapp.test',
        ], [
            'name' => 'Bakery Admin',
            'contact_number' => '+258 84 000 0000',
            'password' => 'password',
        ]);

        $admin->syncRoles(['admin']);

        $operator = User::query()->updateOrCreate([
            'email' => 'operator@bakeryapp.test',
        ], [
            'name' => 'Bakery Operator',
            'contact_number' => '+258 85 111 2233',
            'password' => 'password',
        ]);

        $operator->syncRoles(['user']);

        $this->call(IngredientSeeder::class);
        $this->call(RecipeSeeder::class);
        $this->call(InventoryMovementSeeder::class);
        $this->call(ProductionBatchSeeder::class);
        $this->call(SaleSeeder::class);
        $this->call(FinancialTransactionSeeder::class);
    }
}
