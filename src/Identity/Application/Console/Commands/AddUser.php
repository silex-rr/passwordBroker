<?php

namespace Identity\Application\Console\Commands;

use Exception;
use Identity\Application\Http\Requests\RegisterUserRequest;
use Identity\Application\Services\UserRegistrationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Console\Command\Command as CommandAlias;

class AddUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'identity:addUser {username?} {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a user ';

    /**
     * Execute the console command.
     *
     * @param UserRegistrationService $registrationService
     * @return int
     */
    public function handle(UserRegistrationService $registrationService): int
    {
        $username = $this->argument('username');
        if (empty($username)) {
            $username = $this->ask('Set user name: ');
        }
        $email = $this->argument('email');
        if (empty($email)) {
            $email = $this->ask('Set user email: ');
        }

        do {
            $password = $this->secret("Set password for user " . $username . ": ");
            $password_confirm = $this->secret("Confirm the password: ");
            if ($password !== $password_confirm) {
                $this->info("------------Passwords doesn't match, try again------------");
            }
        } while ($password !== $password_confirm);

        do {
            $master_password = $this->secret("Set master password for user " . $username . ": ");
            $master_password_confirm = $this->secret("Confirm the master password: ");
            if ($master_password !== $master_password_confirm) {
                $this->info("------------Passwords doesn't match, try again------------");
            }
        } while ($master_password !== $master_password_confirm);

        $is_admin = $this->confirm(
            "Will " . $username . " be a super administrator?"
        );

        if (!$this->confirm(
                    "Is the information correct:\r\n"
                    . "  Email   : " . $email . "\r\n"
                    . "  Username: " . $username . "\r\n"
                    . "  Admin   : " . ($is_admin ? 'Y': 'N') . "\r\n"
        )) {
            return CommandAlias::SUCCESS;
        }

        try {
            $validator = Validator::make(
                ['user' => [
                    'email' => $email,
                    'username' => $username,
                    'password' => $password,
                    'password_confirmation' => $password_confirm,
                    'master_password' => $master_password,
                    'master_password_confirmation' => $master_password_confirm,
                ]],
                (new RegisterUserRequest())->rules()
            );

            $validator->validate();

            $user = $registrationService->execute(
                $email,
                $username,
                $password,
                $master_password,
                $is_admin
            );
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
            return CommandAlias::FAILURE;
        }
        $this->info(
            ($is_admin ? "Administrator" : "User") . " '" .  $username . "' has been created."
            . "\r\n user_id: " . $user->user_id->getValue()
        );
        return CommandAlias::SUCCESS;
    }
}
