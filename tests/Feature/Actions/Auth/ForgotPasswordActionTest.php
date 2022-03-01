<?php

namespace Tests\Feature\Actions\Auth;

use App\Actions\Auth\ForgotPasswordAction;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ForgotPasswordActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_execute_method()
    {
        Notification::fake();

        $user = User::factory()->create();
        $email = $user->email;

        $action = new ForgotPasswordAction();
        $status = $action->execute($email);

        $this->assertTrue(true);
        Notification::assertSentTo($user, ResetPassword::class);
        $this->assertCount(1, DB::select('select * from password_resets where email = :email', ['email' => $email]));
    }

    public function test_execute_method_with_unexists_email()
    {
        $action = new ForgotPasswordAction();
        $status = $action->execute('amir');

        $this->assertFalse($status);
    }
}
