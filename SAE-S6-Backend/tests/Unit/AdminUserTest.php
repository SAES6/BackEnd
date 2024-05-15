<?php

// tests/Unit/AdminUserTest.php

namespace Tests\Unit;

use App\Models\AdminUser;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminUserTest extends TestCase
{
    use DatabaseTransactions;

    public function test_create_admin_user()
    {
        $adminUser = AdminUser::factory()->create();

        $this->assertDatabaseHas('admin_users', [
            'email' => $adminUser->email,
            'username' => $adminUser->username,
        ]);
    }

    public function test_update_admin_user()
    {
        $adminUser = AdminUser::factory()->create();
        $adminUser->update(['username' => 'new_username']);

        $this->assertDatabaseHas('admin_users', [
            'username' => 'new_username',
        ]);
    }

    public function test_delete_admin_user()
    {
        $adminUser = AdminUser::factory()->create();
        $adminUser->delete();

        $this->assertDatabaseMissing('admin_users', [
            'id' => $adminUser->id,
        ]);

    }

    public function test_read_admin_user()
    {
        $adminUser = AdminUser::factory()->create();

        $this->assertEquals(AdminUser::find($adminUser->id)->email, $adminUser->email);
    }
}
