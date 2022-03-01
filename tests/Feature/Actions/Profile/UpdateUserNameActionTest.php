<?php

namespace Tests\Feature\Actions\Profile;

use App\Actions\Profile\UpdateUsernameAction;
use App\Exceptions\ProfileException;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UpdateUsernameActionTest extends TestCase
{
    use RefreshDatabase;

    /*
     * 1 --> test execute method
     * 2 --> test when user not logged in
     * 3 --> test with exists username
     */
    public function test_execute_method()
    {
        $user = User::factory()->user()->create();

        $newUserName = 'amir';

        $this->actingAs($user);

        $action = new UpdateUsernameAction();
        $action->execute($newUserName);

        $user->refresh();

        $this->assertEquals($newUserName, $user->username);
    }

    public function test_execute_method_when_user_not_logged_in()
    {
        $newUserName = 'amir';

        $action = new UpdateUsernameAction();

        $catchException = false;

        try {
            $action->execute($newUserName);
        } catch (AuthenticationException $exception) {
            $catchException = true;
        }

        $this->assertTrue($catchException);
    }

    public function test_execute_method_with_exists_username()
    {
        $user = User::factory()->user()->create();

        $newUserName = 'amir';

        User::factory()->state(['username' => $newUserName])->create();

        $this->actingAs($user);

        $action = new UpdateUsernameAction();

        $catchException = false;
        $exceptionMessage = '';
        $exceptionCode = '';

        try {
            $action->execute($newUserName);
        } catch (ProfileException $exception) {
            $catchException = true;
            $exceptionMessage = $exception->getMessage();
            $exceptionCode = $exception->getCode();
        }

        $this->assertTrue($catchException);
        $this->assertEquals(ProfileException::USERNAME_ALREADY_HAVE_TAKEN, $exceptionMessage);
        $this->assertEquals(ProfileException::USERNAME_ALREADY_HAVE_TAKEN_STATUS_CODE, $exceptionCode);
        $this->assertCount(1,
            DB::select('select * from users where username = :username', ['username' => $newUserName])
        );
    }
}