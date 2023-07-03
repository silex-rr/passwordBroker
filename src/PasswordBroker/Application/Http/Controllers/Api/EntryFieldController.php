<?php

namespace PasswordBroker\Application\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use PasswordBroker\Application\Events\FieldDecrypted;
use PasswordBroker\Application\Http\Requests\EntryFieldDecryptedRequest;
use PasswordBroker\Application\Http\Requests\EntryFieldDestroyRequest;
use PasswordBroker\Application\Http\Requests\EntryFieldStoreRequest;
use PasswordBroker\Application\Http\Requests\EntryFieldUpdateRequest;
use PasswordBroker\Application\Services\EncryptionService;
use PasswordBroker\Application\Services\EntryGroupService;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Fields\Field;
use PasswordBroker\Domain\Entry\Models\Fields\Password;
use PasswordBroker\Domain\Entry\Services\AddFieldToEntry;
use PasswordBroker\Domain\Entry\Services\DestroyEntryField;
use PasswordBroker\Domain\Entry\Services\UpdateField;
use phpseclib3\Exception\NoKeyLoadedException;
use Symfony\Component\Mime\Encoder\Base64Encoder;

class EntryFieldController extends Controller
{
    public function __construct(
        private readonly EncryptionService $encryptionService,
        private readonly EntryGroupService $entryGroupService,
        private readonly Base64Encoder $base64Encoder
    )
    {
        $this->authorizeResource(Field::class, ['field']);
    }

    protected function resourceAbilityMap(): array
    {
        $resourceAbilityMap = parent::resourceAbilityMap();
        $resourceAbilityMap['showDecrypted'] = 'view';
        return $resourceAbilityMap;
    }

    public function index(EntryGroup $entryGroup, Entry $entry): JsonResponse
    {
        return new JsonResponse($entry->fields(), 200);
    }

    public function show(EntryGroup $entryGroup, Entry $entry, Field $field): JsonResponse
    {
        return new JsonResponse($field, 200);
    }

    public function showDecrypted(EntryGroup $entryGroup, Entry $entry, Field $field, EntryFieldDecryptedRequest $request)
        : JsonResponse|Response
    {
        try {
            $encodeString = $this->base64Encoder->encodeString(
                $this->entryGroupService->decryptField(field: $field, master_password: $request->getMasterPassword())
            );

            event(new FieldDecrypted(field: $field));

            return new Response(
//                [
//                    'value_decrypted_base64' =>
                    $encodeString//,
//                    'field' => $field
//                ]
                , 200);
        } catch (NoKeyLoadedException $exception) {
            return new JsonResponse([
                'message' => "MasterPassword is invalid",
                'errors' => [
                    'master_password' => 'invalid',
                ]
            ], 422);
        }
    }

    public function store(EntryGroup $entryGroup, Entry $entry, EntryFieldStoreRequest $request): JsonResponse
    {
        try {
            $result = $this->dispatchSync(new AddFieldToEntry(
                entry: $entry,
                entryGroup: $entryGroup,
                type: $request->get('type'),
                title: $request->get('title', ''),
                value_encrypted: $request->get('value_encrypted'),
                initialization_vector: $request->get('initialization_vector'),
                value: base64_encode($request->get('value')),
                file: $request->file('file'),
                file_name: null,
                file_size: null,
                file_mime: null,
                login: $request->get('login'),
                master_password: $request->get('master_password'),
            ));
        } catch (NoKeyLoadedException $exception) {
            return new JsonResponse([
                'message' => "MasterPassword is invalid",
                'errors' => [
                    'master_password' => 'invalid'
                ]
            ], 422);
        } catch (\Exception $e) {dd($e);}


        return new JsonResponse($result, 200);
    }

    public function update(EntryGroup $entryGroup, Entry $entry, Field $field, EntryFieldUpdateRequest $request): JsonResponse
    {
        $result = $this->dispatchSync(new UpdateField(
            user: auth()->user(),
            entry: $entry,
            entryGroup: $entryGroup,
            field: $field,
            title: $request->get('title'),
            value_encrypted: $request->get('value_encrypted'),
            initialization_vector: $request->get('initialization_vector'),
            login: $field->getType() === Password::TYPE ? $request->get('login'): null,
            value: $request->get('value'),
            master_password: $request->get('master_password'),
        ));

        return new JsonResponse($result, 200);
    }

    public function destroy(EntryGroup $entryGroup, Entry $entry, Field $field, EntryFieldDestroyRequest $request): JsonResponse
    {
        try {
                $this->entryGroupService->decryptField(field: $field, master_password: $request->getMasterPassword());
        } catch (NoKeyLoadedException $exception) {
            return new JsonResponse([
                'message' => "MasterPassword is invalid",
                'errors' => [
                    'master_password' => 'invalid',
                ]
            ], 422);
        }

        $result = $this->dispatchSync(new DestroyEntryField(
            field: $field,
            entry: $entry,
            entryGroup: $entryGroup,
            master_password: $request->master_password
        ));

        return new JsonResponse($result, 200);
    }
}
