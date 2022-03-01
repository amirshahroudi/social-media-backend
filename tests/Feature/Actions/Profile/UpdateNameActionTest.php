<?php

namespace Tests\Feature\Actions\Profile;

use App\Actions\Profile\UpdateNameAction;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateNameActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_execute_method()
    {
        $user = User::factory()->user()->create();

        $newName = 'amir';

        $this->actingAs($user);

        $action = new UpdateNameAction();
        $action->execute($newName);

        $user->refresh();

        $this->assertEquals($newName, $user->name);
    }

    public function test_execute_method_when_user_not_logged_in()
    {
        $newName = 'amir';

        $action = new UpdateNameAction();

        $catchException = false;

        try {
            $action->execute($newName);
        } catch (AuthenticationException $exception) {
            $catchException = true;
        }

        $this->assertTrue($catchException);
    }
}