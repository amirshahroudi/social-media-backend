<?php

namespace Tests\Feature\Actions\Auth;

use App\Actions\Auth\RegisterUserAction;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RegisterUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_execute_method()
    {
        Event::fake();

        $data = User::factory()->user()->make()->toArray();
        $data['password'] = '123456789';

        $action = new RegisterUserAction();
        $user = $action->execute(
            $data['name'],
            $data['username'],
            $data['bio'],
            null,
//            $data['profile_image_url'],
            $data['email'],
            $data['password']
        );

        $this->assertDatabaseHas('users', ['username' => $data['username']]);
        $this->assertEquals($data['email'], $user->email);
        $this->assertDatabaseCount('users', 1);

        Event::assertDispatched(Registered::class);
    }
}
