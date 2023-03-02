<?php

namespace PasswordBroker\Application\Policies;

use Identity\Domain\User\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Fields\Field;

class EntryFieldPolicy
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
        $request = request();
        $entryGroup = is_object($request) ? $request->entryGroup : null;
        return $entryGroup instanceof EntryGroup
                && $entryGroup->users()->contains('user_id', $user->user_id->getValue())
            ? Response::allow()
            : Response::denyWithStatus(403);
    }

    public function viewAny(User $user): Response
    {
        return $this->view($user);
    }

    public function delete(User $user, Field $field): Response
    {
        /**
         * @var EntryGroup $entryGroup
         */
        $entryGroup = $field->entry()->firstOrFail()->entryGroup()->firstOrFail();
        return $entryGroup->admins()->where('user_id', $user->user_id->getValue())->exists()
            || $entryGroup->moderators()->where('user_id', $user->user_id->getValue())->exists()
                ? Response::allow()
                : Response::denyWithStatus(403);
    }

    public function update(User $user, Field $field): Response
    {
        return $this->delete($user, $field);
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
