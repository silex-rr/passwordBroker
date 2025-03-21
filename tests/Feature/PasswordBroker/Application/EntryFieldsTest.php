<?php

namespace Tests\Feature\PasswordBroker\Application;

use Identity\Domain\User\Models\User;
use Identity\Infrastructure\Factories\User\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Testing\Fluent\AssertableJson;
use PasswordBroker\Application\Services\EncryptionService;
use PasswordBroker\Application\Services\EntryGroupService;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\TOTPHashAlgorithm;
use PasswordBroker\Domain\Entry\Models\Fields\File;
use PasswordBroker\Domain\Entry\Models\Fields\Password;
use PasswordBroker\Domain\Entry\Models\Fields\TOTP;
use PasswordBroker\Domain\Entry\Services\AddEntry;
use PasswordBroker\Infrastructure\Validation\Handlers\EntryValidationHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Encoder\Base64Encoder;
use Tests\TestCase;

class EntryFieldsTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;
    use PasswordHelper;

    public function test_guest_cannot_see_entry_fields(): void
    {
        /**
         * @var EntryGroup $entryGroup
         * @var User $admin
         * @var Entry $entry
         * @var EntryGroupService $entryGroupService
         */
        $entryGroup = EntryGroup::factory()->create();
        $admin = User::factory()->create();
        $entry = Entry::factory()->make();
        $entryGroupService = app(EntryGroupService::class);

        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroup);

        $this->actingAs($admin);

        dispatch_sync(new AddEntry($entry, $entryGroup, new EntryValidationHandler()));

        $this->actingAsGuest();

        /**
         * @var Entry $entry
         */
        $entry = Entry::where('title', $entry->title)->firstOrFail();

        $password_str = $this->faker->password(12, 32);
        $password = $this->getPasswordHelper($admin, $entryGroup, $entry, $password_str);

        $this->getJson(route('entryFields', ['entryGroup' => $entryGroup, 'entry' => $entry]))
            ->assertStatus(401);
    }

    public function test_guest_cannot_see_an_entry_field(): void
    {
        /**
         * @var EntryGroup $entryGroup
         * @var User $admin
         * @var Entry $entry
         * @var EntryGroupService $entryGroupService
         */
        $entryGroup = EntryGroup::factory()->create();
        $admin = User::factory()->create();
        $entry = Entry::factory()->make();
        $entryGroupService = app(EntryGroupService::class);

        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroup);

        $this->actingAs($admin);
        dispatch_sync(new AddEntry($entry, $entryGroup, new EntryValidationHandler()));
        $this->actingAsGuest();
        /**
         * @var Entry $entry
         */
        $entry = Entry::where('title', $entry->title)->firstOrFail();
        $password_str = $this->faker->password(12, 32);
        $password = $this->getPasswordHelper($admin, $entryGroup, $entry, $password_str);

        $this->getJson(route('entryField', ['entryGroup' => $entryGroup, 'entry' => $entry, 'field' => $password->field_id]))
            ->assertStatus(401);
    }

    public function test_guest_cannot_add_a_field_to_entry(): void
    {
        /**
         * @var EntryGroup $entryGroup
         * @var User $admin
         * @var Entry $entry
         * @var EntryGroupService $entryGroupService
         */
        $entryGroup = EntryGroup::factory()->create();
        $admin = User::factory()->create();
        $entry = Entry::factory()->make();
        $entryGroupService = app(EntryGroupService::class);

        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroup);

        $this->actingAs($admin);
        dispatch_sync(new AddEntry($entry, $entryGroup, new EntryValidationHandler()));
        $this->actingAsGuest();
        /**
         * @var Entry $entry
         */
        $entry = Entry::where('title', $entry->title)->firstOrFail();
        $password_str = $this->faker->password(12, 32);
        $login = $this->faker->word();

        $this->postJson(
            route('entryFields', ['entryGroup' => $entryGroup, 'entry' => $entry]),
            [
                'type' => Password::TYPE,
                'login' => $login,
                'value' => $password_str,
                'master_password' => UserFactory::MASTER_PASSWORD
            ]
        )->assertStatus(401);

        $this->assertCount(0,
            Entry::where('entry_id', $entry->entry_id)->firstOrFail()->fields()
        );
    }

    public function test_guest_cannot_update_an_entry_field(): void
    {
        /**
         * @var EntryGroup $entryGroup
         * @var User $admin
         * @var Entry $entry
         * @var EntryGroupService $entryGroupService
         */
        $entryGroup = EntryGroup::factory()->create();
        $admin = User::factory()->create();
        $entry = Entry::factory()->make();
        $entryGroupService = app(EntryGroupService::class);

        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroup);

        $this->actingAs($admin);
        dispatch_sync(new AddEntry($entry, $entryGroup, new EntryValidationHandler()));
        $this->actingAsGuest();
        /**
         * @var Entry $entry
         */
        $entry = Entry::where('title', $entry->title)->firstOrFail();
        $password_str = $this->faker->password(12, 32);
        $password = $this->getPasswordHelper($admin, $entryGroup, $entry, $password_str);
        $password_str_new = $password_str . '_new';
        $this->putJson(
            route('entryField', ['entryGroup' => $entryGroup, 'entry' => $entry, 'field' => $password->field_id]),
            [
                'type' => Password::TYPE,
                'value' => $password_str_new,
                'master_password' => UserFactory::MASTER_PASSWORD
            ]
        )->assertStatus(401);

        /**
         * @var Password $password_new
         */
        $password_new = Entry::where('entry_id', $entry->entry_id)->firstOrFail()->passwords()->where('field_id', $password->field_id)->firstOrFail();

        $this->assertTrue($password_new->value_encrypted->equals($password->value_encrypted));

    }

    public function test_guest_cannot_delete_an_entry_field(): void
    {
        /**
         * @var EntryGroup $entryGroup
         * @var User $admin
         * @var Entry $entry
         * @var EntryGroupService $entryGroupService
         */
        $entryGroup = EntryGroup::factory()->create();
        $admin = User::factory()->create();
        $entry = Entry::factory()->make();
        $entryGroupService = app(EntryGroupService::class);

        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroup);

        $this->actingAs($admin);
        dispatch_sync(new AddEntry($entry, $entryGroup, new EntryValidationHandler()));
        $this->actingAsGuest();
        /**
         * @var Entry $entry
         */
        $entry = Entry::where('title', $entry->title)->firstOrFail();

        $password_str = $this->faker->password(12, 32);
        $password = $this->getPasswordHelper($admin, $entryGroup, $entry, $password_str);

        $this->deleteJson(
            route('entryField', ['entryGroup' => $entryGroup, 'entry' => $entry, 'field' => $password->field_id]),
        )->assertStatus(401);

        $this->assertCount(1,
            Entry::where('entry_id', $entry->entry_id)->firstOrFail()->fields()
        );
    }

    public function test_admin_can_see_entry_fields_belonged_to_their_group(): void
    {
        /**
         * @var EntryGroup $entryGroup
         * @var User $admin
         * @var Entry $entry
         * @var EntryGroupService $entryGroupService
         */
        $entryGroup = EntryGroup::factory()->create();
        $admin = User::factory()->create();
        $entry = Entry::factory()->make();
        $entryGroupService = app(EntryGroupService::class);

        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroup);

        $this->actingAs($admin);
        dispatch_sync(new AddEntry($entry, $entryGroup, new EntryValidationHandler()));
        /**
         * @var Entry $entry
         */
        $entry = Entry::where('title', $entry->title)->firstOrFail();
        $password_str = $this->faker->password(12, 32);
        $password = $this->getPasswordHelper($admin, $entryGroup, $entry, $password_str);

        /**
         * @var Base64Encoder $base64Encoder
         */
        $base64Encoder = app(Base64Encoder::class);

        $this->getJson(route('entryFields', ['entryGroup' => $entryGroup, 'entry' => $entry]))
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $fields)
                => $fields->has(1)->first(fn (AssertableJson $field)
                    => $field->where('field_id', $password->field_id->getValue())
//                        ->where('encrypted_value_base64',
//                            $base64Encoder->encodeString($password->value_encrypted->getValue()))
//                        ->where('initialization_vector_base64',
//                            $base64Encoder->encodeString($password->initialization_vector->getValue()))
                        ->where('type', $password->getType())
                        ->etc()
                )
            );
    }

    public function test_moderator_can_see_entry_fields_belonged_to_their_group(): void
    {
        /**
         * @var EntryGroup $entryGroup
         * @var User $admin
         * @var User $moderator
         * @var Entry $entry
         * @var EntryGroupService $entryGroupService
         */
        $entryGroup = EntryGroup::factory()->create();
        [$admin, $moderator] = User::factory()->count(2)->create();
        $entry = Entry::factory()->make();
        $entryGroupService = app(EntryGroupService::class);

        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroup);

        $this->actingAs($admin);
        dispatch_sync(new AddEntry($entry, $entryGroup, new EntryValidationHandler()));
        $entryGroupService->addUserToGroupAsModerator($moderator, $entryGroup, null, UserFactory::MASTER_PASSWORD);
        $this->actingAs($moderator);
        /**
         * @var Entry $entry
         */
        $entry = Entry::where('title', $entry->title)->firstOrFail();
        $password_str = $this->faker->password(12, 32);
        $password = $this->getPasswordHelper($admin, $entryGroup, $entry, $password_str);

        $base64Encoder = app(Base64Encoder::class);

        $this->getJson(route('entryFields', ['entryGroup' => $entryGroup, 'entry' => $entry]))
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $fields)
                => $fields->has(1)->first(fn (AssertableJson $field)
                    => $field->where('field_id', $password->field_id->getValue())
//                        ->where('encrypted_value_base64',
//                            $base64Encoder->encodeString($password->value_encrypted->getValue()))
//                        ->where('initialization_vector_base64',
//                            $base64Encoder->encodeString($password->initialization_vector->getValue()))
                        ->where('type', $password->getType())
                        ->etc()
                    )
            );
    }

    public function test_member_can_see_entry_fields_belonged_to_their_group(): void
    {
        /**
         * @var EntryGroup $entryGroup
         * @var User $admin
         * @var User $member
         * @var Entry $entry
         * @var EntryGroupService $entryGroupService
         */
        $entryGroup = EntryGroup::factory()->create();
        [$admin, $member] = User::factory()->count(2)->create();
        $entry = Entry::factory()->make();
        $entryGroupService = app(EntryGroupService::class);

        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroup);

        $this->actingAs($admin);
        dispatch_sync(new AddEntry($entry, $entryGroup, new EntryValidationHandler()));
        $entryGroupService->addUserToGroupAsMember($member, $entryGroup, null, UserFactory::MASTER_PASSWORD);
