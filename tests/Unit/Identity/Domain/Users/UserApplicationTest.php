<?php

namespace Identity\Domain\Users;

use Carbon\Carbon;
use Identity\Domain\User\Models\User;
use Identity\Domain\UserApplication\Models\Attributes\IsOfflineDatabaseMode;
use Identity\Domain\UserApplication\Models\Attributes\UserApplicationId;
use Identity\Domain\UserApplication\Models\Attributes\IsOfflineDatabaseRequiredUpdate;
use Identity\Domain\UserApplication\Models\Attributes\IsRsaPrivateRequiredUpdate;
use Identity\Domain\UserApplication\Models\UserApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserApplicationTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_a_user_application_constructor(): void
    {
        /**
         * @var User $sysAdmin
         */
        $sysAdmin = User::factory()->systemAdministrator()->create();
        /**
         * @var UserApplication $userApplication
         */
        $userApplication = UserApplication::factory()->belongToUser($sysAdmin)->randomApplicationId()->create();

        $this->assertInstanceOf(UserApplication::class, $userApplication);
    }
    public function test_a_user_application_has_user(): void
    {
        /**
         * @var User $sysAdmin
         */
        $sysAdmin = User::factory()->systemAdministrator()->create();
        /**
         * @var UserApplication $userApplication
         */
        $userApplication = UserApplication::factory()->belongToUser($sysAdmin)->create();

        $this->assertEquals($userApplication->user()->first()->user_id->getValue(), $sysAdmin->user_id->getValue());
    }

    public function test_a_user_application_has_application_id(): void
    {
        /**
         * @var UserApplication $userApplication
         */
        $userApplication = UserApplication::factory()->createUser()->create();
        $this->assertInstanceOf(UserApplicationId::class, $userApplication->user_application_id);
    }
    public function test_a_user_application_has_is_offline_database_mode(): void
    {
        /**
         * @var UserApplication $userApplication
         */
        $userApplication = UserApplication::factory()->createUser()->create();
        $this->assertInstanceOf(IsOfflineDatabaseMode::class, $userApplication->is_offline_database_mode);
    }

    public function test_a_user_application_has_rsa_private_fetched_at(): void
    {
        /**
         * @var UserApplication $userApplication
         */
        $userApplication = UserApplication::factory()->createUser()->create();
        $userApplication->rsa_private_fetched_at = Carbon::now();
        $userApplication->save();
        $this->assertInstanceOf(Carbon::class, $userApplication->rsa_private_fetched_at);
    }
    public function test_a_user_application_has_offline_database_fetched_at(): void
    {
        /**
         * @var UserApplication $userApplication
         */
        $userApplication = UserApplication::factory()->createUser()->create();
        $userApplication->offline_database_fetched_at = Carbon::now();
        $userApplication->save();
        $this->assertInstanceOf(Carbon::class, $userApplication->offline_database_fetched_at);
    }

    public function test_a_user_application_has_is_offline_database_required_update(): void
    {
        /**
         * @var UserApplication $userApplication
         */
        $userApplication = UserApplication::factory()->createUser()->create();
        $this->assertInstanceOf(IsOfflineDatabaseRequiredUpdate::class,
            $userApplication->is_offline_database_required_update);
    }
    public function test_a_user_application_has_is_rsa_private_required_update(): void
    {
        /**
         * @var UserApplication $userApplication
         */
        $userApplication = UserApplication::factory()->createUser()->create();
        $this->assertInstanceOf(IsRsaPrivateRequiredUpdate::class,
            $userApplication->is_rsa_private_required_update);
    }
}
