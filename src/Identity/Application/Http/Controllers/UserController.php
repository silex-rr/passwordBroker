<?php

namespace Identity\Application\Http\Controllers;

use App\Http\Controllers\Controller;
use Identity\Application\Http\Requests\RegisterUserRequest;
use Identity\Application\Http\Requests\UpdateUserRequest;
use Identity\Application\Services\UserRegistrationService;
use Identity\Domain\User\Models\User;
use Identity\Domain\User\Services\DestroyUser;
use Identity\Domain\User\Services\UpdateUser;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct(protected readonly UserRegistrationService $registrationService)
    {
        $this->authorizeResource(User::class, ['user']);
    }

    protected function resourceAbilityMap(): array
    {
        $resourceAbilityMap = parent::resourceAbilityMap();
        $resourceAbilityMap['show_me'] = 'viewSelf';
        return $resourceAbilityMap;
    }

    public function index(): JsonResponse
    {
        return new JsonResponse([], 200);
    }

    public function show(User $user): JsonResponse
    {
        return new JsonResponse($user, 200);
    }

    public function destroy(User $user): JsonResponse
    {
        $this->dispatchSync(new DestroyUser($user));
        return new JsonResponse(null, 200);
    }

    public function update(User $user, UpdateUserRequest $request): JsonResponse
    {
        $all = $request->all();

        $this->dispatchSync(new UpdateUser(
            userTarget: $user,
            username: $request->get('username'),
            email: $request->get('email'),
            password: $request->get('password')
        ));

        return new JsonResponse(null, 200);
    }

    public function showMe(): JsonResponse
    {
        return new JsonResponse(Auth::user(), 200);
    }

    public function store(RegisterUserRequest $request): JsonResponse
    {
        $email = $request->input('user.email');
        $username = $request->input('user.username');
        $password = $request->input('user.password');
        $master_password = $request->input('user.master_password');

        return new JsonResponse($this->registrationService->execute(
            $email,
            $username,
            $password,
            $master_password
        ));
    }

}
