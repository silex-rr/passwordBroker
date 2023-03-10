<?php

namespace Identity\Application\Policies;

use Identity\Domain\User\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class UserPolicy
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
        return Response::allow();
    }
    public function viewSelf(): Response
    {
        return Response::allow();
    }

    public function viewAny(User $user): Response
    {
        return Response::allow();
    }

    public function delete(User $user): Response
    {
        return $user->is_admin->getValue()
            ? Response::allow()
            : Response::denyWithStatus(403);
    }

    public function update(User $user, User $userTarget): Response
    {
        return $user->is_admin->getValue() || $user->user_id->equals($userTarget->user_id)
            ? Response::allow()
            : Response::denyWithStatus(403);
    }

    public function create(User $user): Response
    {
        if (User::doesntExist()) {
            return Response::allow();
        }
        return $this->delete($user);
    }
}
