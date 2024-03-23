<?php

namespace Identity\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;

#[Schema(
    schema: "Identity_GetOrCreateTokenRequest",
    properties: [
        new Property(property: "token_name", type: "string", nullable: false,),
    ],
    type: "object",
)]
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
