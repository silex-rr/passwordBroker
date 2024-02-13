<?php

namespace Identity\Application\Console\Commands;

use Exception;
use Identity\Application\Http\Requests\RegisterUserRequest;
use Identity\Domain\User\Models\User;
use Identity\Domain\User\Services\ChangeEmailForUser;
use Identity\Domain\User\Services\ChangePasswordForUser;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Console\Command\Command as CommandAlias;

class ChangeEmail extends Command
{
    use DispatchesJobs;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'identity:changeEmail {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change user email';

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
            $email = $this->ask("Set new email for user " . $user->name->getValue() . ": ");
            $email_confirm = $this->ask("Confirm new email: ");
            if ($email !== $email_confirm) {
                $this->info("------------eMails doesn't match, try again------------");
                continue;
            }
            $validatorEmail = Validator::make(['email' => $email], ['email' => 'required|email']);
            try {
                $validatorEmail->validate();
            } catch (Exception $exception) {
                $this->info($exception->getMessage());
                continue;
            }

            break;
        } while (true);

        try {
            $this->dispatchSync(new ChangeEmailForUser($user, $email));

            $this->info('The eMail has been successfully changed for user: ' . $user->name->getValue());

        } catch (Exception $exception) {
            $this->error($exception->getMessage());
            return CommandAlias::FAILURE;
        }

        return CommandAlias::SUCCESS;
    }
}
