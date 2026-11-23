<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_user_resets_password_and_can_log_in(): void
    {
        $this->artisan('admin:user', ['email' => 'Manager@Test.ru', '--password' => 'first-pass-123', '--name' => 'Руслан'])->assertSuccessful();
        $user = User::query()->where('email', 'manager@test.ru')->sole();
        $this->assertSame('Руслан', $user->name);
        $this->assertTrue(Hash::check('first-pass-123', $user->password));

        $this->artisan('admin:user', ['email' => 'manager@test.ru'])->assertFailed();
        $this->artisan('admin:user', ['email' => 'manager@test.ru', '--password' => 'short'])->assertFailed();
        $this->artisan('admin:user', ['email' => 'manager@test.ru', '--password' => 'second-pass-456'])->assertSuccessful();

        $this->post('/che168/login', ['email' => 'manager@test.ru', 'password' => 'second-pass-456'])->assertRedirect('/che168');
        $this->get('/che168/showcase')->assertOk();
    }

    public function test_generated_password_is_printed_once(): void
    {
        $this->artisan('admin:user', ['email' => 'new@test.ru'])->expectsOutputToContain('PASSWORD=')->assertSuccessful();
        $this->artisan('admin:user', ['email' => 'new@test.ru', '--reset' => true])->expectsOutputToContain('Пароль обновлён')->assertSuccessful();
        $this->artisan('admin:user', ['email' => 'not-an-email'])->assertFailed();
    }
}
