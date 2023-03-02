<?php

namespace Identity\Application\Http\Controllers;



use Identity\Application\Http\Requests\RegisterUserRequest;
use Identity\Application\Services\UserRegistrationService;
use Illuminate\Http\JsonResponse;

class UserRegistrationController
{
    private UserRegistrationService $registrationService;

    public function __construct(UserRegistrationService $registrationService)
    {
        $this->registrationService = $registrationService;
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
