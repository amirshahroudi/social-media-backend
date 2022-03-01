<?php

namespace Tests\Feature\Models;

use App\Models\FollowRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FollowRequestTest extends TestCase
{
    use RefreshDatabase, ModelTestingHelper;

    protected function model(): Model
    {
        return new FollowRequest();
    }

    //todo maybe in future we need this model
}
