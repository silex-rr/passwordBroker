<?php

namespace PasswordBroker\Application\Providers;

use App\Common\Application\Traits\ProviderMergeConfigRecursion;
use Identity\Domain\User\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use PasswordBroker\Application\Observers\EntryGroupObserver;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Fields\EntryFieldHistory;
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
        $this->defineGates();

        EntryGroup::observe(EntryGroupObserver::class);
        //Clean domain table prefix
//        DB::connection()->setTablePrefix('');
    }

    public function register(): void
    {
        $this->mergeConfigRecursion(
            require $this->base_path . $this->configs_dir . DIRECTORY_SEPARATOR . 'password.php',
            'passwordBroker'
        );
        $this->mergeConfigRecursion(
            require $this->base_path . $this->configs_dir . DIRECTORY_SEPARATOR . 'database.connections.php',
            'database.connections'
        );
        $this->mergeConfigRecursion(
            require $this->base_path . $this->configs_dir . DIRECTORY_SEPARATOR . 'filesystems.disks.php',
            'filesystems.disks'
        );
//        dd(config('database.connections'));
    }

    public function bindRoutes(): void
    {
        Route::model('entryGroup', EntryGroup::class);

        Route::model('entryGroupExclude', EntryGroup::class);
        Route::model('entryGroupInclude', EntryGroup::class);

        Route::bind('entry', fn(string $entry_id) => Entry::where('entry_id', $entry_id)->firstOrFail());
        Route::bind('field', fn(string $field_id) => Field::getFiledByFieldId($field_id));
        Route::bind('fieldEditLog', fn(string $field_edit_log_id) => EntryFieldHistory::where('field_edit_log_id', $field_edit_log_id)->firstOrFail());
    }

    public function defineGates(): void
    {
        Gate::define('field-history-search-any', static fn(User $user) =>
            $user->is_admin->getValue()
                ? Response::allow()
                : Response::deny('You must be a system administrator')
        );

        Gate::define('get-groups-with-fields', static fn (User $user) =>
            $user->is_admin->getValue()
                ? Response::allow()
                : Response::deny('You must be a system administrator')
        );
    }
}
