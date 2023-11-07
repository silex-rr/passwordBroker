<?php

namespace Identity\Application\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Identity\Application\Http\Requests\RegisterUserRequest;
use Identity\Application\Http\Requests\UpdateUserRequest;
use Identity\Application\Http\Requests\UsersSearchRequest;
use Identity\Application\Services\RsaService;
use Identity\Application\Services\UserRegistrationService;
use Identity\Application\Services\UserService;
use Identity\Domain\User\Models\User;
use Identity\Domain\User\Models\UserAccessToken;
use Identity\Domain\User\Services\DestroyUser;
use Identity\Domain\User\Services\SearchUsers;
use Identity\Domain\User\Services\UpdateUser;
use Identity\Domain\User\Services\UserApplicationChangeOfflineDatabaseRequiredUpdate;
use Identity\Domain\User\Services\UserApplicationChangeRsaPrivateRequiredUpdate;
use Identity\Domain\UserApplication\Models\Attributes\IsOfflineDatabaseRequiredUpdate;
use Identity\Domain\UserApplication\Models\Attributes\IsRsaPrivateRequiredUpdate;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use PasswordBroker\Application\Services\EncryptionService;
use Symfony\Component\Mime\Encoder\Base64Encoder;

class UserController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct(
        protected readonly UserRegistrationService $registrationService
    )
    {
        $this->authorizeResource(User::class, ['user']);
    }

    public function index(UsersSearchRequest $request): JsonResponse
    {

        $job = new SearchUsers(
            query: $request->getQuery(),
            perPage: $request->getPerPage(),
            page: $request->getPage(),
            entryGroupInclude: $request->getEntryGroupInclude(),
            entryGroupExclude: $request->getEntryGroupExclude()
        );

        return new JsonResponse($this->dispatchSync($job), 200);
    }

    public function destroy(User $user): JsonResponse
    {
        $this->dispatchSync(new DestroyUser($user));
        return new JsonResponse(null, 200);
    }

    public function update(User $user, UpdateUserRequest $request): JsonResponse
    {
//        $all = $request->all();
//        dd($request->get('username'));

        $this->dispatchSync(new UpdateUser(
            userTarget: $user,
            username: $request->get('username'),
            email: $request->get('email'),
            password: $request->get('password')
        ));

        return new JsonResponse(null, 200);
    }

    public function show(User $user): JsonResponse
    {
        return new JsonResponse($user, 200);
    }

    public function store(RegisterUserRequest $request): JsonResponse
    {
        $email = $request->input('user.email');
        $username = $request->input('user.username');
        $password = $request->input('user.password');
        $master_password = $request->input('user.master_password');

        return new JsonResponse($this->registrationService->execute(
            email: $email,
            username: $username,
            password: $password,
            master_password: $master_password
        ));
    }

    public function getCbcSalt(EncryptionService $encryptionService, Base64Encoder $base64Encoder): JsonResponse
    {
        $carbon = Carbon::now();
        return new JsonResponse([
            'timestamp' => $carbon->timestamp,
            'salt_base64' => $base64Encoder->encodeString($encryptionService->getCbcSalt())
        ], 200);
    }

    public function getPrivateRsa(RsaService $rsaService, Base64Encoder $base64Encoder, UserService $userService): JsonResponse
    {
        $carbon = Carbon::now();
        /**
         * @var User $authUser
         */
        $authUser = Auth::user();
        $userPrivateKeyString = $rsaService->getUserPrivateKeyString($authUser->user_id);

        /**
         * @var UserAccessToken $accessToken
         */
        $accessToken = $authUser->currentAccessToken();
        if ($accessToken) {
            $userApplication = $userService->getUserApplicationByToken($accessToken);
            if ($userApplication) {
                $this->dispatchSync(new UserApplicationChangeRsaPrivateRequiredUpdate(
                    userApplication: $userApplication,
                    isRsaPrivateRequiredUpdate: new IsRsaPrivateRequiredUpdate(false),
                    carbon: $carbon
                ));
            }
        }

        return new JsonResponse([
            'timestamp' => $carbon->timestamp,
            'rsa_private_key_base64' => $base64Encoder->encodeString($userPrivateKeyString)
        ], 200);
    }

}
