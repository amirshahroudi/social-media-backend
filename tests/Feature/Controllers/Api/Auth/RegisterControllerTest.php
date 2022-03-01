<?php

namespace Tests\Feature\Controllers\Api\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\Feature\Controllers\Api\JsonRequestForValidation;
use Tests\TestCase;

class RegisterControllerTest extends TestCase
{
    use RefreshDatabase, JsonRequestForValidation;

    public function test_register_method()
    {
        $user = User::factory()->make();

        $data = [
            'name'                  => $user->name,
            'email'                 => $user->email,
            'username'              => $user->username,
            'bio'                   => $user->bio,
            'profile_image_url'     => $user->profile_image_url,
            'password'              => $password = Str::random(10),
            'password_confirmation' => $password,
        ];

        $this->postJson(route('api.register'), $data)
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJson([
                'message' => "{$data['username']} created successfully",
                'success' => true,
            ]);

        $shouldSee = [
            'name'              => $data['name'],
            'email'             => $data['email'],
            'username'          => $data['username'],
            'bio'               => $data['bio'],
            'profile_image_url' => $data['profile_image_url'],
        ];

        $this->assertDatabaseHas('users', $shouldSee);
        $this->assertEquals(request()->route()->gatherMiddleware(), ['api']);
    }

    public function test_register_validation_required_data()
    {
        $data = [];
        $errors = [
            'name'     => 'The name field is required.',
            'username' => 'The username field is required.',
            'email'    => 'The email field is required.',
            'password' => 'The password field is required.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.register'), $data, $errors);
    }

    public function test_register_validation_name_and_username_and_bio_and_email_have_string_rule()
    {
        $data = [
            'name'     => 12,
            'username' => 1234,
            'bio'      => 6534,
            'email'    => 34,
        ];
        $errors = [
            'name'     => 'The name must be a string.',
            'username' => 'The username must be a string.',
            'bio'      => 'The bio must be a string.',
            'email'    => 'The email must be a string.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.register'), $data, $errors);
    }

    public function test_register_validation_name_and_username_and_email_have_max_255_rule()
    {
        $data = [
            'name'     => Str::random(256),
            'username' => Str::random(256),
            'email'    => Str::random(256),
        ];
        $errors = [
            'name'     => 'The name must not be greater than 255 characters.',
            'username' => 'The username must not be greater than 255 characters.',
            'email'    => 'The email must not be greater than 255 characters.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.register'), $data, $errors);
    }

    public function test_register_validation_bio_has_max_1023_rule()
    {
        $data = [
            'bio' => Str::random(1024),
        ];
        $errors = [
            'bio' => 'The bio must not be greater than 1023 characters.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.register'), $data, $errors);
    }

    public function test_register_validation_email_has_email_rule()
    {
        $data = [
            'email' => 'amir',
        ];
        $errors = [
            'email' => 'The email must be a valid email address.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.register'), $data, $errors);
    }

    public function test_register_validation_email_and_username_has_unique_rule()
    {
        User::factory()
            ->state(
                ['email'    => 'amir@email.com',
                 'username' => 'amir',
                ])
            ->create();
        $data = [
            'username' => 'amir',
            'email'    => 'amir@email.com',
        ];
        $errors = [
            'username' => 'The username has already been taken.',
            'email'    => 'The email has already been taken.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.register'), $data, $errors);
    }

    public function test_register_validation_password_has_confirmed_rule()
    {
        $data = [
            'password' => 123456789,
        ];
        $errors = [
            'password' => 'The password confirmation does not match.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.register'), $data, $errors);

        $data = [
            'password'              => 123456789,
            'password_confirmation' => 45678,
        ];
        $this->sendPostJsonRequestForValidation(route('api.register'), $data, $errors);
    }

    public function test_register_validation_password_has_password_default_rule()
    {
        $data = [
            'password' => 1234567,
        ];
        $errors = [
            'password' => 'The password must be at least 8 characters.',
        ];
        $this->sendPostJsonRequestForValidation(route('api.register'), $data, $errors);
    }
}