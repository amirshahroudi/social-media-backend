<?php

namespace Tests\Feature\Controllers\Api\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Controllers\Api\JsonRequestForValidation;
use Tests\TestCase;

class ForgotPasswordControllerTest extends TestCase
{
    use RefreshDatabase, JsonRequestForValidation;

    public function test_forgotPassword_method()
    {
        Notification::fake();

        $user = User::factory()->create();
        $email = $user->email;
//        $token = Password::broker()->createToken($user);

        $this->postJson(route('api.password.forgot'), ['email' => $email])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'message' => "Reset password emailed to {$email}",
                'success' => true,
            ]);

        Notification::assertSentTo($user, ResetPassword::class);
        $this->assertCount(1,
            DB::select('select * from password_resets where email = :email', ['email' => $email])
        );
        $this->assertEquals(request()->route()->gatherMiddleware(), ['api']);
    }

    public function test_forgotPassword_method_with_unexists_email()
    {
        $this->postJson(route('api.password.forgot'), ['email' => 'test@email.com'])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson([
                'message' => "We can't find a user with that email address.",
                'success' => false,
            ]);
    }

    public function test_forgotPassword_validation_required_data()
    {
        $data = [];
        $errors = [
            'email' => 'The email field is required.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.password.forgot'), $data, $errors);
    }

    public function test_forgotPassword_validation_email_has_string_rule()
    {
        $data = ['email' => 124];
        $errors = [
            'email' => 'The email must be a string.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.password.forgot'), $data, $errors);
    }

    public function test_forgotPassword_validation_email_has_email_rule()
    {
        $data = ['email' => 'test'];
        $errors = [
            'email' => 'The email must be a valid email address.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.password.forgot'), $data, $errors);
    }
}