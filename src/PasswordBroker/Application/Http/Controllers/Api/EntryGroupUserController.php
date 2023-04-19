<?php

namespace PasswordBroker\Application\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Identity\Domain\User\Models\User;
use Illuminate\Http\JsonResponse;
use PasswordBroker\Application\Http\Requests\EntryGroupUserRequest;
use PasswordBroker\Application\Services\EntryGroupService;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Groups\Admin;
use PasswordBroker\Domain\Entry\Models\Groups\Member;
use PasswordBroker\Domain\Entry\Models\Groups\Moderator;
use PasswordBroker\Domain\Entry\Services\AddAdminToEntryGroup;
use PasswordBroker\Domain\Entry\Services\AddMemberToEntryGroup;
use PasswordBroker\Domain\Entry\Services\AddModeratorToEntryGroup;
use PasswordBroker\Domain\Entry\Services\RemoveAdminFromEntryGroup;
use PasswordBroker\Domain\Entry\Services\RemoveMemberFromEntryGroup;
use PasswordBroker\Domain\Entry\Services\RemoveModeratorFromEntryGroup;
use PasswordBroker\Infrastructure\Validation\Handlers\EntryGroupUserValidationHandler;
use RuntimeException;

class EntryGroupUserController extends Controller
{
    public function __construct(private readonly EntryGroupService $entryGroupService)
    {
        $this->authorizeResource(EntryGroup::class, ['entryGroup']);
    }

    protected function resourceAbilityMap(): array
    {
        return [
            'index' => 'viewAnyRole',
            'show' => 'viewRole',
            'create' => 'createRole',
            'store' => 'createRole',
            'edit' => 'updateRole',
            'update' => 'updateRole',
            'destroy' => 'deleteRole'
        ];
    }


    public function index(EntryGroup $entryGroup): JsonResponse
    {
        return new JsonResponse($entryGroup->users(), 200);
    }

    public function store(EntryGroup $entryGroup, EntryGroupUserRequest $request): JsonResponse
    {
        $params = [
            $request->targetUser(),
            $entryGroup,
            $request->get('encrypted_aes_password'),
            $request->get('master_password'),
            new EntryGroupUserValidationHandler()
        ];
        switch ($request->get('role')) {
            case Admin::ROLE_NAME:
                $job = new AddAdminToEntryGroup(...$params);
                break;
            case Moderator::ROLE_NAME:
                $job = new AddModeratorToEntryGroup(...$params);
                break;
            case Member::ROLE_NAME:
                $job = new AddMemberToEntryGroup(...$params);
                break;
        }
        if (!isset($job)) {
            throw new RuntimeException("Undefined Role Service");
        }

        $this->dispatchSync($job);
        return new JsonResponse(null, 200);
    }

    public function show(): JsonResponse
    {
        return new JsonResponse([], 200);
    }

    public function update(): JsonResponse
    {
        return new JsonResponse([], 200);
    }

    public function destroy(EntryGroup $entryGroup, User $user): JsonResponse
    {

        /**
         * @var Admin $admin
         */
        $admin = $entryGroup->admins()->where('user_id', $user->user_id)->first();
        if (!is_null($admin)) {
            $this->dispatchSync(new RemoveAdminFromEntryGroup($admin, $entryGroup));
            return new JsonResponse(null, 200);
        }
        /**
         * @var Moderator $moderator
         */
        $moderator = $entryGroup->moderators()->where('user_id', $user->user_id)->first();
        if (!is_null($moderator)) {
            $this->dispatchSync(new RemoveModeratorFromEntryGroup($moderator, $entryGroup));
            return new JsonResponse(null, 200);
        }
        /**
         * @var Member $member
         */
        $member = $entryGroup->members()->where('user_id', $user->user_id)->first();
        if (!is_null($member)) {
            $this->dispatchSync(new RemoveMemberFromEntryGroup($member, $entryGroup));
            return new JsonResponse(null, 200);
        }
        abort(404, 'User was not found in the Entry Group');
    }
}
