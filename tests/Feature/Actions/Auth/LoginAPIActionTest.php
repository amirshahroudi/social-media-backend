<?php

namespace Tests\Feature\Actions\Auth;

use App\Actions\Auth\LoginAPIAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LoginAPIActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_execute_method()
    {
        $user = User::factory()
            ->state(['password' => bcrypt('123456789')])
            ->user()->create();
        $data = array('email' => $user->email, 'password' => '123456789');

        $action = new LoginAPIAction();
        $token = $action->execute($data);

        $this->assertEquals($user->id, Auth::id());
        $this->assertNotNull($user->tokens()->first());
        $this->assertNotNull($token);
    }

    public function test_execute_method_with_incorrect_password()
    {
        $user = User::factory()
            ->state(['password' => bcrypt('123456789')])
            ->user()->create();
        $data = array('email' => $user->email, 'password' => 'incorrect');

        $catchException = false;
        $exceptionMessage = '';

        try {
            $action = new LoginAPIAction();
            $action->execute($data);
        } catch (ValidationException $exception) {
            $catchException = true;
            $exceptionMessage = $exception->getMessage();
        }

        $this->assertTrue($catchException);
        $this->assertEquals('The given data was invalid.', $exceptionMessage);
    }

    public function test_each_user_must_have_only_one_token_after_each_login()
    {
        $user = User::factory()->state(['password' => bcrypt('123456789')])->create();

        $data = array('email' => $user->email, 'password' => '123456789');

        $action = new LoginAPIAction();

        $action->execute($data);
        $action->execute($data);
        $token = $action->execute($data);

        $this->assertCount(1, $user->tokens);
    }
}
