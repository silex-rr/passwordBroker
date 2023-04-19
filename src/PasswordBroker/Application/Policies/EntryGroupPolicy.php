<?php

namespace PasswordBroker\Application\Policies;

use Identity\Domain\User\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class EntryGroupPolicy
{
    use HandlesAuthorization;

    public function before(?User $user): ?Response
    {
        if (is_null($user)) {
            return Response::denyWithStatus(401);
        }
        return null;
    }

    public function viewAny(User $user): Response
    {
        return Response::allow();
    }

    public function view(User $user, EntryGroup $entryGroup): Response
    {
        return $entryGroup->admins()->where('user_id', $user->user_id)->exists()
            || $entryGroup->moderators()->where('user_id', $user->user_id)->exists()
            || $entryGroup->members()->where('user_id', $user->user_id)->exists()
                ? Response::allow()
                : Response::denyWithStatus(403);
    }

    public function delete(User $user, EntryGroup $entryGroup): Response
    {
        return $entryGroup->admins()->where('user_id', $user->user_id->getValue())->exists()
            ? Response::allow()
            : Response::denyWithStatus(403);
    }

    public function create(?User $user): Response
    {
        return Response::allow();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function move(User $user, EntryGroup $entryGroup): Response
    {
        $can_delete = $this->delete(user: $user, entryGroup: $entryGroup);
        if ($can_delete->denied()) {
            return $can_delete;
        }
        /**
         * @var EntryGroup $entryGroup
         */
        $entry_group_id = request()?->get('entryGroupTarget');
        if (is_null($entry_group_id)) {
            return Response::allow();
        }
        $entryGroupTarget = EntryGroup::where('entry_group_id', $entry_group_id)->firstOrFail();
        return $entryGroupTarget->admins()->where('user_id', $user->user_id->getValue())->exists()
            || $entryGroupTarget->moderators()->where('user_id', $user->user_id->getValue())->exists()
                ? Response::allow()
                : Response::denyWithStatus(403, 'You do not have right to the target Entry Group');
    }

    //// ROLE POLICY

    public function viewAnyRole(User $user): Response
    {
        /**
         * @var EntryGroup $entryGroup
         */
        $entryGroup = request()->entryGroup;

        return $entryGroup->admins()->where('user_id', $user->user_id)->exists()
            || $entryGroup->moderators()->where('user_id', $user->user_id)->exists()
            || $entryGroup->members()->where('user_id', $user->user_id)->exists()
            ? Response::allow()
            : Response::denyWithStatus(403);
    }
    public function viewRole(User $user): Response
    {
        return Response::denyWithStatus(403);
    }
    public function createRole(User $user): Response
    {
        /**
         * @var EntryGroup $entryGroup
         */
        $entryGroup = request()->entryGroup;

        if (!$entryGroup->admins()->where('user_id', $user->user_id)->exists()) {
            return Response::denyWithStatus(403, 'Only Admins can assign a role to a user');
        }

        return Response::allow();
    }
    public function updateRole(User $user): Response
    {
        return $this->createRole($user);
    }
    public function deleteRole(User $user): Response
    {
        $response = $this->createRole($user);
        if ($response->denied()) {
            return $response;
        }

        /**
         * @var EntryGroup $entryGroup
         */
        $entryGroup = request()->entryGroup;
        /**
         * @var User $targetUser
         */
        $targetUser = request()->user;

        if ($entryGroup->admins()->count() === 1
            && $entryGroup->admins()->where('user_id', $targetUser->user_id)->exists()
        ) {
            return Response::denyWithStatus(409, "You cannot delete the last Admin from the Entry Group");
        }

        return Response::allow();
    }
}
