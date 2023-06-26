<?php

namespace PasswordBroker\Application\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use PasswordBroker\Application\Http\Requests\EntryFieldDecryptedRequest;
use PasswordBroker\Application\Services\EncryptionService;
use PasswordBroker\Application\Services\EntryGroupService;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Fields\Field;
use PasswordBroker\Domain\Entry\Models\Fields\FieldEditLog;
use phpseclib3\Exception\NoKeyLoadedException;
use Symfony\Component\Mime\Encoder\Base64Encoder;

class EntryFieldHistoryController extends Controller
{
    public function __construct(
        private readonly EncryptionService $encryptionService,
        private readonly EntryGroupService $entryGroupService,
        private readonly Base64Encoder $base64Encoder
    )
    {
        $this->authorizeResource(Field::class, ['field']);
    }

    public function index(EntryGroup $entryGroup, Entry $entry, Field $field): JsonResponse
    {
        return new JsonResponse($field->fieldEditLogs()->get(), 200);
    }

    public function showDecrypted(
        EntryGroup $entryGroup,
        Entry $entry,
        Field $field,
        FieldEditLog $fieldEditLog,
        EntryFieldDecryptedRequest $request
    ): JsonResponse|Response
    {
        try {
            return new Response(
                $this->base64Encoder->encodeString(
                    $this->entryGroupService->decryptFieldEditLog($fieldEditLog, $request->getMasterPassword())
                )
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
}
