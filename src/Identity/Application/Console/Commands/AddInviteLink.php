<?php

namespace Identity\Application\Console\Commands;

use Exception;
use Identity\Application\Http\Requests\RegisterUserRequest;
use Identity\Application\Services\RecoveryUserService;
use Identity\Application\Services\UserRegistrationService;
use Identity\Application\Services\UserService;
use Identity\Domain\User\Models\Attributes\RecoveryLinkType;
use Identity\Domain\User\Services\CreateRecoveryLink;
use Illuminate\Bus\Dispatcher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use PasswordBroker\Infrastructure\Services\PasswordGenerator;
use Symfony\Component\Console\Command\Command as CommandAlias;

class AddInviteLink extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'identity:addInviteLink {username?} {email?} {--force=no}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an Invite Link for a user';


    /**
     * @param UserRegistrationService $registrationService
     * @param RecoveryUserService $recoveryUserService
     * @param UserService $userService
     * @param Dispatcher $dispatcher
     * @return int
     */
    public function handle(
        UserRegistrationService $registrationService,
        RecoveryUserService     $recoveryUserService,
        UserService             $userService,
        Dispatcher              $dispatcher,
        PasswordGenerator       $passwordGenerator,
    ): int
    {
        $email = $this->argument('email');
        if (empty($email)) {
            $email = $this->ask('Set user email: ');
        }

        $username = $this->argument('username');
        if (empty($username)) {
            $username = $this->ask('Set user name: ', $userService->getUserUniqTemporaryName());
        }

        $is_admin = $this->confirm(
            "Will " . $username . " be a super administrator?"
        );


        if ($this->option('force') === 'no' && !$this->confirm(
            "Is the information correct:\r\n"
            . "  Email   : " . $email . "\r\n"
            . "  Username: " . $username . "\r\n"
            . "  Admin   : " . ($is_admin ? 'Y' : 'N') . "\r\n"
        )) {
            return CommandAlias::SUCCESS;
        }

        $passwordGenerator->setLength(42);
        $passwordGenerator->setSymbolsLetterSpecial(true);
        $passwordGenerator->setSymbolsLetterBrackets(true);

        $password_confirm = $password = $passwordGenerator->generate();
        $master_password_confirm = $master_password = $passwordGenerator->generate();

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

        $recoveryLink = $recoveryUserService->createRecoveryLink(
            user: $user,
            linkType: RecoveryLinkType::INVITE,
        );

        $dispatcher->dispatchSync(new CreateRecoveryLink($recoveryLink));

        $this->info('Invite link for new user "' . $email . '": ' . $recoveryUserService->makeRecoveryUrl($recoveryLink));

        return CommandAlias::SUCCESS;
    }
}
