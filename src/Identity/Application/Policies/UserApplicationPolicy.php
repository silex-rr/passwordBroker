<?php

namespace Identity\Application\Policies;

use Identity\Domain\User\Models\User;
use Identity\Domain\UserApplication\Models\UserApplication;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class UserApplicationPolicy
{
    use HandlesAuthorization;

    public function before(?User $user): ?Response
    {
        if (is_null($user)) {
            return Response::denyWithStatus(401);
        }
        return null;
    }

    public function view(User $user, UserApplication $userApplication): Response
    {
        /**
         * @var User $userOwner
         */
        $userOwner = $userApplication->user()->first();
        return $userOwner->user_id->equals($user->user_id)
            ? Response::allow()
            : Response::denyWithStatus(403, "You have to be owner of that Application to see it");
    }

    public function viewAny(User $user): Response
    {
        return $user->is_admin->getValue()
            ? Response::allow()
            : Response::denyWithStatus(403, "Only System Administrators can see any Applications");
    }

    public function delete(User $user, UserApplication $userApplication): Response
    {
        /**
         * @var User $userOwner
         */
        $userOwner = $userApplication->user()->first();
        return $user->is_admin->getValue() || $userOwner->user_id->equals($user->user_id)
            ? Response::allow()
            : Response::denyWithStatus(403, "You can delete only application that belonged to you");
    }

    public function update(User $user, UserApplication $userApplication): Response
    {
        /**
         * @var User $userOwner
         */
        $userOwner = $userApplication->user()->first();
        return $user->is_admin->getValue() || $userOwner->user_id->equals($user->user_id)
            ? Response::allow()
            : Response::denyWithStatus(403);
    }

    public function create(User $user): Response
    {
        return Response::allow();
    }
}
