<?php

namespace Tests\Feature\Actions\Auth;

use App\Actions\Auth\LoginAPIAction;
use App\Actions\Auth\LogoutAPIAction;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LogoutAPIActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_execute_method()
    {
        $user = User::factory()
            ->state(['password' => bcrypt('123456789')])
            ->user()->create();

        $data = array('email' => $user->email, 'password' => '123456789');

        $loginAction = new LoginAPIAction();
        $loginAction->execute($data);

        $logoutAction = new LogoutAPIAction();
        $logoutAction->execute();

        $this->assertNull($user->tokens()->first());
        $this->assertEmpty($user->tokens);
        $this->assertCount(0, $user->tokens);
    }

    public function test_execute_method_when_user_not_logged_in()
    {
        $catchException = false;
        $exceptionMessage = '';

        try {
            $logoutAction = new LogoutAPIAction();
            $logoutAction->execute();
        } catch (AuthenticationException $exception) {
            $catchException = true;
            $exceptionMessage = $exception->getMessage();
        }

        $this->assertTrue($catchException);
        $this->assertEquals('You have not been logged in.', $exceptionMessage);
    }
}
