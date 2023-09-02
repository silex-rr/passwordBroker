<?php

namespace Identity\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetOrCreateTokenRequest extends FormRequest
{
    public function authorize() : bool
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'token_name' => 'required|min:1',
        ];
    }
}
