<?php

namespace Tests\Feature\Actions\Profile;

use App\Actions\Profile\UpdatePrivacyAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdatePrivacyActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_execute_method_change_to_private()
    {
        $user = User::factory()->user()->publicAccount()->create();

        $privacy = 'private';

        $this->actingAs($user);

        $action = new UpdatePrivacyAction();
        $action->execute($privacy);

        $user->refresh();

        $this->assertEquals(User::PRIVATE_ACCOUNT, $user->privacy);
    }

    public function test_execute_method_change_to_public()
    {
        $user = User::factory()->user()->privateAccount()->create();

        $privacy = 'public';

        $this->actingAs($user);

        $action = new UpdatePrivacyAction();
        $action->execute($privacy);

        $user->refresh();

        $this->assertEquals(User::PUBLIC_ACCOUNT, $user->privacy);
    }
}
