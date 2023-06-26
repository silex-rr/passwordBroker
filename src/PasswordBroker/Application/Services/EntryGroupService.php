<?php

namespace PasswordBroker\Application\Services;

use Identity\Application\Services\RsaService;
use Identity\Domain\User\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use PasswordBroker\Domain\Entry\Models\Attributes\MaterializedPath;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Fields\Field;
use PasswordBroker\Domain\Entry\Models\Fields\FieldEditLog;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventDispatcher;

class EntryGroupService
{
    public function __construct(
        private EventDispatcher            $dispatcher,
        private readonly EncryptionService $encryptionService,
        private readonly RsaService $rsaService
    ){

    }
    public function addUserToGroupAsAdmin(User $user, EntryGroup $entryGroup, ?string $encrypted_aes_password = null, ?string $master_password = null): void
    {
        $encrypted_aes_password = $this->getEncryptedAesPassword($encrypted_aes_password, $master_password, $entryGroup, $user);
        $entryGroup->addAdmin($user, $encrypted_aes_password);
    }
    public function addUserToGroupAsModerator(User $user, EntryGroup $entryGroup, ?string $encrypted_aes_password = null, ?string $master_password = null): void
    {
        $encrypted_aes_password = $this->getEncryptedAesPassword($encrypted_aes_password, $master_password, $entryGroup, $user);
        $entryGroup->addModerator($user, $encrypted_aes_password);
    }
    public function addUserToGroupAsMember(User $user, EntryGroup $entryGroup, ?string $encrypted_aes_password = null, ?string $master_password = null): void
    {
        $encrypted_aes_password = $this->getEncryptedAesPassword($encrypted_aes_password, $master_password, $entryGroup, $user);
        $entryGroup->addMember($user, $encrypted_aes_password);
    }

    /**
     * @param string|null $encrypted_aes_password
     * @param string|null $master_password
     * @param EntryGroup $entryGroup
     * @param User $user
     * @return string
     */
    private function getEncryptedAesPassword(?string $encrypted_aes_password, ?string $master_password, EntryGroup $entryGroup, User $user): string
    {
        if ($master_password === '') {
            $master_password = null;
        }
        if (is_null($encrypted_aes_password)
            && !is_null($master_password)
        ) {
            /**
             * @var User $auth_user
             */
            $auth_user = Auth::user();
            $privateKey = $this->rsaService->getUserPrivateKey($auth_user->user_id, $master_password);
            $auth_encrypted_aes_password = $entryGroup->admins()->where('user_id', $auth_user->user_id)->firstOrFail()->encrypted_aes_password->getValue();
            $decrypted_aes_password = $privateKey->decrypt($auth_encrypted_aes_password);
            $publicKey = $this->rsaService->getUserPublicKey($user);
            $encrypted_aes_password = $publicKey->encrypt($decrypted_aes_password);
        }
        if (is_null($encrypted_aes_password)
            && $entryGroup->admins()->count() === 0
        ) {
            $password = $this->encryptionService->generatePassword();
            $publicKey = $this->rsaService->getUserPublicKey($user);
            $encrypted_aes_password = $publicKey->encrypt($password);
        }

        if (!is_string($encrypted_aes_password)) {
            throw new RuntimeException('Unable to get encrypted AES password for this Group');
        }
        return $encrypted_aes_password;
    }

    public function getDecryptedAesPassword(string $master_password, EntryGroup $entryGroup): string
    {
        /**
         * @var User $auth_user
         */
        $auth_user = Auth::user();
        $privateKey = $this->rsaService->getUserPrivateKey($auth_user->user_id, $master_password);
        $auth_encrypted_aes_password = $entryGroup->users()->where('user_id', $auth_user->user_id->getValue())
            ->firstOrFail()->encrypted_aes_password->getValue();
        return $privateKey->decrypt($auth_encrypted_aes_password);
    }

