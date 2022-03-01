<?php

namespace Tests\Feature\Controllers\Api\Auth;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\Feature\Controllers\Api\JsonRequestForValidation;
use Tests\TestCase;

class ResetPasswordControllerTest extends TestCase
{
    use RefreshDatabase, JsonRequestForValidation;

    public function test_resetPassword_method()
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

        $this->postJson(route('api.password.reset'), $data)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'message' => "Password for {$user->email} updated",
                'success' => true,
            ]);

        $user->refresh();

        $this->assertFalse(Hash::check($oldPassword, $user->password));
        $this->assertTrue(Hash::check($newPassword, $user->password));
        $this->assertEquals(request()->route()->gatherMiddleware(), ['api']);
        Event::assertDispatched(PasswordReset::class);
    }

    public function test_resetPassword_method_invalid_token()
    {
        Event::fake();

        $oldPassword = '123456789';
        $user = User::factory()
            ->state(['password' => bcrypt($oldPassword)])
            ->create();

        $token = Password::broker()->createToken($user);
        $token[5] = 's';
        $token[6] = '5';

        $data = [
            'token'                 => $token,
            'email'                 => $user->email,
            'password'              => $newPassword = 'new password',
            'password_confirmation' => $newPassword,
        ];

        $this->postJson(route('api.password.reset'), $data)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson([
                'message' => 'Password reset token is invalid.',
                'success' => false,
            ]);
        $user->refresh();
        $this->assertTrue(Hash::check($oldPassword, $user->password));
        $this->assertFalse(Hash::check($newPassword, $user->password));
        $this->assertEquals(request()->route()->gatherMiddleware(), ['api']);
        Event::assertNotDispatched(PasswordReset::class);
    }

    public function test_resetPassword_validation_required_data()
    {
        $data = [];
        $errors = [
            'token'    => 'The token field is required.',
            'email'    => 'The email field is required.',
            'password' => 'The password field is required.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.password.reset'), $data, $errors);
    }

    public function test_resetPassword_validation_email_has_email_rule()
    {
        $data = [
            'email' => 'this is not email',
        ];
        $errors = [
            'email' => 'The email must be a valid email address.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.password.reset'), $data, $errors);
    }

    public function test_resetPassword_validation_password_has_string_rule()
    {
        $data = [
            'password' => 123456789,
        ];
        $errors = [
            'password' => 'The password must be a string.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.password.reset'), $data, $errors);
    }

    public function test_resetPassword_validation_password_has_confirmed_rule()
    {
        $data = [
            'password' => '123456789',
        ];
        $errors = [
            'password' => 'The password confirmation does not match.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.password.reset'), $data, $errors);

        $data = [
            'password'              => '123456789',
            'password_confirmation' => '12345678901',
        ];
        $errors = [
            'password' => 'The password confirmation does not match.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.password.reset'), $data, $errors);
    }

    public function test_resetPassword_validation_password_has_password_default_rule()
    {
        $data = [
            'password' => '12345',
        ];
        $errors = [
            'password' => 'The password must be at least 8 characters.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.password.reset'), $data, $errors);
    }
}