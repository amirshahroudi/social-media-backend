<?php
/**
 * Created by PhpStorm.
 * User: amirpc
 * Date: 3/1/2022
 * Time: 10:07 PM
 */

namespace App\Actions\Profile;


use App\Models\User;

class UpdatePrivacyAction
{

    /**
     * @param bool $privacy
     */
    public function execute($privacy)
    {
        if ($privacy === 'private') {
            auth()->user()->update([
                'privacy' => User::PRIVATE_ACCOUNT,
            ]);
        } else if ($privacy === 'public') {
            auth()->user()->update([
                'privacy' => User::PUBLIC_ACCOUNT,
            ]);
        }
    }
}