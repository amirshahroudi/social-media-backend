<?php
/**
 * Created by PhpStorm.
 * User: amirpc
 * Date: 1/31/2022
 * Time: 11:33 PM
 */

namespace App\Actions\Profile;


use App\Exceptions\ProfileException;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;

class UpdateUsernameAction
{
    /**
     * @param $newUserName
     * @throws AuthenticationException
     * @throws ProfileException
     */
    public function execute($newUserName)
    {
        if (!auth()->check()) {
            throw new AuthenticationException();
        }
        if (User::where('username', $newUserName)->first()) {
            throw new ProfileException(ProfileException::USERNAME_ALREADY_HAVE_TAKEN, ProfileException::USERNAME_ALREADY_HAVE_TAKEN_STATUS_CODE);
        }
        auth()->user()->update([
            'username' => $newUserName,
        ]);
    }
}