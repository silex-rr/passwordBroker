<?php

namespace Tests\Feature\PasswordBroker\Application;

use Identity\Application\Services\RsaService;
use Identity\Domain\User\Models\User;
use Identity\Infrastructure\Factories\User\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use PasswordBroker\Application\Services\EncryptionService;
use PasswordBroker\Application\Services\EntryGroupService;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Fields\Password;
use PasswordBroker\Domain\Entry\Services\AddEntry;
use PasswordBroker\Infrastructure\Validation\Handlers\EntryValidationHandler;
use Symfony\Component\Mime\Encoder\Base64Encoder;
use Tests\TestCase;

class EntryFieldsTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

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
        $entry = Entry::factory()->make(['entry_group_id' => null]);
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
        $entry = Entry::factory()->make(['entry_group_id' => null]);
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
        $entry = Entry::factory()->make(['entry_group_id' => null]);
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

        $this->postJson(
            route('entryFields', ['entryGroup' => $entryGroup, 'entry' => $entry]),
            [
                'type' => Password::TYPE,
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
        $entry = Entry::factory()->make(['entry_group_id' => null]);
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
        $entry = Entry::factory()->make(['entry_group_id' => null]);
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
        $entry = Entry::factory()->make(['entry_group_id' => null]);
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
                        ->where('encrypted_value_base64',
                            $base64Encoder->encodeString($password->value_encrypted->getValue()))
                        ->where('initialization_vector_base64',
                            $base64Encoder->encodeString($password->initialization_vector->getValue()))
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
        $entry = Entry::factory()->make(['entry_group_id' => null]);
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
                        ->where('encrypted_value_base64',
                            $base64Encoder->encodeString($password->value_encrypted->getValue()))
                        ->where('initialization_vector_base64',
                            $base64Encoder->encodeString($password->initialization_vector->getValue()))
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
        $entry = Entry::factory()->make(['entry_group_id' => null]);
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
                        ->where('encrypted_value_base64',
                            $base64Encoder->encodeString($password->value_encrypted->getValue()))
                        ->where('initialization_vector_base64',
                            $base64Encoder->encodeString($password->initialization_vector->getValue()))
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
        $entry = Entry::factory()->make(['entry_group_id' => null]);
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
                    ->where('encrypted_value_base64',
                        $base64Encoder->encodeString($password->value_encrypted->getValue()))
                    ->where('initialization_vector_base64',
                        $base64Encoder->encodeString($password->initialization_vector->getValue()))
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
        $entry = Entry::factory()->make(['entry_group_id' => null]);
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
                    ->where('encrypted_value_base64',
                        $base64Encoder->encodeString($password->value_encrypted->getValue()))
                    ->where('initialization_vector_base64',
                        $base64Encoder->encodeString($password->initialization_vector->getValue()))
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
        $entry = Entry::factory()->make(['entry_group_id' => null]);
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

        $base64Encoder = app(Base64Encoder::class);

        $this->getJson(route('entryField', ['entryGroup' => $entryGroup, 'entry' => $entry, 'field' => $password]))
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $field)
                => $field->where('field_id', $password->field_id->getValue())
                    ->where('encrypted_value_base64',
                        $base64Encoder->encodeString($password->value_encrypted->getValue()))
                    ->where('initialization_vector_base64',
                        $base64Encoder->encodeString($password->initialization_vector->getValue()))
                    ->where('type', $password->getType())
                    ->etc()
            );
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
        $entry = Entry::factory()->make(['entry_group_id' => null]);
        $entryGroupService = app(EntryGroupService::class);

        $entryGroupService->addUserToGroupAsAdmin($admin, $entryGroup);

        $this->actingAs($admin);
        dispatch_sync(new AddEntry($entry, $entryGroup, new EntryValidationHandler()));
        /**
         * @var Entry $entry
         */
        $entry = Entry::where('title', $entry->title)->firstOrFail();
        $password_str = $this->faker->password(12, 32);

        $entryNumOriginal = Entry::where('entry_id', $entry->entry_id)->firstOrFail()->fields()->count();

        $this->postJson(
            route('entryFields', ['entryGroup' => $entryGroup, 'entry' => $entry]),
            [
                'type' => Password::TYPE,
                'value' => $password_str,
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
        $entry = Entry::factory()->make(['entry_group_id' => null]);
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

        $entryNumOriginal = Entry::where('entry_id', $entry->entry_id)->firstOrFail()->fields()->count();

        $this->postJson(
            route('entryFields', ['entryGroup' => $entryGroup, 'entry' => $entry]),
            [
                'type' => Password::TYPE,
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
        $entry = Entry::factory()->make(['entry_group_id' => null]);
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

        $entryNumOriginal = Entry::where('entry_id', $entry->entry_id)->firstOrFail()->fields()->count();

        $this->postJson(
            route('entryFields', ['entryGroup' => $entryGroup, 'entry' => $entry]),
            [
                'type' => Password::TYPE,
                'value' => $password_str,
                'master_password' => UserFactory::MASTER_PASSWORD
            ]
        )->assertStatus(403);

        $this->assertCount($entryNumOriginal,
            Entry::where('entry_id', $entry->entry_id)->firstOrFail()->fields()
        );
    }

    public function test_admin_can_update_an_entry_field_belonged_to_their_group(): void
    {
        /**
         * @var EntryGroup $entryGroup
         * @var User $admin
         * @var Entry $entry
         * @var EntryGroupService $entryGroupService
         */
        $entryGroup = EntryGroup::factory()->create();
        $admin = User::factory()->create();
        $entry = Entry::factory()->make(['entry_group_id' => null]);
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
        $encryptionService = app(EncryptionService::class);
        /**
         * @var RsaService $rsaService
         */
        $rsaService = app(RsaService::class);

        $encrypted_aes_password = $admin->userOf()->where('entry_group_id', $entryGroup->entry_group_id)->firstOrFail()->encrypted_aes_password;
        $privateKey = $rsaService->getUserPrivateKey($admin->user_id, UserFactory::MASTER_PASSWORD);
        $decrypted_aes_password = $privateKey->decrypt($encrypted_aes_password);

        $password_str_updated = $encryptionService->decryptField($password_updated, $decrypted_aes_password);

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
        $entry = Entry::factory()->make(['entry_group_id' => null]);
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
        $encryptionService = app(EncryptionService::class);
        /**
         * @var RsaService $rsaService
         */
        $rsaService = app(RsaService::class);

        $encrypted_aes_password = $moderator->userOf()->where('entry_group_id', $entryGroup->entry_group_id)->firstOrFail()->encrypted_aes_password;

        $privateKey = $rsaService->getUserPrivateKey($moderator->user_id, UserFactory::MASTER_PASSWORD);
        $decrypted_aes_password = $privateKey->decrypt($encrypted_aes_password);

        $password_str_updated = $encryptionService->decryptField($password_updated, $decrypted_aes_password);

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
        $entry = Entry::factory()->make(['entry_group_id' => null]);
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
        /**
         * @var EncryptionService $encryptionService
         */
        $encryptionService = app(EncryptionService::class);
        /**
         * @var RsaService $rsaService
         */
        $rsaService = app(RsaService::class);

        $encrypted_aes_password = $member->userOf()->where('entry_group_id', $entryGroup->entry_group_id)->firstOrFail()->encrypted_aes_password;
        $privateKey = $rsaService->getUserPrivateKey($member->user_id, UserFactory::MASTER_PASSWORD);
        $decrypted_aes_password = $privateKey->decrypt($encrypted_aes_password);

        $password_str_updated = $encryptionService->decryptField($password_updated, $decrypted_aes_password);

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
        $entry = Entry::factory()->make(['entry_group_id' => null]);
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
            route('entryField', ['entryGroup' => $entryGroup, 'entry' => $entry, 'field' => $password]),
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
        $entry = Entry::factory()->make(['entry_group_id' => null]);
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
            route('entryField', ['entryGroup' => $entryGroup, 'entry' => $entry, 'field' => $password]),
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
        $entry = Entry::factory()->make(['entry_group_id' => null]);
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

    /**
     * @param User $admin
     * @param EntryGroup $entryGroup
     * @param Entry $entry
     * @return Password
     */
    public function getPasswordHelper(User $admin, EntryGroup $entryGroup, Entry $entry, string $password_str): Password
    {
        /**
         * @var EncryptionService $encryptionService
         */
        $encryptionService = app(EncryptionService::class);
        /**
         * @var RsaService $rsaService
         */
        $rsaService = app(RsaService::class);
        $iv = $encryptionService->generateInitializationVector();
        $encrypted_aes_password = $admin->userOf()->where('entry_group_id', $entryGroup->entry_group_id)->firstOrFail()->encrypted_aes_password;
        $privateKey = $rsaService->getUserPrivateKey($admin->user_id, UserFactory::MASTER_PASSWORD);
        $decrypted_aes_password = $privateKey->decrypt($encrypted_aes_password);

        $password_str_encrypted = $encryptionService->encrypt($password_str, $decrypted_aes_password, $iv);

        $password = $entry->addPassword($admin->user_id, $password_str_encrypted, $iv);

        $this->assertCount(1,
            Entry::where('entry_id', $entry->entry_id)->firstOrFail()->fields()
        );
        return $password;
    }


}