    /**
     * @param Collection $entryGroupsFlatMap
     * @param bool $base64Encoded
     * @return Collection
     */
    public function groupsAsTree(Collection $entryGroupsFlatMap, bool $base64Encoded = false): Collection
    {
        $tree = collect([]);
        /**
         * @var Collection[] $treeChildren
         */
        $treeChildren = [];

        /**
         * @var EntryGroup[] $entryGroupsById
         */
        $entryGroupsById = [];
        $needToFill = [];
        foreach ($entryGroupsFlatMap as $entryGroup) {
            $entryGroupsById[$entryGroup->entry_group_id->getValue()] = [
                'entryGroup' => $entryGroup->entryGroup()->first(),
                'role' => $entryGroup->getRoleName()
            ];
            $needToFill[] = $entryGroup->entry_group_id->getValue();
        }

        foreach ($needToFill as $id) {
            if (array_key_exists($id, $treeChildren)) {
                continue;
            }
            $children = collect([]);
            while ($id) {
                if (!$entryGroupsById[$id]['entryGroup']->parentEntryGroup()->exists()) {
                    if(!$tree->contains('entry_group_id', $id)) {
                        $treeChildren[$id] = $children;
                        $tree->add(
                            collect([
                                'entry_group_id' => $id,
                                'title' => $entryGroupsById[$id]['entryGroup']->name->getValue(),
                                'materialized_path' => $entryGroupsById[$id]['entryGroup']->materialized_path->getValue(),
                                'role' => $entryGroupsById[$id]['role'],
                                'children' => $children
                            ])
                        );
                    }
                    break;
                }

                $treeChildren[$id] = $children;
                $children = collect([[
                    'entry_group_id' => $id,
                    'title' => $entryGroupsById[$id]['entryGroup']->name->getValue(),
                    'materialized_path' => $entryGroupsById[$id]['entryGroup']->materialized_path->getValue(),
                    'role' => $entryGroupsById[$id]['role'],
                    'children' => $treeChildren[$id]
                ]]);

                /**
                 * @var EntryGroup $parent
                 */
                $parent = $entryGroupsById[$id]['entryGroup']->parentEntryGroup()->first();
                $parent_id = $parent->entry_group_id->getValue();

                if (array_key_exists($parent_id, $treeChildren)) {
                    $children->each(fn ($child) => $treeChildren[$parent_id]->add($child));
                    break;
                }

                if (!array_key_exists($parent_id, $entryGroupsById)) {
                    $entryGroupsById[$parent_id] = [
                        'entryGroup' => $parent,
                        'role' => 'guest'
                    ];
                }
                $id = $parent_id;
            }
        }

        return $tree;
    }

    public function rebuildMaterializedPath(EntryGroup $entryGroup, ?EntryGroup $parentEntryGroup = null): void
    {
        $materializedPath = $entryGroup->entry_group_id->getValue();

        if (is_null($parentEntryGroup)) {
            $parent = $entryGroup->parentEntryGroup();
            if ($parent->exists()) {
                $parentEntryGroup = $parent->first();
            }
        }
        if ($parentEntryGroup) {
            $materializedPath = $parentEntryGroup->materialized_path->getValue() . '.' . $materializedPath;
        }

        $entryGroup->materialized_path = new MaterializedPath($materializedPath);
        $entryGroup->save();
        foreach ($entryGroup->entryGroups()->get() as $entryGroupChild) {
            $this->rebuildMaterializedPath($entryGroupChild, $entryGroup);
        }
    }

    /**
     * @param Field $field
     * @param string $master_password
     * @return string
     */
    public function decryptField(Field $field, string $master_password): string
    {
        $decryptedAesPassword = $this->getDecryptedAesPassword(
            master_password: $master_password,
            entryGroup: $field->entry()->firstOrFail()->entryGroup()->firstOrFail()
        );
        return $this->encryptionService->decrypt(
            data_encrypted: $field->value_encrypted->getValue(),
            decrypted_aes_password: $decryptedAesPassword,
            iv: $field->initialization_vector->getValue()
        );
    }

    /**
     * @param FieldEditLog $fieldEditLog
     * @param string $master_password
     * @return string
     */
    public function decryptFieldEditLog(FieldEditLog $fieldEditLog, string $master_password): string
    {
        /**
         * @var Field $field
         */
        $field = $fieldEditLog->field()->firstOrFail();
        $decryptedAesPassword = $this->getDecryptedAesPassword(
            master_password: $master_password,
            entryGroup: $field->entry()->firstOrFail()->entryGroup()->firstOrFail()
        );
        return $this->encryptionService->decrypt(
            data_encrypted: $fieldEditLog->value_encrypted->getValue(),
            decrypted_aes_password: $decryptedAesPassword,
            iv: $fieldEditLog->initialization_vector->getValue()
        );
    }

    /**
     * @param EntryGroup $fistGroup
     * @param EntryGroup|null $secondGroup
     * @return bool
     */
    public function isSecondGroupChildOfFirst(EntryGroup $fistGroup, ?EntryGroup $secondGroup = null): bool
    {
        if (is_null($secondGroup)) {
            return false;
        }
        return str_contains($secondGroup->materialized_path->getValue(), $fistGroup->entry_group_id->getValue());
    }
}
