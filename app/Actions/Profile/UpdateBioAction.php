<?php
/**
 * Created by PhpStorm.
 * User: amirpc
 * Date: 2/5/2022
 * Time: 5:32 PM
 */

namespace App\Actions\Profile;


use Illuminate\Auth\AuthenticationException;

class UpdateBioAction
{
    /**
     * @param $newBio
     * @throws AuthenticationException
     */
    public function execute($newBio)
    {
        if (!auth()->check()) {
            throw new AuthenticationException();
        }
        auth()->user()->update([
            'bio' => $newBio,
        ]);
    }
}