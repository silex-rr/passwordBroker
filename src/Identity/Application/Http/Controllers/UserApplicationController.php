<?php

namespace Identity\Application\Http\Controllers;

use App\Http\Controllers\Controller;
use Identity\Application\Http\Requests\CreateUserApplicationRequest;
use Identity\Application\Http\Requests\UpdateOfflineDatabaseModeRequest;
use Identity\Domain\User\Models\User;
use Identity\Domain\User\Models\UserAccessToken;
use Identity\Domain\UserApplication\Models\Attributes\ClientId;
use Identity\Domain\UserApplication\Models\Attributes\IsOfflineDatabaseMode;
use Identity\Domain\UserApplication\Models\Attributes\UserApplicationId;
use Identity\Domain\UserApplication\Models\UserApplication;
use Identity\Domain\UserApplication\Services\CreateUserApplication;
use Identity\Domain\UserApplication\Services\UpdateOfflineDatabaseMode;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserApplicationController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(UserApplication::class, ['userApplication']);
    }

    protected function resourceAbilityMap(): array
    {
        $resourceAbilityMap = parent::resourceAbilityMap();
        $resourceAbilityMap['setOfflineDatabaseStatus'] = 'update';
        $resourceAbilityMap['getOfflineDatabaseStatus'] = 'view';
        return $resourceAbilityMap;
    }

    public function show(UserApplication $userApplication): JsonResponse
    {
        return new JsonResponse(['userApplication' => $userApplication], 200);
    }
    public function store(CreateUserApplicationRequest $request): JsonResponse
    {
        $clientId = new ClientId($request->clientId());
        /**
         * @var User $user
         */
        $user = Auth::user();
        $userApplication = UserApplication::where('client_id', $clientId)->where('user_id', $user->user_id->getValue())
            ->first();
        if (!$userApplication) {
            /**
             * @var UserApplication $userApplication
             */
            $userApplication = $this->dispatchSync(new CreateUserApplication(user: $user, clientId: $clientId));
        }
        return new JsonResponse(['userApplication' => $userApplication], 200);
    }

    public function getOfflineDatabaseStatus(UserApplication $userApplication): JsonResponse
    {
        return new JsonResponse(['status' => $userApplication->is_offline_database_mode->getValue()], 200);
    }
    public function isOfflineDatabaseRequiredUpdate(UserApplication $userApplication): JsonResponse
    {
        return new JsonResponse(['status' => $userApplication->is_offline_database_required_update->getValue()], 200);
    }
    public function isRsaPrivateRequiredUpdate(UserApplication $userApplication): JsonResponse
    {
        return new JsonResponse(['status' => $userApplication->is_rsa_private_required_update->getValue()], 200);
    }

    public function setOfflineDatabaseStatus(UserApplication $userApplication, UpdateOfflineDatabaseModeRequest $request): JsonResponse
    {
        $this->dispatchSync(
            new UpdateOfflineDatabaseMode($userApplication, IsOfflineDatabaseMode::fromNative($request->status()))
        );
        return new JsonResponse([],200);
    }

    /**
     * @param User $user
     * @return UserAccessToken
     */
    private function getCurrentAccessToken(User $user): UserAccessToken
    {
        /**
         * @var UserAccessToken $token
         */
        $token = $user->currentAccessToken();
        return $token;
    }
}
