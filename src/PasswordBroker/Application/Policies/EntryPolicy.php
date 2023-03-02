<?php

namespace PasswordBroker\Application\Policies;

use Identity\Domain\User\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class EntryPolicy
{
    use HandlesAuthorization;

    public function before(?User $user): ?Response
    {
        if (is_null($user)) {
            return Response::denyWithStatus(401);
        }
        return null;
    }

    public function view(User $user): Response
    {
        /**
         * @var EntryGroup $entryGroup
         */
        $entryGroup = request()->entryGroup;
        return $entryGroup->users()->contains('user_id', $user->user_id->getValue())
                ? Response::allow()
                : Response::denyWithStatus(403);
    }

    public function viewAny(User $user): Response
    {
        return $this->view($user);
    }

    public function delete(User $user, Entry $entry): Response
    {
        /**
         * @var EntryGroup $entryGroup
         */
        $entryGroup = $entry->entryGroup()->firstOrFail();
        return $entryGroup->admins()->where('user_id', $user->user_id->getValue())->exists()
            || $entryGroup->moderators()->where('user_id', $user->user_id->getValue())->exists()
            ? Response::allow()
            : Response::denyWithStatus(403);
    }

    public function update(User $user, Entry $entry): Response
    {
        return $this->delete($user, $entry);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function move(User $user, Entry $entry): Response
    {
        $can_delete = $this->delete(user: $user, entry: $entry);
        if ($can_delete->denied()) {
            return $can_delete;
        }

        /**
         * @var EntryGroup $entryGroup
         */
        $entryGroup = EntryGroup::where('entry_group_id', request()?->get('entryGroupTarget'))->firstOrFail();
        return $entryGroup->admins()->where('user_id', $user->user_id->getValue())->exists()
            || $entryGroup->moderators()->where('user_id', $user->user_id->getValue())->exists()
                ? Response::allow()
                : Response::denyWithStatus(403, 'You do not have right to the target Entry Group');
    }

    public function create(User $user): Response
    {
        /** @var EntryGroup $entryGroup */
        $entryGroup = request()->entryGroup;
        return $entryGroup->admins()->where('user_id', $user->user_id->getValue())->exists()
            || $entryGroup->moderators()->where('user_id', $user->user_id->getValue())->exists()
            ? Response::allow()
            : Response::denyWithStatus(403);
    }
}
