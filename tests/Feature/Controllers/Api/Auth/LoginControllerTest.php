<?php

namespace Tests\Feature\Controllers\Api\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Tests\Feature\Controllers\Api\JsonRequestForValidation;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase, JsonRequestForValidation;

//    test_login_method_with_incorrect_password                     -> in action
//    test_each_user_must_have_only_one_token_after_each_login      -> in action

    public function test_login_method()
    {
        User::factory()->count(rand(3, 10))->create();

        $user = User::factory()->state(['password' => bcrypt('123456789')])->create();

        $data = ['email' => $user->email, 'password' => '123456789'];

        $this->postJson(route('api.login'), $data)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => ['token'],
                'success',
            ])
            ->assertJson(['success' => true]);

        $this->assertEquals($user->id, Auth::id());
        $this->assertEquals(request()->route()->gatherMiddleware(), ['api']);
    }

    public function test_login_validation_required_data()
    {
        $data = [];
        $errors = [
            'email'    => 'The email field is required.',
            'password' => 'The password field is required.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.login'), $data, $errors);
    }

    public function test_login_validation_email_and_password_have_string_rule()
    {
        $data = [
            'email'    => 1234,
            'password' => 4321,
        ];
        $errors = [
            'email'    => 'The email must be a string.',
            'password' => 'The password must be a string.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.login'), $data, $errors);
    }

    public function test_login_validation_email_has_email_rule()
    {
        $data = [
            'email' => 'amir',
        ];
        $errors = [
            'email' => 'The email must be a valid email address.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.login'), $data, $errors);
    }
}