//        Auth::logout();
        $this->actingAs($member);
        /**
         * @var Entry $entry
         */
        $entry = Entry::where('title', $entry->title)->firstOrFail();
        $password_str = $this->faker->password(12, 32);
        $password = $this->getPasswordHelper($admin, $entryGroup, $entry, $password_str);

        $base64Encoder = app(Base64Encoder::class);

        $this->getJson(route('entryFields', ['entryGroup' => $entryGroup, 'entry' => $entry]))
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $fields)
                => $fields->has(1)->first(fn (AssertableJson $field)
                    => $field->where('field_id', $password->field_id->getValue())
//                        ->where('encrypted_value_base64',
//                            $base64Encoder->encodeString($password->value_encrypted->getValue()))
//                        ->where('initialization_vector_base64',
//                            $base64Encoder->encodeString($password->initialization_vector->getValue()))
                        ->where('type', $password->getType())
                        ->etc()
                    )
            );
    }

    public function test_admin_can_see_an_entry_field_belonged_to_their_group(): void
    {
        /**
         * @var EntryGroup $entryGroup
         * @var User $admin
         * @var Entry $entry
         * @var EntryGroupService $entryGroupService
         */
        $entryGroup = EntryGroup::factory()->create();
        $admin = User::factory()->create();
        $entry = Entry::factory()->make();
        $entryGroupService = app(EntryGroupService::class);

        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroup);

        $this->actingAs($admin);
        dispatch_sync(new AddEntry($entry, $entryGroup, new EntryValidationHandler()));
        /**
         * @var Entry $entry
         */
        $entry = Entry::where('title', $entry->title)->firstOrFail();
        $password_str = $this->faker->password(12, 32);
        $password = $this->getPasswordHelper($admin, $entryGroup, $entry, $password_str);

        $base64Encoder = app(Base64Encoder::class);

        $this->getJson(route('entryField', ['entryGroup' => $entryGroup, 'entry' => $entry, 'field' => $password]))
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $field)
                => $field->where('field_id', $password->field_id->getValue())
