<?php

namespace System\Application;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Identity\Domain\User\Models\User;
use Illuminate\Bus\Dispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Fluent\AssertableJson;
use System\Application\Mail\BackupEmail;
use System\Application\Services\BackupService;
use System\Domain\Backup\Models\Attributes\BackupState;
use System\Domain\Backup\Models\Backup;
use System\Domain\Backup\Service\CreateBackup;
use System\Domain\Settings\Models\Attributes\Backup\Email;
use System\Domain\Settings\Models\Attributes\Backup\Enable;
use System\Domain\Settings\Models\Attributes\Backup\Password;
use System\Domain\Settings\Models\BackupSetting;
use Tests\TestCase;
use ZipArchive;

class BackupTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_only_an_administrator_can_see_list_of_backups(): void
    {
        $admin = User::factory()->systemAdministrator()->create();
        Backup::factory()->count(100)->create();

        $this->actingAs($admin);

        $this->getJson(route('system_backups', ['perPage' => 20]))->assertStatus(200)
            ->assertJson(fn(AssertableJson $json) => $json->has('data', 20)
                ->etc()
            );

        $user = User::factory()->create();

        $this->actingAs($user)->getJson(route('system_backups'))->assertStatus(403);
    }

    public function test_an_email_can_be_send_after_backup_creation(): void
    {
        $user = User::factory()->systemAdministrator()->create();
        $this->actingAs($user);
        Mail::fake();

        /**
         * @var BackupSetting $backupSetting
         */
        $backupSetting = BackupSetting::firstOrCreate([
            'key' => BackupSetting::TYPE,
            'type' => BackupSetting::TYPE,
        ]);

        $backupSetting->setEmailEnable(Enable::fromNative(true));
        $backupSetting->setEmail(Email::fromNative('some@email.com'));
        $backupSetting->save();

        /**
         * @var BackupService $backupService
         */
        $backupService = app(BackupService::class);
        /**
         * @var Dispatcher $dispatcher
         */
        $dispatcher = app(Dispatcher::class);

        /**
         * @var Backup $backup
         */
        $backup = $dispatcher->dispatchSync(
            new CreateBackup(backup: new Backup(), backupService: $backupService)
        );
        $this->assertDatabaseHas($backup->getTableFullName(), ['backup_id' => $backup->backup_id->getValue()]);
        /**
         * @var Backup $backup
         */
        $backup = Backup::where(['backup_id' => $backup->backup_id->getValue()])->get()->firstorFail();

        Mail::assertSent(BackupEmail::class, function (BackupEmail $mail) use ($backup) {
//            $fileData = Storage::disk('system_backup')->get($backup->file_name->getValue());
            $attachments = $mail->attachments();
            $this->assertCount(1, $attachments);
            $this->assertEquals($attachments[0]->as, $backup->file_name->getValue());
            return true;
        });
    }

    public function test_an_administrator_can_create_a_backup(): void
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->systemAdministrator()->create();
        $carbon_now = Carbon::now();
        $carbon_now->sub(
            CarbonInterval::create(years: 0, months: 0, weeks: 0, days: 0, hours: 0, minutes: 0, seconds: 10)
        );
        $route_create_name = 'system_backups';
        $route_create = route($route_create_name);

        $archivePassword = $this->faker->password();

        /**
         * @var BackupSetting $backupSetting
         */
        $backupSetting = BackupSetting::firstOrCreate([
            'key' => BackupSetting::TYPE,
            'type' => BackupSetting::TYPE,
        ]);

        $backupSetting->setArchivePassword(Password::fromNative($archivePassword));
        $backupSetting->save();

        $this->actingAs($user);

        $backup_id = '';

        $setBackupId = static function ($v) use (&$backup_id): bool {
            $backup_id = $v;
            return !empty($v);
        };

        $this->postJson($route_create)->assertStatus(200)
            ->assertJson(function (AssertableJson $json) use ($setBackupId) {
                $json->where('state', BackupState::AWAIT->value)
                    ->where('backup_id', $setBackupId)
                    ->etc();
                });

        $route_get_name = 'system_backup';
        $route_get = route($route_get_name, ['backup' => $backup_id]);

        $backup_size = 0;
        $setBackupSize = static function ($v) use (&$backup_size): bool {
            $backup_size = $v;
            return is_numeric($v) && $v > 0;
        };
        $backup_file_name = '';
        $setBackupFileName = static function ($v) use (&$backup_file_name): bool {
            $backup_file_name = $v;
            return !empty($backup_file_name);
        };
        $backup_password = '';
        $setBackupPassword = static function ($v) use (&$backup_password): bool {
            $backup_password = $v;
            return !empty($backup_password);
        };


        $this->get($route_get)->assertStatus(200)
            ->assertJson(function (AssertableJson $json) use ($setBackupSize, $setBackupFileName, $setBackupPassword, $backup_id, $carbon_now) {
                $json->where('size', $setBackupSize)
                    ->where('file_name', $setBackupFileName)
                    ->where('backup_id', $backup_id)
                    ->where('password', $setBackupPassword)
                    ->where('state', BackupState::CREATED->value)
                    ->where('backup_created', fn($bc) => new Carbon($bc) >= $carbon_now)
                    ->etc();
            });

        /**
         * @var Backup $backup
         */
        $backup = app(Backup::class);
        $this->assertDatabaseHas($backup->getTableFullName(), [
            'file_name' => $backup_file_name,
            'size' => $backup_size
        ]);
        $this->assertTrue(Storage::disk('system_backup')->exists($backup_file_name));
        $content = Storage::disk('system_backup')->get($backup_file_name);

        $tmp = tmpfile();
        $tmp_path = stream_get_meta_data($tmp)['uri'];
        fwrite($tmp, $content);
        $fileInfo = fstat($tmp);
        $fileSize = $fileInfo['size'];
        $this->assertEquals($fileSize, $backup_size);
        $zipArchive = new ZipArchive();
        $zipArchive->open($tmp_path, ZipArchive::RDONLY);
        $zipArchive->setPassword($backup_password);
        $hasKeys = false;
        $hasSalt = false;
        $hasDatabase = false;
        $hasEnv = false;
//        $dataBaseName = '';

        for ($i = $zipArchive->numFiles; --$i >= 0;) {
            $nameIndex = $zipArchive->getNameIndex($i);

            if (!$hasKeys && preg_match('/^identity_keys\/(.+)$/', $nameIndex)) {
                $hasKeys = true;
            }
            if (!$hasSalt && preg_match('/^cbc_salt\/(.+)$/', $nameIndex)) {
                $hasSalt = true;
            }
            if (!$hasDatabase && preg_match('/^database_(.+)$/', $nameIndex)) {
                $hasDatabase = true;
            }
            if (!$hasEnv && preg_match('/^\.env$/', $nameIndex)) {
                $env_content_original = file_get_contents(base_path('.env'));
                $env_content = $zipArchive->getFromIndex($i);
                $hasEnv = $env_content_original === $env_content;
            }
        }
        $this->assertTrue($hasKeys, 'User\'s Private Keys were not found in the backup');
        $this->assertTrue($hasSalt, 'Salt were not found in the backup');
        $this->assertTrue($hasDatabase, 'Database were not found in the backup');
        $this->assertTrue($hasEnv, '.env files were not found in the backup');

        fclose($tmp);
    }

}
