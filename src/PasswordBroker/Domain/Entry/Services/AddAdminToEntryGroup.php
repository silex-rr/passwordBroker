<?php

namespace PasswordBroker\Domain\Entry\Services;

use Identity\Domain\User\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use PasswordBroker\Application\Services\EntryGroupService;
use PasswordBroker\Domain\Entry\Events\AdminWasAddedToEntryGroup;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Groups\Admin;
use PasswordBroker\Infrastructure\Validation\Handlers\EntryGroupUserValidationHandler;

class AddAdminToEntryGroup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    public function __construct(
        protected User $user,
        protected EntryGroup $entryGroup,
        protected ?string $encrypted_aes_password,
        protected ?string $master_password,
        protected EntryGroupUserValidationHandler $entryGroupUserValidationHandler
    ){}

    public function handle(): void
    {
        $this->validate();

        app(EntryGroupService::class)->addUserToGroupAsAdmin($this->user, $this->entryGroup, $this->encrypted_aes_password, $this->master_password);
        /**
         * @var Admin $admin;
         */
        $admin = $this->entryGroup->admins()->where('user_id', $this->user->user_id->getValue())->firstOrFail();
        event(new AdminWasAddedToEntryGroup($admin));
    }

    public function validate(): void
    {
        $this->entryGroup->validateUser($this->entryGroupUserValidationHandler, $this->user);
    }
}
