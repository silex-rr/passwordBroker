<?php

namespace Identity\Application\Listeners;

use Identity\Domain\User\Models\Attributes\UserId;
use Identity\Domain\User\Services\UserApplicationChangeOfflineDatabaseRequiredUpdate;
use Identity\Domain\UserApplication\Events\UserApplicationOfflineDatabaseModeHasChanged;
use Identity\Domain\UserApplication\Models\Attributes\IsOfflineDatabaseRequiredUpdate;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use PasswordBroker\Application\Events\EntryEvent;
use PasswordBroker\Application\Events\EntryGroupEvent;
use PasswordBroker\Application\Events\FieldEvent;
use PasswordBroker\Application\Events\RoleEvent;
use PasswordBroker\Domain\Entry\Models\Groups\Admin;
use PasswordBroker\Domain\Entry\Models\Groups\Member;
use PasswordBroker\Domain\Entry\Models\Groups\Moderator;

class UserApplicationSetOfflineDatabaseRequiredUpdate
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param EntryGroupEvent|EntryEvent|RoleEvent|FieldEvent $event
     * @return void
     */
    public function handle(EntryGroupEvent|EntryEvent|RoleEvent|FieldEvent|UserApplicationOfflineDatabaseModeHasChanged $event): void
    {
        /**
         * @var Dispatcher $dispatcher
         */
        $dispatcher = app(Dispatcher::class);

        $userApplicationJoin = static function(?UserId $userId = null) {
            return static function (Builder|BelongsTo $builder) use ($userId){
                $builder->withWhereHas('applications', static function(Builder|HasMany $builder){
                    $builder
                        ->where('is_offline_database_mode', true)
                        ->where('is_offline_database_required_update', false);
                });
                if ($userId) {
                    $builder->where('user_id', $userId->getValue());
                }
            };
        };

        switch (true) {
            case $event instanceof UserApplicationOfflineDatabaseModeHasChanged:
                if ($event->isOfflineDatabaseMode->getValue()
                    && $event->userApplication->is_offline_database_required_update->getValue() === false
                ) {
                    $dispatcher->dispatch(new UserApplicationChangeOfflineDatabaseRequiredUpdate(
                        userApplication: $event->userApplication,
                        isOfflineDatabaseRequiredUpdate: new IsOfflineDatabaseRequiredUpdate(true)
                    ));
                }
                return;
            case $event instanceof RoleEvent:
                $entryGroup = $event->role->entryGroup();
                switch ($event->role::ROLE_NAME) {
                    case Admin::ROLE_NAME:
                        $entryGroup->with('admins.user', fn () => $userApplicationJoin($event->role->user_id));
                        break;
                    case Moderator::ROLE_NAME:
                        $entryGroup->with('moderators.user', $userApplicationJoin($event->role->user_id));
                        break;
                    case Member::ROLE_NAME:
                        $entryGroup->with('members.user', $userApplicationJoin($event->role->user_id));
                        break;
                }

                $entryGroup = $entryGroup->first();
                break;
            case $event instanceof FieldEvent:
                $entry = $event->field->entry()->with('entryGroup',
                    static function (Builder|BelongsTo $builder) use ($userApplicationJoin) {
                        $builder->with('admins.user', $userApplicationJoin());
                        $builder->with('moderators.user', $userApplicationJoin());
                        $builder->with('members.user', $userApplicationJoin());
                })->first();
                if (empty($entry)) {
                    return;
                }
                $entryGroup = $entry->entryGroup;
                break;
            case $event instanceof EntryEvent:
                $entryGroup = $event->entry->entryGroup()
                    ->with([
                        'admins.user' => $userApplicationJoin(),
                        'moderators.user' => $userApplicationJoin(),
                        'members.user' =>  $userApplicationJoin()
                    ])->first();
                break;
            case $event instanceof EntryGroupEvent:
                $entryGroup = $event->entryGroup
                    ->load([
                        'admins.user' => $userApplicationJoin(),
                        'moderators.user' => $userApplicationJoin(),
                        'members.user' =>  $userApplicationJoin()
                    ]);
                break;
            default:
                return;

        }

        $users = $entryGroup->admins->pluck('user');
        $users->concat($entryGroup->moderators->pluck('user'));
        $users->concat($entryGroup->members->pluck('user'));
        $applications = $users->pluck('applications')->flatten(1);
        if (!$applications->count()) {
            return;
        }
        $isOfflineDatabaseRequiredUpdate = new IsOfflineDatabaseRequiredUpdate(true);


        $applications->each(fn ( $userApplication)
            => !is_null($userApplication)
                && $dispatcher->dispatch(new UserApplicationChangeOfflineDatabaseRequiredUpdate(
                    userApplication: $userApplication,
                    isOfflineDatabaseRequiredUpdate: $isOfflineDatabaseRequiredUpdate
                )
            )
        );
    }
}
