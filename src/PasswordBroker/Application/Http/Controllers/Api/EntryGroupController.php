<?php

namespace PasswordBroker\Application\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Identity\Domain\User\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use PasswordBroker\Application\Http\Requests\EntryGroupMoveRequest;
use PasswordBroker\Application\Http\Requests\EntryGroupRequest;
use PasswordBroker\Application\Services\EntryGroupService;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Services\AddEntryGroup;
use PasswordBroker\Domain\Entry\Services\MoveEntryGroup;
use PasswordBroker\Infrastructure\Validation\Handlers\EntryGroupValidationHandler;

class EntryGroupController extends Controller
{
    public function __construct(private readonly EntryGroupService $entryGroupService)
    {
        $this->authorizeResource(EntryGroup::class, ['entryGroup']);
    }

    protected function resourceAbilityMap(): array
    {
        $resourceAbilityMap = parent::resourceAbilityMap();
        $resourceAbilityMap['move'] = 'move';
        $resourceAbilityMap['indexAsTree'] = 'viewAny';
        return $resourceAbilityMap;
    }

    protected function resourceMethodsWithoutModels(): array
    {
        $resourceMethodsWithoutModels = parent::resourceMethodsWithoutModels();
        $resourceMethodsWithoutModels[] = 'indexAsTree';
        return $resourceMethodsWithoutModels;
    }


    public function index(): JsonResponse
    {
        /**
         * @var User $user
         */
        $user = Auth::user();

        return new JsonResponse($user->userOf(), 200);
    }

    public function indexWithFields(): JsonResponse
    {
        /**
         * @var User $user
         */
        $user = Auth::user();
        $timestamp = time();
        return new JsonResponse([
            'timestamp' => $timestamp,
            'data' => $this->entryGroupService->groupsWithFields($user)
        ], 200);
    }
    public function indexAsTree(): JsonResponse
    {
        /**
         * @var User $user
         */
        $user = Auth::user();

        return new JsonResponse(
            [
                'trees' => $this->entryGroupService->groupsAsTree($user->userOf())
            ]
            , 200);
    }

    public function store(EntryGroupRequest $request): JsonResponse
    {

        $entryGroup = EntryGroup::hydrate([$request->all()])->first();
        $entryGroup->exists = false;
        $response = $this->dispatchSync(
            new AddEntryGroup(
                $entryGroup,
                new EntryGroupValidationHandler()
            )
        );

        return new JsonResponse($response, 200);
    }

    public function move(EntryGroup $entryGroup, EntryGroupMoveRequest $request): JsonResponse
    {
        $this->dispatchSync(new MoveEntryGroup($entryGroup, $request->entryGroupTarget(), $this->entryGroupService));
        return new JsonResponse(1, 200);
    }

    public function show(EntryGroup $entryGroup): JsonResponse
    {
        $role = $entryGroup->users()->where('user_id', Auth::user()->user_id->getValue())->first();
        return new JsonResponse(
            [
                'entryGroup' => $entryGroup,
                'role' => $role
            ]
            , 200);
    }

    public function update(): JsonResponse
    {
        return new JsonResponse([], 200);
    }

    public function destroy(EntryGroup $entryGroup): JsonResponse
    {
        $entryGroup->delete();
        return new JsonResponse([], 200);
    }
}
