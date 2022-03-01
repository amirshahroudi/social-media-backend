<?php

namespace Tests\Feature\Controllers\Api\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class LogoutControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_logout_method()
    {
        //todo test maybe incomplete
        $password = '123456789';
        $user = User::factory()->state(['password' => bcrypt($password)])->create();
        $data = [
            'email'    => $user->email,
            'password' => $password,
        ];

        $this->postJson(route('api.login'), $data)
            ->assertStatus(Response::HTTP_OK);

        $this->actingAs($user)
            ->postJson(route('api.logout'))
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'message' => 'Logged out successfully',
                'success' => true,
            ]);

        $user->refresh();

        $this->assertCount(0, $user->tokens);
        $this->assertEquals(request()->route()->gatherMiddleware(), ['api', 'auth:sanctum']);
    }
}