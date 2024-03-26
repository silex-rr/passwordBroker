<?php

namespace PasswordBroker\Application\Http\Requests;

use Identity\Domain\User\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use PasswordBroker\Domain\Entry\Models\Groups\Admin;
use PasswordBroker\Domain\Entry\Models\Groups\Member;
use PasswordBroker\Domain\Entry\Models\Groups\Moderator;

#[Schema(
    schema: "PasswordBroker_EntryGroupUserRequest",
    properties: [
        new Property(property: "target_user_id", ref: "#/components/schemas/Identity_UserId",),
        new Property(
            property: "role",
            oneOf: [
                new Schema(ref: "#/components/schemas/PasswordBroker_Role_Admin"),
                new Schema(ref: "#/components/schemas/PasswordBroker_Role_Moderator"),
                new Schema(ref: "#/components/schemas/PasswordBroker_Role_Member"),
            ]),
        new Property(property: "encrypted_aes_password", ref: "#/components/schemas/PasswordBroker_EncryptedAesPassword", nullable: true,),
        new Property(property: "master_password", type: "string", nullable: true,),
    ],
)]
class EntryGroupUserRequest extends FormRequest
{
    public function authorize() : bool
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'target_user_id' => 'required|exists:' . User::class . ',user_id',
            'role' => Rule::in([Admin::ROLE_NAME, Moderator::ROLE_NAME, Member::ROLE_NAME]),
            'encrypted_aes_password' => 'required_without:master_password|string|min:1',
            'master_password' => 'required_without:encrypted_aes_password|string|min:1'
        ];
    }

    public function targetUser(): User
    {
        return User::where('user_id', $this->get('target_user_id'))->firstOrFail();
    }

}
