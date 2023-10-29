<?php

namespace PasswordBroker\Application\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Identity\Domain\User\Models\User;
use Identity\Domain\User\Models\UserAccessToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use PasswordBroker\Application\Http\Requests\EntryGroupMoveRequest;
use PasswordBroker\Application\Http\Requests\EntryGroupRequest;
use PasswordBroker\Application\Services\EncryptionService;
use PasswordBroker\Application\Services\EntryGroupService;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Services\AddEntryGroup;
use PasswordBroker\Domain\Entry\Services\MoveEntryGroup;
use PasswordBroker\Infrastructure\Validation\Handlers\EntryGroupValidationHandler;
use Symfony\Component\Mime\Encoder\Base64Encoder;

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
        $carbon = Carbon::now();
        /**
         * @var UserAccessToken $accessToken
         */
        $accessToken = $user->currentAccessToken();
        if ($accessToken) {
            $accessToken->rsa_private_fetched_at = $carbon;
            $accessToken->save();
        }
        $encoder = app(Base64Encoder::class);
        return new JsonResponse([
            'timestamp' => $carbon->timestamp,
            'data' => [
                'groups' => $this->entryGroupService->groupsWithFields($user),
                'trees' => $this->entryGroupService->groupsAsTree($user->userOf()),
                'cbcSaltBase64' => $encoder->encodeString(app(EncryptionService::class)->getCbcSalt())
            ]
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
