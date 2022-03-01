<?php
/**
 * Created by PhpStorm.
 * User: amirpc
 * Date: 1/18/2022
 * Time: 7:31 PM
 */

namespace App\Actions\Auth;


use App\Models\User;
use Illuminate\Auth\Events\Registered;

class RegisterUserAction
{
    /**
     * @param $name
     * @param $username
     * @param $bio
     * @param $profile_image_url
     * @param $email
     * @param $password
     * @return User
     */
    public function execute($name, $username, $bio, $profile_image_url, $email, $password)
    {
        //todo email and username must be unique
        $type = User::USER;
        $user = User::create([
            'name'              => $name,
            'username'          => $username,
            'bio'               => $bio,
            'profile_image_url' => $profile_image_url,
            'email'             => $email,
            'password'          => bcrypt($password),
            'type'              => $type,
        ]);
        event(new Registered($user));
        return $user;
    }
}