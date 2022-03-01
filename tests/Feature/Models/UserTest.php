<?php

namespace Tests\Feature\Models;

use App\Models\Comment;
use App\Models\FollowRequest;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase, ModelTestingHelper;

    protected function model(): Model
    {
        return new User();
    }

    public function test_user_relationship_with_post()
    {
        $postCount = rand(1, 10);
        $user = User::factory()
            ->has(Post::factory()->count($postCount))
            ->create();

        $this->assertCount($postCount, $user->posts);
        $this->assertTrue($user->posts->first() instanceof Post);
        $this->assertCount($postCount, DB::select('select * from posts where user_id = :userId', ['userId' => $user->id]));
    }

    public function test_user_relationship_with_comment()
    {
        $commentCount = rand(1, 10);
        $user = User::factory()
            ->has(Comment::factory()->count($commentCount))
            ->create();

        $this->assertCount($commentCount, $user->comments);
        $this->assertTrue($user->comments->first() instanceof Comment);
        $this->assertCount($commentCount, DB::select('select * from comments where user_id = :userId', ['userId' => $user->id]));
    }

    public function test_user_relation_with_like()
    {
        $likeCount = rand(1, 10);
        $user = User::factory()
            ->has(Like::factory()->count($likeCount))
            ->create();

        $this->assertCount($likeCount, $user->likes);
        $this->assertTrue($user->likes->first() instanceof Like);
        $this->assertCount($likeCount, DB::select('select * from likes where user_id = :userId', ['userId' => $user->id]));
    }

    public function test_user_relation_with_user_user_has_follower()
    {
        $followerCount = rand(1, 50);
        $user = User::factory()
            ->has(User::factory()->count($followerCount), 'followers')
            ->create();

        $user->refresh();

        $this->assertCount($followerCount, $user->followers);
//        $this->assertEquals($followerCount, $user->follower_count);
        $this->assertTrue($user->followers->first() instanceof User);
        $this->assertEquals($user->id, $user->followers->first()->followings->first()->id);
        $this->assertCount($followerCount, DB::select('select * from user_user where user_id = :userId', ['userId' => $user->id]));
    }

    public function test_user_relation_with_user_user_has_following()
    {
        $followingCount = rand(1, 50);
        $user = User::factory()
            ->has(User::factory()->count($followingCount), 'followings')
            ->create();

        $user->refresh();

        $this->assertCount($followingCount, $user->followings);
//        $this->assertEquals($followingCount, $user->following_count);
        $this->assertTrue($user->followings->first() instanceof User);
        $this->assertEquals($user->id, $user->followings->first()->followers->first()->id);
        $this->assertCount($followingCount, DB::select('select * from user_user where follower_id = :userId', ['userId' => $user->id]));
    }

    public function test_user_has_followRequest()
    {
        $receivedFollowRequestsCount = rand(1, 10);
        $privateUser = User::factory()
            ->has(User::factory()->count($receivedFollowRequestsCount),
                'receivedFollowRequests')
            ->privateAccount()
            ->create();

        $this->assertTrue($privateUser->receivedFollowRequests->first() instanceof User);
        $this->assertEquals($privateUser->id, $privateUser->receivedFollowRequests->first()->sentFollowRequests->first()->id);
        $this->assertCount($receivedFollowRequestsCount, $privateUser->receivedFollowRequests);
        $this->assertCount(
            $receivedFollowRequestsCount,
            DB::select("select * from follow_requests where request_to = :userId",
                ['userId' => $privateUser->id])
        );
    }

    public function test_user_send_followRequest()
    {
        $sentFollowRequestsCount = rand(1, 10);
        $user = User::factory()
            ->has(User::factory()->privateAccount()->count($sentFollowRequestsCount), 'sentFollowRequests')
            ->create();

        $this->assertTrue($user->sentFollowRequests->first() instanceof User);
        $this->assertEquals($user->id, $user->sentFollowRequests->first()->receivedFollowRequests->first()->id);
        $this->assertCount($sentFollowRequestsCount, $user->sentFollowRequests);
        $this->assertCount(
            $sentFollowRequestsCount,
            DB::select("select * from follow_requests where request_from = :userId",
                ['userId' => $user->id])
        );
    }
}