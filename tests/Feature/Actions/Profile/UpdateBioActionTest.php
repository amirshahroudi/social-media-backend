<?php

namespace Tests\Feature\Actions\Profile;

use App\Actions\Profile\UpdateBioAction;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateBioActionTest extends TestCase
{
    use RefreshDatabase;

    /*
     * 1 --> test execute
     * 2 -->test without logged in
     */

    public function test_execute_method()
    {
        $user = User::factory()->user()->create();
        $newBio = 'This is new bio for me.';

        $this->actingAs($user);

        $action = new UpdateBioAction();
        $action->execute($newBio);

        $user->refresh();

        $this->assertEquals($newBio, $user->bio);
    }

    public function test_execute_method_when_user_not_logged_in()
    {
        $newBio = 'This is new bio for me.';

        $action = new UpdateBioAction();

        $catchException = false;

        try {
            $action->execute($newBio);
        } catch (AuthenticationException $exception) {
            $catchException = true;
        }

        $this->assertTrue($catchException);
    }
}