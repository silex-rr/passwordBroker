<?php

namespace PasswordBroker\Application\Providers;

use App\Common\Application\Traits\ProviderMergeConfigRecursion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use PasswordBroker\Application\Observers\EntryGroupObserver;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Fields\Field;

class PasswordBrokerServiceProvider extends ServiceProvider
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

        EntryGroup::observe(EntryGroupObserver::class);
        //Clean domain table prefix
        DB::connection()->setTablePrefix('');
    }

    public function register(): void
    {
        $this->mergeConfigRecursion(require $this->base_path . $this->configs_dir . DIRECTORY_SEPARATOR . 'password.php', 'passwordBroker');
    }

    public function bindRoutes(): void
    {
        Route::bind('entryGroup', fn (string $entry_group_id) => EntryGroup::where('entry_group_id', $entry_group_id)->firstOrFail());
        Route::bind('entry', fn (string $entry_id) => Entry::where('entry_id', $entry_id)->firstOrFail());
        Route::bind('field', fn (string $field_id) => Field::getFiledByFieldId($field_id));
    }

}