//                    ->where('encrypted_value_base64',
//                        $base64Encoder->encodeString($password->value_encrypted->getValue()))
//                    ->where('initialization_vector_base64',
//                        $base64Encoder->encodeString($password->initialization_vector->getValue()))
                    ->where('type', $password->getType())
                    ->etc()
            );
    }

    public function test_moderator_can_see_an_entry_field_belonged_to_their_group(): void
    {
        /**
         * @var EntryGroup $entryGroup
         * @var User $admin
         * @var User $moderator
         * @var Entry $entry
         * @var EntryGroupService $entryGroupService
         */
        $entryGroup = EntryGroup::factory()->create();
        [$admin, $moderator] = User::factory()->count(2)->create();
        $entry = Entry::factory()->make();
        $entryGroupService = app(EntryGroupService::class);

        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroup);

        $this->actingAs($admin);
        dispatch_sync(new AddEntry($entry, $entryGroup, new EntryValidationHandler()));
        $entryGroupService->addUserToGroupAsModerator($moderator, $entryGroup, null, UserFactory::MASTER_PASSWORD);
        $this->actingAs($moderator);
        /**
         * @var Entry $entry
         */
        $entry = Entry::where('title', $entry->title)->firstOrFail();
        $password_str = $this->faker->password(12, 32);
        $password = $this->getPasswordHelper($admin, $entryGroup, $entry, $password_str);

        $base64Encoder = app(Base64Encoder::class);

        $this->getJson(route('entryField', ['entryGroup' => $entryGroup, 'entry' => $entry, 'field' => $password]))
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $field)
                => $field->where('field_id', $password->field_id->getValue())
