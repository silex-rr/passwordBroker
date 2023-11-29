<?php

namespace System\Application\Providers;

use App\Common\Application\Traits\ProviderMergeConfigRecursion;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use System\Domain\Backup\Models\Backup;
use System\Domain\Settings\Models\BackupSetting;

class SystemServiceProvider extends ServiceProvider
{
    use ProviderMergeConfigRecursion;
    private string $migrations_dir = 'Infrastructure'
        . DIRECTORY_SEPARATOR . 'Database'
        . DIRECTORY_SEPARATOR . 'migrations';
    private string $configs_dir = 'Application'
        . DIRECTORY_SEPARATOR . 'config';
    private string $base_path;

    public function __construct($app)
    {
        $this->base_path = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
        parent::__construct($app);
    }


    public function boot(): void
    {
        $this->loadMigrationsFrom($this->base_path . $this->migrations_dir);
        $this->bindRoutes();
        $this->defineGates();
    }

    public function register(): void
    {
        $this->mergeConfigRecursion(
            require $this->base_path . $this->configs_dir . DIRECTORY_SEPARATOR . 'filesystems.disks.php',
            'filesystems.disks'
        );
    }

    public function bindRoutes(): void
    {
        Route::bind('backup', fn (string $backup_id) => Backup::where('backup_id', $backup_id)->firstOrFail());
        Route::bind('backupScheduleSetting', fn() => BackupSetting::firstOrCreate([
            'key' => BackupSetting::TYPE,
            'type' => BackupSetting::TYPE,
        ]));
    }

    public function defineGates(): void
    {
//        Gate::define('field-history-search-any', static fn(User $user) =>
//        $user->is_admin->getValue()
//            ? Response::allow()
//            : Response::deny('You must be a system administrator')
//        );
    }
}
