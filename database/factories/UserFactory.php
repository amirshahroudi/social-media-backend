<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name'              => $this->faker->name(),
            //            'username'          => $this->faker->unique()->userName,
            'username'          => Str::slug($this->faker->name) . (User::max('id') + rand(99, 99999)),
            'bio'               => $this->faker->text(User::MAX_BIO_CHARS),
            'post_count'        => 0,
            //            'profile_image_url' => $this->faker->imageUrl,
            'privacy'           => Arr::random([User::PUBLIC_ACCOUNT, User::PRIVATE_ACCOUNT]),
            //            'email'             => $this->faker->unique()->safeEmail,
            'email'             => 'email' . (User::max('id') + rand(99, 99999)) . '@gmail' . rand(99, 99999) . 'com',
            'type'              => Arr::random([User::ADMIN, User::USER]),
            'email_verified_at' => now(),
            'password'          => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token'    => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }

    public function admin()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => User::ADMIN,
            ];
        });
    }

    public function user()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => User::USER,
            ];
        });
    }

    public function privateAccount()
    {
        return $this->state(function (array $attributes) {
            return [
                'privacy' => User::PRIVATE_ACCOUNT,
            ];
        });
    }

    public function publicAccount()
    {
        return $this->state(function (array $attributes) {
            return [
                'privacy' => User::PUBLIC_ACCOUNT,
            ];
        });
    }
}
