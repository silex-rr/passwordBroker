<?php

namespace System\Application;

use Carbon\Carbon;
use Identity\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Fluent\AssertableJson;
use System\Domain\Backup\Models\Attributes\BackupState;
use System\Domain\Backup\Models\Backup;
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
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('data', 20)
                    ->etc()
            );

        $user = User::factory()->create();

        $this->actingAs($user)->getJson(route('system_backups'))->assertStatus(403);
    }
    public function test_an_administrator_can_create_a_backup(): void
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->systemAdministrator()->create();
        $carbon_now = Carbon::now();
        $route_create_name = 'system_backups';
        $route_create = route($route_create_name);

        $this->actingAs($user);

        $backup_id = '';

        $setBackupId = static function ($v) use (&$backup_id): bool
        {
            $backup_id = $v;
            return !empty($v);
        };

        $this->postJson($route_create)->assertStatus(200)
            ->assertJson(function (AssertableJson $json) use ($setBackupId) {
                    $json->where('state', BackupState::AWAIT->value)
                        ->where('backup_id', $setBackupId)
                        ->etc();
                }
            );

        $route_get_name = 'system_backup';
        $route_get = route($route_get_name, ['backup' => $backup_id]);

        $backup_size = 0;
        $setBackupSize = static function ($v) use (&$backup_size): bool
        {
            $backup_size = $v;
            return is_numeric($v) && $v > 0;
        };
        $backup_file_name = '';
        $setBackupFileName = static function ($v) use (&$backup_file_name): bool
        {
            $backup_file_name = $v;
            return !empty($backup_file_name);
        };


        $this->get($route_get)->assertStatus(200)
            ->assertJson(function (AssertableJson $json) use ($setBackupSize, $setBackupFileName, $backup_id, $carbon_now) {
                $json->where('size', $setBackupSize)
                    ->where('file_name', $setBackupFileName)
                    ->where('backup_id', $backup_id)
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
//                $dataBaseName = $nameIndex;
            }
            if (!$hasEnv && preg_match('/^\.env/', $nameIndex)) {
                $hasEnv = true;
            }
        }
        $this->assertTrue($hasKeys, 'User\'s Private Keys were not found in the backup');
        $this->assertTrue($hasSalt, 'Salt were not found in the backup');
        $this->assertTrue($hasDatabase, 'Database were not found in the backup');
        $this->assertTrue($hasEnv, '.env files were not found in the backup');

        fclose($tmp);
    }

}
