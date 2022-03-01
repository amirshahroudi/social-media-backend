<?php
/**
 * Created by PhpStorm.
 * User: amirpc
 * Date: 2/5/2022
 * Time: 5:49 PM
 */

namespace App\Actions\Profile;


use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Storage;

class UpdateProfileImageAction
{
    /**
     * @param $imageFile
     * @throws AuthenticationException
     */
    public function execute($imageFile)
    {
        if (!auth()->check()) {
            throw new AuthenticationException();
        }

        $path = Storage::disk('public')->putFile('profiles', $imageFile);
        if (!is_null($oldProfileImagePath = auth()->user()->profile_image_url)) {
            Storage::disk('public')->delete($oldProfileImagePath);
        }
        auth()->user()->update([
            'profile_image_url' => $path,
        ]);
    }
}