//                    ->where('encrypted_value_base64',
//                        $base64Encoder->encodeString($password->value_encrypted->getValue()))
//                    ->where('initialization_vector_base64',
//                        $base64Encoder->encodeString($password->initialization_vector->getValue()))
                    ->where('type', $password->getType())
                    ->etc()
            );
    }

    public function test_member_can_see_an_entry_field_belonged_to_their_group(): void
    {
        /**
         * @var EntryGroup $entryGroup
         * @var User $admin
         * @var User $member
         * @var Entry $entry
         * @var EntryGroupService $entryGroupService
         */
        $entryGroup = EntryGroup::factory()->create();
        [$admin, $member] = User::factory()->count(2)->create();
        $entry = Entry::factory()->make();
        $entryGroupService = app(EntryGroupService::class);

        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroup);

        $this->actingAs($admin);
        dispatch_sync(new AddEntry($entry, $entryGroup, new EntryValidationHandler()));
        $entryGroupService->addUserToGroupAsMember($member, $entryGroup, null, UserFactory::MASTER_PASSWORD);
        $this->actingAs($member);
        /**
         * @var Entry $entry
         */
        $entry = Entry::where('title', $entry->title)->firstOrFail();
        $password_str = $this->faker->password(12, 32);
        $password = $this->getPasswordHelper($admin, $entryGroup, $entry, $password_str);

        /**
         * @var Base64Encoder $base64Encoder
         */
        $base64Encoder = app(Base64Encoder::class);

        $this->getJson(route('entryField', ['entryGroup' => $entryGroup, 'entry' => $entry, 'field' => $password]))
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $field)
                => $field->where('field_id', $password->field_id->getValue())
//                    ->where('encrypted_value_base64',
//                        $base64Encoder->encodeString($password->value_encrypted->getValue()))
//                    ->where('initialization_vector_base64',
//                        $base64Encoder->encodeString($password->initialization_vector->getValue()))
                    ->where('type', $password->getType())
                    ->etc()
            );
    }

    public function test_member_can_generate_an_otp_for_totp_field(): void
    {
        $this->withoutExceptionHandling();
        /**
         * @var EntryGroup $entryGroup
         * @var User $admin
         * @var User $member
         * @var Entry $entry
         * @var EntryGroupService $entryGroupService
         */
        $entryGroup = EntryGroup::factory()->create();
        [$admin, $member] = User::factory()->count(2)->create();
        $entry = Entry::factory()->make();
        /**
         * @var EncryptionService $encryptionService
         */
        $entryGroupService = app(EntryGroupService::class);

        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroup);

        $this->actingAs($admin);
        dispatch_sync(new AddEntry($entry, $entryGroup, new EntryValidationHandler()));
        $entryGroupService->addUserToGroupAsMember($member, $entryGroup, null, UserFactory::MASTER_PASSWORD);

        /**
         * @var Entry $entry
         */
        $entry = Entry::where('title', $entry->title)->firstOrFail();
        $secret = $this->faker->word();
        $this->postJson(route('entryFields', ['entryGroup' => $entryGroup, 'entry' => $entry]), [
            'type' => TOTP::TYPE,
            'master_password' => UserFactory::MASTER_PASSWORD,
            'value' => $secret,
        ])->assertStatus(200);
        $totp = $entry->TOTPs()->first();

        $this->actingAs($member);

        $this->postJson(route('entryFieldTOTP', ['entryGroup' => $entryGroup, 'entry' => $entry, 'field' => $totp]),
            ['master_password' => UserFactory::MASTER_PASSWORD]
        )
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $field) =>
                $field->has('code')
                    ->has('time')
                    ->has('epoch')
                    ->has('period')
                    ->has('expiresIn')
                    ->has('expiresAt')
                    ->etc()
            );

    }
    public function test_member_can_see_an_decrypted_entry_field_belonged_to_their_group(): void
    {
        /**
         * @var EntryGroup $entryGroup
         * @var User $admin
         * @var User $member
         * @var Entry $entry
         * @var EntryGroupService $entryGroupService
         */
        $entryGroup = EntryGroup::factory()->create();
        [$admin, $member] = User::factory()->count(2)->create();
        $entry = Entry::factory()->make();
        /**
         * @var EncryptionService $encryptionService
         */
        $entryGroupService = app(EntryGroupService::class);

        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroup);

        $this->actingAs($admin);
        dispatch_sync(new AddEntry($entry, $entryGroup, new EntryValidationHandler()));
        $entryGroupService->addUserToGroupAsMember($member, $entryGroup, null, UserFactory::MASTER_PASSWORD);
        $this->actingAs($member);
        /**
         * @var Entry $entry
         */
        $entry = Entry::where('title', $entry->title)->firstOrFail();
        $password_str = $this->faker->password(12, 32);
        $password = $this->getPasswordHelper($admin, $entryGroup, $entry, $password_str);

        /**
         * @var Base64Encoder $base64Encoder
         */
        $base64Encoder = app(Base64Encoder::class);
        $content = $this->postJson(route('entryFieldDecrypted',
            [
                'entryGroup' => $entryGroup,
                'entry' => $entry,
                'field' => $password
            ]),
            [
                'master_password' => UserFactory::MASTER_PASSWORD
            ]
        )->assertStatus(200)->getContent();
        $this->assertEquals(base64_decode($content), $entryGroupService->decryptField($password,  UserFactory::MASTER_PASSWORD));


