<?php
/**
 * Created by PhpStorm.
 * User: amirpc
 * Date: 1/31/2022
 * Time: 11:28 PM
 */

namespace App\Actions\Profile;


use Illuminate\Auth\AuthenticationException;

class UpdateNameAction
{
    /**
     * @param $newName
     * @throws AuthenticationException
     */
    public function execute($newName)
    {
        if (!auth()->check()) {
            throw new AuthenticationException();
        }

        auth()->user()->update([
            'name' => $newName,
        ]);
    }
}