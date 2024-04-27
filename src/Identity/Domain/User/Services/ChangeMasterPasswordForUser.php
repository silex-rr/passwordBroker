<?php

namespace Identity\Domain\User\Services;

use Exception;
use Identity\Application\Services\RsaService;
use Identity\Domain\User\Events\MasterPasswordForUserWasChanged;
use Identity\Domain\User\Models\Attributes\PublicKey;
use Identity\Domain\User\Models\User;
use Illuminate\Bus\Dispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use PasswordBroker\Application\Services\EntryGroupService;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Groups\Admin;
use PasswordBroker\Domain\Entry\Models\Groups\Attributes\EncryptedAesPassword;
use PasswordBroker\Domain\Entry\Models\Groups\Member;
use PasswordBroker\Domain\Entry\Models\Groups\Moderator;
use PasswordBroker\Domain\Entry\Models\Groups\Role;
use PasswordBroker\Domain\Entry\Services\RemoveAdminFromEntryGroup;
use PasswordBroker\Domain\Entry\Services\RemoveMemberFromEntryGroup;
use PasswordBroker\Domain\Entry\Services\RemoveModeratorFromEntryGroup;
use RuntimeException;


class ChangeMasterPasswordForUser implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        private readonly User    $userTarget,
        private readonly string  $newMasterPassword,
        private readonly ?string $oldMasterPassword = null,
    )
    {
    }

    public function handle(): void
    {

        /**
         * @var RsaService $rsaService
         */
        $rsaService = app(RsaService::class);
        /**
         * @var EntryGroupService $entryGroupService
         */
        $entryGroupService = app(EntryGroupService::class);
        /**
         * @var Dispatcher $dispatcher
         */
        $dispatcher = app(Dispatcher::class);

        [$privateKey, $publicKey] = $rsaService->generateKeyPair($this->newMasterPassword);

        if ($this->oldMasterPassword) {
            DB::beginTransaction();
            try {
                foreach ($this->userTarget->userOf() as $role) {
                    $decryptedAesPasswordForRole = $entryGroupService->getDecryptedAesPasswordForRole(master_password: $this->oldMasterPassword, role: $role, user: $this->userTarget);

                    $role->encrypted_aes_password = new EncryptedAesPassword(
                        $entryGroupService->getEncryptedAesPasswordByPublicKey(
                            decrypted_aes_password: $decryptedAesPasswordForRole,
                            publicKey: $publicKey,
                        )
                    );

                    $role->save();
                }
                $rsaService->storeUserPrivateKey($this->userTarget->user_id, $privateKey);
            } catch (Exception $exception) {
                DB::rollBack();
                throw new RuntimeException($exception->getMessage());
            }

            $this->userTarget->public_key = new PublicKey((string)$publicKey);
            DB::commit();
        } else {
            foreach ($this->userTarget->userOf() as $role) {
                /**
                 * @var Role $role
                 */
                switch ($role::ROLE_NAME) {
                    case Admin::ROLE_NAME:
                        $dispatcher->dispatchSync(new RemoveAdminFromEntryGroup(
                            $role,
                            EntryGroup::where(['entry_group_id', $role->entry_group_id])->firstOrFail()),
                        );
                        break;
                    case Moderator::ROLE_NAME:
                        $dispatcher->dispatchSync(new RemoveModeratorFromEntryGroup(
                            $role,
                            EntryGroup::where(['entry_group_id', $role->entry_group_id])->firstOrFail()),
                        );
                        break;
                    case Member::ROLE_NAME:
                        $dispatcher->dispatchSync(new RemoveMemberFromEntryGroup(
                            $role,
                            EntryGroup::where(['entry_group_id', $role->entry_group_id])->firstOrFail()),
                        );
                        break;
                    default:
                        throw new RuntimeException('Unexpected Role name ' . $role::ROLE_NAME);
                }
            }

            $rsaService->storeUserPrivateKey($this->userTarget->user_id, $privateKey);
            $this->userTarget->public_key = new PublicKey((string)$publicKey);
        }

        $this->userTarget->save();
        event(new MasterPasswordForUserWasChanged(user: $this->userTarget));
    }
}
