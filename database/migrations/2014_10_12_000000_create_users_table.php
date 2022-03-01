<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('bio', User::MAX_BIO_CHARS);
            $table->unsignedInteger('post_count')->default(0);
            $table->unsignedInteger('following_count')->default(0);
            $table->unsignedInteger('follower_count')->default(0);
            $table->string('profile_image_url')->nullable();
            $table->boolean('privacy')->default(User::DEFAULT_PRIVACY);
            $table->string('email')->unique();
            $table->string('type');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
