<?php

namespace Tests\Feature\Actions\Auth;

use App\Actions\Auth\ResetPasswordAction;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class ResetPasswordActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_execute_method()
    {
        Event::fake();

        $oldPassword = '123456789';
        $user = User::factory()
            ->state(['password' => bcrypt($oldPassword)])
            ->create();
        $token = Password::broker()->createToken($user);

        $data = [
            'token'                 => $token,
            'email'                 => $user->email,
            'password'              => $newPassword = 'new password',
            'password_confirmation' => $newPassword,
        ];

        $action = new ResetPasswordAction();
        $status =
            $action->execute(
                $data['email'],
                $data['password'],
                $data['password_confirmation'],
                $data['token']
            );

        $user->refresh();

        $this->assertTrue($status);
        $this->assertFalse(Hash::check($oldPassword, $user->password));
        $this->assertTrue(Hash::check($newPassword, $user->password));

        Event::assertDispatched(PasswordReset::class);
    }

    public function test_execute_method_with_invalid_token()
    {
        Event::fake();

        $oldPassword = '123456789';
        $user = User::factory()
            ->state(['password' => bcrypt($oldPassword)])
            ->create();

        $data = [
            'token'                 => 'invalid token',
            'email'                 => $user->email,
            'password'              => $newPassword = 'new password',
            'password_confirmation' => $newPassword,
        ];


        $action = new ResetPasswordAction();
        $status =
            $action->execute(
                $data['email'],
                $data['password'],
                $data['password_confirmation'],
                $data['token']
            );


        $user->refresh();

        $this->assertFalse($status);
        $this->assertTrue(Hash::check($oldPassword, $user->password));
        $this->assertFalse(Hash::check($newPassword, $user->password));

        Event::assertNotDispatched(PasswordReset::class);
    }

}
