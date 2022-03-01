<?php
/**
 * Created by PhpStorm.
 * User: amirpc
 * Date: 1/30/2022
 * Time: 6:09 PM
 */

namespace Tests\Feature\Controllers\Api;


trait JsonRequestForValidation
{
    /**
     * @param array $data
     * @param array $errors
     */
    public function sendPostJsonRequestForValidation($uri, array $data, array $errors)
    {
        $this->postJson($uri, $data)
            ->assertJsonValidationErrors($errors);
    }

    /**
     * @param array $data
     * @param array $errors
     */
    public function sendPatchJsonRequestForValidation($uri, array $data, array $errors)
    {
        $this->patchJson($uri, $data)
            ->assertJsonValidationErrors($errors);
    }
}