//            ->assertContent(
//                '"' .
//                $base64Encoder->encodeString($entryGroupService->decryptField($password,  UserFactory::MASTER_PASSWORD))
//                . '"'
//            );
//            ->assertJson(fn (AssertableJson $field)
//            => $field->where('value_decrypted_base64',
//                    $base64Encoder->encodeString($entryGroupService->decryptField($password,  UserFactory::MASTER_PASSWORD))
//                )->etc()
//            );
    }

    public function test_admin_can_add_a_field_to_entry_belonged_to_their_group(): void
    {
        /**
         * @var EntryGroup $entryGroup
         * @var User $admin
         * @var Entry $entry
         * @var EntryGroupService $entryGroupService
         */
        $entryGroup = EntryGroup::factory()->create();
        $admin = User::factory()->create();
        $entry = Entry::factory()->make();
        $entryGroupService = app(EntryGroupService::class);

        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroup);

        $this->actingAs($admin);
        dispatch_sync(new AddEntry($entry, $entryGroup, new EntryValidationHandler()));
        /**
         * @var Entry $entry
         */
        $entry = Entry::where('title', $entry->title)->firstOrFail();
        $password_str = $this->faker->password(12, 32);
        $login = $this->faker->word();

        $entryNumOriginal = Entry::where('entry_id', $entry->entry_id)->firstOrFail()->fields()->count();

        $this->postJson(
            route('entryFields', ['entryGroup' => $entryGroup, 'entry' => $entry]),
            [
                'type' => Password::TYPE,
                'value' => $password_str,
                'login' => $login,
                'title' => '',
                'master_password' => UserFactory::MASTER_PASSWORD
            ]
        )->assertStatus(200);

        $this->assertCount($entryNumOriginal + 1,
            Entry::where('entry_id', $entry->entry_id)->firstOrFail()->fields()
        );
    }

    public function test_moderator_can_add_a_field_to_entry_belonged_to_their_group(): void
    {
        /**
         * @var EntryGroup $entryGroup
         * @var User $admin
         * @var User $moderator
         * @var Entry $entry
         * @var EntryGroupService $entryGroupService
         */
        $entryGroup = EntryGroup::factory()->create();
        [$admin, $moderator] = User::factory()->count(2)->create();
        $entry = Entry::factory()->make();
        $entryGroupService = app(EntryGroupService::class);

        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroup);

        $this->actingAs($admin);
        dispatch_sync(new AddEntry($entry, $entryGroup, new EntryValidationHandler()));
        $entryGroupService->addUserToGroupAsModerator($moderator, $entryGroup, null, UserFactory::MASTER_PASSWORD);
        $this->actingAs($moderator);
        /**
         * @var Entry $entry
         */
        $entry = Entry::where('title', $entry->title)->firstOrFail();
        $password_str = $this->faker->password(12, 32);
        $login = $this->faker->word();

        $entryNumOriginal = Entry::where('entry_id', $entry->entry_id)->firstOrFail()->fields()->count();

        $this->postJson(
            route('entryFields', ['entryGroup' => $entryGroup, 'entry' => $entry]),
            [
                'type' => Password::TYPE,
                'login' => $login,
                'value' => $password_str,
                'master_password' => UserFactory::MASTER_PASSWORD
            ]
        )->assertStatus(200);

        $this->assertCount($entryNumOriginal + 1,
            Entry::where('entry_id', $entry->entry_id)->firstOrFail()->fields()
        );
    }

    public function test_member_cannot_add_a_field_to_entry_belonged_to_their_group(): void
    {
        /**
         * @var EntryGroup $entryGroup
         * @var User $admin
         * @var User $member
         * @var Entry $entry
         * @var EntryGroupService $entryGroupService
         */
        $entryGroup = EntryGroup::factory()->create();
        [$admin, $member] = User::factory()->count(2)->create();
        $entry = Entry::factory()->make();
        $entryGroupService = app(EntryGroupService::class);

        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroup);

        $this->actingAs($admin);
        dispatch_sync(new AddEntry($entry, $entryGroup, new EntryValidationHandler()));
        $entryGroupService->addUserToGroupAsMember($member, $entryGroup, null, UserFactory::MASTER_PASSWORD);
        $this->actingAs($member);
        /**
         * @var Entry $entry
         */
        $entry = Entry::where('title', $entry->title)->firstOrFail();
        $password_str = $this->faker->password(12, 32);
        $login = $this->faker->word();

        $entryNumOriginal = Entry::where('entry_id', $entry->entry_id)->firstOrFail()->fields()->count();
        $this->postJson(
            route('entryFields', ['entryGroup' => $entryGroup, 'entry' => $entry]),
            [
                'type' => Password::TYPE,
                'value' => $password_str,
                'login' => $login,
                'master_password' => UserFactory::MASTER_PASSWORD
            ]
        )->assertStatus(403);

        $this->assertCount($entryNumOriginal,
            Entry::where('entry_id', $entry->entry_id)->firstOrFail()->fields()
        );
    }

    public function test_admin_can_update_an_entry_field_belonged_to_their_group(): void
    {
        $this->withoutExceptionHandling();
        /**
         * @var EntryGroup $entryGroup
         * @var User $admin
         * @var Entry $entry
         * @var EntryGroupService $entryGroupService
         */
        $entryGroup = EntryGroup::factory()->create();
        $admin = User::factory()->create();
        $entry = Entry::factory()->make();
        $entryGroupService = app(EntryGroupService::class);

        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroup);
        $this->actingAs($admin);
        dispatch_sync(new AddEntry($entry, $entryGroup, new EntryValidationHandler()));
        /**
         * @var Entry $entry
         */
        $entry = Entry::where('title', $entry->title)->firstOrFail();
        $password_str = $this->faker->password(12, 32);
        $password = $this->getPasswordHelper($admin, $entryGroup, $entry, $password_str);
        $password_str_new = $password_str . '_new';
        $login_new = $this->faker->word();
        $this->putJson(
            route('entryField', ['entryGroup' => $entryGroup, 'entry' => $entry, 'field' => $password]),
            [
                'login' => $login_new,
                'value' => $password_str_new,
                'master_password' => UserFactory::MASTER_PASSWORD
            ]
        )->assertStatus(200);

        /**
         * @var Password $password_updated
         */
        $password_updated = Password::where('field_id', $password->field_id)->firstOrFail();
        /**
         * @var EntryGroupService $entryGroupService
         */
        $entryGroupService = app(EntryGroupService::class);

        $password_str_updated = $entryGroupService->decryptField($password_updated, UserFactory::MASTER_PASSWORD);

        $this->assertEquals($password_str_new, $password_str_updated);

    }

    public function test_moderator_can_update_an_entry_field_belonged_to_their_group(): void
    {
        /**
         * @var EntryGroup $entryGroup
         * @var User $admin
         * @var User $moderator
         * @var Entry $entry
         * @var EntryGroupService $entryGroupService
         */
        $entryGroup = EntryGroup::factory()->create();
        [$admin, $moderator] = User::factory()->count(2)->create();
        $entry = Entry::factory()->make();
        $entryGroupService = app(EntryGroupService::class);

        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroup);

        $this->actingAs($admin);
        dispatch_sync(new AddEntry($entry, $entryGroup, new EntryValidationHandler()));
        $entryGroupService->addUserToGroupAsModerator($moderator, $entryGroup, null, UserFactory::MASTER_PASSWORD);
        $this->actingAs($moderator);
        /**
         * @var Entry $entry
         */
        $entry = Entry::where('title', $entry->title)->firstOrFail();
        $password_str = $this->faker->password(12, 32);
        $password = $this->getPasswordHelper($admin, $entryGroup, $entry, $password_str);
        $password_str_new = $password_str . '_new';
        $this->putJson(
            route('entryField', ['entryGroup' => $entryGroup, 'entry' => $entry, 'field' => $password]),
            [
                'value' => $password_str_new,
                'master_password' => UserFactory::MASTER_PASSWORD
            ]
        )->assertStatus(200);

        /**
         * @var Password $password_updated
         */
        $password_updated = Password::where('field_id', $password->field_id)->firstOrFail();
        /**
         * @var EncryptionService $encryptionService
         */

        $password_str_updated = $entryGroupService->decryptField($password_updated, UserFactory::MASTER_PASSWORD);

        $this->assertEquals($password_str_new, $password_str_updated);
    }

    public function test_member_cannot_update_an_entry_field_belonged_to_their_group(): void
    {
        /**
         * @var EntryGroup $entryGroup
         * @var User $admin
         * @var User $member
         * @var Entry $entry
         * @var EntryGroupService $entryGroupService
         */
        $entryGroup = EntryGroup::factory()->create();
        [$admin, $member] = User::factory()->count(2)->create();
        $entry = Entry::factory()->make();
        $entryGroupService = app(EntryGroupService::class);

        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroup);

        $this->actingAs($admin);
        dispatch_sync(new AddEntry($entry, $entryGroup, new EntryValidationHandler()));
        $entryGroupService->addUserToGroupAsMember($member, $entryGroup, null, UserFactory::MASTER_PASSWORD);
        $this->actingAs($member);
        /**
         * @var Entry $entry
         */
        $entry = Entry::where('title', $entry->title)->firstOrFail();
        $password_str = $this->faker->password(12, 32);
        $password = $this->getPasswordHelper($admin, $entryGroup, $entry, $password_str);
        $password_str_new = $password_str . '_new';
        $this->putJson(
            route('entryField', ['entryGroup' => $entryGroup, 'entry' => $entry, 'field' => $password]),
            [
                'value' => $password_str_new,
                'master_password' => UserFactory::MASTER_PASSWORD
            ]
        )->assertStatus(403);

        /**
         * @var Password $password_updated
         */
        $password_updated = Password::where('field_id', $password->field_id)->firstOrFail();

        $password_str_updated = $entryGroupService->decryptField($password_updated, UserFactory::MASTER_PASSWORD);

        $this->assertEquals($password_str, $password_str_updated);
    }

    public function test_admin_can_delete_an_entry_field_belonged_to_their_group(): void
    {
        /**
         * @var EntryGroup $entryGroup
         * @var User $admin
         * @var Entry $entry
         * @var EntryGroupService $entryGroupService
         */
        $entryGroup = EntryGroup::factory()->create();
        $admin = User::factory()->create();
        $entry = Entry::factory()->make();
        $entryGroupService = app(EntryGroupService::class);

        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroup);

        $this->actingAs($admin);
        dispatch_sync(new AddEntry($entry, $entryGroup, new EntryValidationHandler()));
        /**
         * @var Entry $entry
         */
        $entry = Entry::where('title', $entry->title)->firstOrFail();
        $password_str = $this->faker->password(12, 32);
        $password = $this->getPasswordHelper($admin, $entryGroup, $entry, $password_str);

        $this->assertTrue(
            Password::where('field_id', $password->field_id)->exists()
        );

        $this->deleteJson(
            route('entryField', [
                'entryGroup' => $entryGroup,
                'entry' => $entry,
                'field' => $password,
                'master_password' => UserFactory::MASTER_PASSWORD,
                ]),
        )->assertStatus(200);

        $this->assertFalse(
            Password::where('field_id', $password->field_id)->exists()
        );
    }

    public function test_moderator_can_delete_an_entry_field_belonged_to_their_group(): void
    {
        /**
         * @var EntryGroup $entryGroup
         * @var User $admin
         * @var User $moderator
         * @var Entry $entry
         * @var EntryGroupService $entryGroupService
         */
        $entryGroup = EntryGroup::factory()->create();
        [$admin, $moderator] = User::factory()->count(2)->create();
        $entry = Entry::factory()->make();
        $entryGroupService = app(EntryGroupService::class);

        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroup);

        $this->actingAs($admin);
        dispatch_sync(new AddEntry($entry, $entryGroup, new EntryValidationHandler()));
        $entryGroupService->addUserToGroupAsModerator($moderator, $entryGroup, null, UserFactory::MASTER_PASSWORD);
        $this->actingAs($moderator);
        /**
         * @var Entry $entry
         */
        $entry = Entry::where('title', $entry->title)->firstOrFail();
        $password_str = $this->faker->password(12, 32);
        $password = $this->getPasswordHelper($admin, $entryGroup, $entry, $password_str);

        $this->assertTrue(
            Password::where('field_id', $password->field_id)->exists()
        );

        $this->deleteJson(
            route('entryField', [
                'entryGroup' => $entryGroup,
                'entry' => $entry,
                'field' => $password,
                'master_password' => UserFactory::MASTER_PASSWORD,
                ]),
        )->assertStatus(200);

        $this->assertFalse(
            Password::where('field_id', $password->field_id)->exists()
        );
    }

    public function test_member_cannot_delete_an_entry_field_belonged_to_their_group(): void
    {
        /**
         * @var EntryGroup $entryGroup
         * @var User $admin
         * @var User $member
         * @var Entry $entry
         * @var EntryGroupService $entryGroupService
         */
        $entryGroup = EntryGroup::factory()->create();
        [$admin, $member] = User::factory()->count(2)->create();
        $entry = Entry::factory()->make();
        $entryGroupService = app(EntryGroupService::class);

        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroup);

        $this->actingAs($admin);
        dispatch_sync(new AddEntry($entry, $entryGroup, new EntryValidationHandler()));
        $entryGroupService->addUserToGroupAsMember($member, $entryGroup, null, UserFactory::MASTER_PASSWORD);
        $this->actingAs($member);
        /**
         * @var Entry $entry
         */
        $entry = Entry::where('title', $entry->title)->firstOrFail();
        $password_str = $this->faker->password(12, 32);
        $password = $this->getPasswordHelper($admin, $entryGroup, $entry, $password_str);

        $this->assertTrue(
            Password::where('field_id', $password->field_id)->exists()
        );

        $this->deleteJson(
            route('entryField', ['entryGroup' => $entryGroup, 'entry' => $entry, 'field' => $password]),
        )->assertStatus(403);

        $this->assertTrue(
            Password::where('field_id', $password->field_id)->exists()
        );
    }

    public function test_moderator_can_add_file_field_to_their_entry(): void
    {
        /**
         * @var EntryGroup $entryGroup
         * @var User $admin
         * @var User $moderator
         * @var Entry $entry
         * @var EntryGroupService $entryGroupService
         */
        $entryGroup = EntryGroup::factory()->create();
        $admin = User::factory()->create();
        $moderator = User::factory()->create();
        $entry = Entry::factory()->make();
        $entryGroupService = app(EntryGroupService::class);

        $this->actingAs($admin);
        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroup);
        $entryGroupService->addUserToGroupAsModerator($moderator, $entryGroup, null, UserFactory::MASTER_PASSWORD);
        $this->actingAs($moderator);

        dispatch_sync(new AddEntry($entry, $entryGroup, new EntryValidationHandler()));
        /**
         * @var Entry $entry
         */
        $entry = Entry::where('title', $entry->title)->firstOrFail();

        $content = $this->faker->text;
        $file = UploadedFile::fake()->createWithContent('test_file.txt', $content);
        $this->postJson(route('entryFields', ['entryGroup' => $entryGroup, 'entry' => $entry]), [
            'type' => File::TYPE,
            'master_password' => UserFactory::MASTER_PASSWORD,
            'file' => $file
        ])->assertStatus(200);

        $base64Encoder = app(Base64Encoder::class);

        $entryFields = $this->getJson(route('entryFields', ['entryGroup' => $entryGroup, 'entry' => $entry]))
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $fields)
                => $fields->has(1)->first(fn (AssertableJson $entry)
                    => $entry->where('type', File::TYPE)
                        ->etc()
                )
            )
            ->json();

        $filed_id = $entryFields[0]['field_id'];


        $resp = $this->postJson(
            route('entryFieldDecrypted',
                ['entryGroup' => $entryGroup, 'entry' => $entry, 'field' => $filed_id]
            ),
            ['master_password' => UserFactory::MASTER_PASSWORD]
        )
            ->assertStatus(200)
            ->content();
