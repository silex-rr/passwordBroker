<?php

namespace Identity\Application\Console\Commands;

use Exception;
use Identity\Application\Http\Requests\RegisterUserRequest;
use Identity\Application\Services\UserRegistrationService;
use Identity\Domain\User\Models\User;
use Identity\Domain\User\Services\ChangePasswordForUser;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Console\Command\Command as CommandAlias;

class ChangePassword extends Command
{
    use DispatchesJobs;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'identity:changePassword {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change user password';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        if (empty($email)) {
            $email = $this->ask('Type user email: ');
        }
        /**
         * @var User|null $user
         */
        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("User with email: " . $email . " doesnt exist");
            return CommandAlias::INVALID;
        }

        do {
            $password = $this->secret("Set new password for user " . $user->name->getValue() . ": ");
            $password_confirm = $this->secret("Confirm new password: ");
            if ($password !== $password_confirm) {
                $this->info("------------Passwords doesn't match, try again------------");
            }
        } while ($password !== $password_confirm);

        try {
            $rules = (new RegisterUserRequest())->rules();

            $validator = Validator::make(
                ['user' => [
                    'password' => $password,
                    'password_confirmation' => $password_confirm,
                ]],
                ['user.password' => $rules['user.password']]
            );

            $validator->validate();

            $this->dispatchSync(new ChangePasswordForUser($user, $password));

            $this->info('The password has been successfully changed for user: ' . $email);

        } catch (Exception $exception) {
            $this->error($exception->getMessage());
            return CommandAlias::FAILURE;
        }

        return CommandAlias::SUCCESS;
    }
}
