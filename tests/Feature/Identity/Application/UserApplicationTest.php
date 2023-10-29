<?php

namespace Identity\Application;

use Identity\Domain\User\Models\User;
use Identity\Domain\UserApplication\Models\Attributes\ClientId;
use Identity\Domain\UserApplication\Models\Attributes\IsOfflineDatabaseRequiredUpdate;
use Identity\Domain\UserApplication\Models\Attributes\IsRsaPrivateRequiredUpdate;
use Identity\Domain\UserApplication\Models\Attributes\IsOfflineDatabaseMode;
use Identity\Domain\UserApplication\Models\UserApplication;
use Illuminate\Broadcasting\BroadcastEvent;
use Illuminate\Contracts\Broadcasting\Factory;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\Fluent\AssertableJson;
use PasswordBroker\Application\Events\EntryGroupCreated;
use PasswordBroker\Application\Services\EncryptionService;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Services\AddEntryGroup;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Mime\Encoder\Base64Encoder;
use Tests\Feature\PasswordBroker\Application\EntryGroupRandomAttributes;
use Tests\TestCase;

class UserApplicationTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;
    use EntryGroupRandomAttributes;

    public function test_a_system_administrator_can_fetch_cbc_salt(): void
    {
        /**
         * @var User $user
         */
        $user = User::factory()->systemAdministrator()->create();
        $this->actingAs($user);
        /**
         * @var EncryptionService $encryptionService
         */
        $encryptionService = app(EncryptionService::class);
        $cbcSalt = $encryptionService->getCbcSalt();
        /**
         * @var Base64Encoder $base64
         */
        $base64 = app(Base64Encoder::class);
        $this->getJson(route('getCbcSalt'))
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json)
                => $json->where('salt_base64', $base64->encodeString($cbcSalt))
                    ->where('timestamp', fn($timestamp) => is_numeric($timestamp))
            );
    }

    public function test_after_updating_an_entry_group_a_user_application_marked_as_offline_database_required_update(): void
    {
        $this->withoutExceptionHandling();
//        Queue::fake()->except([AddEntryGroup::class]);
        /**
         * @var User $admin
         */
        $admin = User::factory()->systemAdministrator()->create();

        /**
         * @var UserApplication $userApplication
         */
        $userApplication = UserApplication::factory()
            ->belongToUser($admin)->create(['is_offline_database_mode' => new IsOfflineDatabaseMode(true)]);

        $this->actingAs($admin);
        $this->getJson(route('userApplicationIsOfflineDatabaseRequiredUpdate', ['userApplication' => $userApplication]))
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) => $json->where('status', false));


        $attributes = $this->getEntryGroupRandomAttributes();
        unset($attributes['materialized_path']);
        $this->postJson(route('entryGroups'), $attributes)->assertStatus(200);

        $this->assertDatabaseHas(
            app(EntryGroup::class)->getTable(),
            $attributes,
            app(EntryGroup::class)->getConnection()->getName()
        );
        $userApplication->refresh();
        $this->assertTrue($userApplication->is_offline_database_required_update->getValue());
    }

    public function test_a_system_administrator_can_switch_a_database_offline_mode_for_self_tokens(): void
    {

        /**
         * @var UserApplication $userApplication
         */
        $userApplication = UserApplication::factory()->createUser()->create();

        /**
         * @var User $user
         */
        $user = $userApplication->user()->first();
        $this->actingAs($user);

        $this->getJson(route('userApplicationOfflineDatabaseMode', ['userApplication' => $userApplication]))
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) => $json->where('status', false));

        $this->putJson(route('userApplicationOfflineDatabaseMode', ['userApplication' => $userApplication]),
                ['status' => true]
            )->assertStatus(200);

        $this->getJson(route('userApplicationOfflineDatabaseMode', ['userApplication' => $userApplication]))
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) => $json->where('status', true));
    }

    public function test_a_user_can_get_or_create_a_user_application(): void
    {
//        $this->withoutExceptionHandling();
        /**
         * @var User $user
         */
        $user = User::factory()->create();
        $this->actingAs($user);
        $clientId = new ClientId(Uuid::uuid4());

        $application_id = null;

        $this->postJson(route('userApplications'), ['clientId' => $clientId->getValue()])
            ->assertStatus(200)
            ->assertJson(static function (AssertableJson $json) use (&$application_id, $clientId) {
                     $json
                         ->where('userApplication.client_id', $clientId->getValue())
                         ->where('userApplication.user_application_id',
                            static function ($json_application_id) use (&$application_id) {
                                $application_id = $json_application_id;
                                return Uuid::isValid($application_id);
                            });
                    }
            );

        $this->assertEquals(
            1,
            $user->applications()->where('client_id', $clientId->getValue())->count()
        );

        $this->postJson(route('userApplications'), ['clientId' => $clientId->getValue()])
            ->assertStatus(200)
            ->assertJson(static fn (AssertableJson $json)
                => $json->where('userApplication.client_id', $clientId->getValue())
                    ->where('userApplication.user_application_id', $application_id)
            );
    }

    public function test_a_user_can_check_is_their_offline_database_required_update(): void
    {
        /**
         * @var UserApplication $userApplication
         */
        $userApplication = UserApplication::factory()->createUser()->create();

        /**
         * @var User $user
         */
        $user = $userApplication->user()->first();
        $this->actingAs($user);

        $userApplication->is_offline_database_required_update = new IsOfflineDatabaseRequiredUpdate(false);
        $userApplication->save();

        $this->getJson(route('userApplicationIsOfflineDatabaseRequiredUpdate', ['userApplication' => $userApplication]))
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) => $json->where('status', false));

        $userApplication->is_offline_database_required_update = new IsOfflineDatabaseRequiredUpdate(true);
        $userApplication->save();

        $this->getJson(route('userApplicationIsOfflineDatabaseRequiredUpdate', ['userApplication' => $userApplication]))
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) => $json->where('status', true));
    }

    public function test_a_user_can_check_is_rsa_private_required_update(): void
    {
        /**
         * @var UserApplication $userApplication
         */
        $userApplication = UserApplication::factory()->createUser()->create();

        /**
         * @var User $user
         */
        $user = $userApplication->user()->first();
        $this->actingAs($user);

        $userApplication->is_rsa_private_required_update = new IsRsaPrivateRequiredUpdate(false);
        $userApplication->save();

        $this->getJson(route('userApplicationIsRsaPrivateRequiredUpdate', ['userApplication' => $userApplication]))
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) => $json->where('status', false));

        $userApplication->is_rsa_private_required_update = new IsRsaPrivateRequiredUpdate(true);
        $userApplication->save();

        $this->getJson(route('userApplicationIsRsaPrivateRequiredUpdate', ['userApplication' => $userApplication]))
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) => $json->where('status', true));
    }

    public function test_a_user_can_get_a_user_application(): void
    {
        $this->withoutExceptionHandling();
        /**
         * @var User $user
         */
        $user = User::factory()->create();
        $this->actingAs($user);
        $clientId = new ClientId(Uuid::uuid4());
        /**
         * @var UserApplication $userApplication
         */
        $userApplication = UserApplication::factory()->clientId($clientId)->belongToUser($user)->create();

        $this->getJson(route('userApplication', ['userApplication' => $userApplication->client_id->getValue()]))
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json)
                => $json->where('userApplication.client_id', $clientId->getValue())
                    ->where('userApplication.user_application_id', $userApplication->user_application_id->getValue())
                    ->etc()
            );

        $this->getJson(route('userApplication', ['userApplication' => $userApplication->user_application_id->getValue()]))
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json)
                => $json->where('userApplication.client_id', $clientId->getValue())
                    ->where('userApplication.client_id', $userApplication->client_id->getValue())
                    ->etc()
            );
    }
}