//            ->assertJson(fn (AssertableJson $fieldDecrypted)
//                => $fieldDecrypted->where('value_decrypted_base64', $base64Encoder->encodeString($content))
//                    ->where('field.file_size', fn ($file_size) => $file_size !== '0')
//                    ->where('field.file_name', fn ($file_size) => $file_size !== '')
//                    ->etc()
//            );
        $this->assertEquals(base64_decode($resp), $content);

    }

    public function test_moderator_can_add_a_totp_field_to_entry_belonged_to_their_group(): void
    {
        /**
         * @var EntryGroup $entryGroup
         * @var User $admin
         * @var User $moderator
         * @var Entry $entry
         * @var EntryGroupService $entryGroupService
         */
        $entryGroup = EntryGroup::factory()->create();
        [$admin, $moderator] = User::factory()->count(2)->create();
        $entry = Entry::factory()->make();
        $entryGroupService = app(EntryGroupService::class);

        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroup);

        $this->actingAs($admin);
        dispatch_sync(new AddEntry($entry, $entryGroup, new EntryValidationHandler()));
        $entryGroupService->addUserToGroupAsModerator($moderator, $entryGroup, null, UserFactory::MASTER_PASSWORD);
        $this->actingAs($moderator);
        /**
         * @var Entry $entry
         */
        $entry = Entry::where('title', $entry->title)->firstOrFail();
        $totpSecret = $this->faker->password(12, 32);

        $entryNumOriginal = Entry::where('entry_id', $entry->entry_id)->firstOrFail()->fields()->count();

        /**
         * @var TOTPHashAlgorithm $hashAlgorithm
         */
        $hashAlgorithm = Arr::random(TOTPHashAlgorithm::cases());
        $timeout = $this->faker->numberBetween(1, 100);
        $title = 'totp_' . $this->faker->word();
        $this->postJson(
            route('entryFields', ['entryGroup' => $entryGroup, 'entry' => $entry]),
            [
                'title' => $title,
                'type' => TOTP::TYPE,
                'totp_hash_algorithm' => $hashAlgorithm->value,
                'totp_timeout' => $timeout,
                'value' => $totpSecret,
                'master_password' => UserFactory::MASTER_PASSWORD,
            ]
        )->assertStatus(Response::HTTP_OK);

        $fields = Entry::where('entry_id', $entry->entry_id)->firstOrFail()->fields();
        $this->assertCount($entryNumOriginal + 1,
            $fields
        );

        /**
         * @var TOTP $totpFieldEntry
         */
        $totpFieldEntry = $fields->where('title', $title)->firstOrFail();

        $this->assertEquals(TOTP::TYPE, $totpFieldEntry->type);
        $this->assertEquals($hashAlgorithm, $totpFieldEntry->totp_hash_algorithm);
        $this->assertEquals($timeout, $totpFieldEntry->totp_timeout->getValue());
    }

}
