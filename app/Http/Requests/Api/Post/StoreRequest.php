<?php

namespace App\Http\Requests\Api\Post;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'caption'  => ['required', 'string', 'max:2047'],
            'medias'   => ['required', 'array', 'max:10'],
            'medias.*' => ['file', 'mimes:jpeg,jpg', 'max:1023'],
        ];
    }
}