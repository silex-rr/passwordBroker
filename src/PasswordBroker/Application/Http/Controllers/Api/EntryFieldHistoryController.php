<?php

namespace PasswordBroker\Application\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenApi\Attributes\Get;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\MediaType;
use OpenApi\Attributes\PathParameter;
use OpenApi\Attributes\Post;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Response as ResponseOA;
use OpenApi\Attributes\Schema;
use PasswordBroker\Application\Http\Requests\EntryFieldDecryptedRequest;
use PasswordBroker\Application\Services\EncryptionService;
use PasswordBroker\Application\Services\EntryGroupService;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Fields\Field;
use PasswordBroker\Domain\Entry\Models\Fields\EntryFieldHistory;
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

    #[Get(
        path: "/passwordBroker/api/entryGroups/{entryGroup:entry_group_id}/entries/{entry:entry_id}/fields/{field:field_id}/history",
        summary: "List of changes of a Field",
        tags: ["PasswordBroker_EntryFieldHistoryController"],
        parameters: [
            new PathParameter(parameter: "{entryGroup:entry_group_id}", ref: "#/components/schemas/PasswordBroker_EntryGroupId"),
            new PathParameter(parameter: "{entry:entry_id}", ref: "#/components/schemas/PasswordBroker_EntryId"),
            new PathParameter(parameter: "{field:field_id}", ref: "#/components/schemas/PasswordBroker_FieldId"),
        ],
        responses: [
            new ResponseOA(
                response: 200,
                description: "List of Field changes",
                content: new JsonContent(
                    type: "array",
                    items: new Items(ref: "#/components/schemas/PasswordBroker_EntryFieldHistory"),
                )
            ),
        ],
    )]
    public function index(EntryGroup $entryGroup, Entry $entry, Field $field): JsonResponse
    {
        return new JsonResponse($field->fieldHistories()->with('User')->orderByDesc('created_at')->get(), 200);
    }

    #[Post(
        path: "/passwordBroker/api/entryGroups/{entryGroup:entry_group_id}/entries/{entry:entry_id}/fields/{field:field_id}/history/{fieldEditLog:field_edit_log_id}/decrypted",
        summary: "Get a decrypted value of a Field History",
        requestBody: new RequestBody(
            content: new MediaType(
                mediaType: "multipart/form-data",
                schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntryFieldDecryptedRequest"),
            ),
        ),
        tags: ["PasswordBroker_EntryFieldHistoryController"],
        parameters: [
            new PathParameter(parameter: "{entryGroup:entry_group_id}", ref: "#/components/schemas/PasswordBroker_EntryGroupId"),
            new PathParameter(parameter: "{entry:entry_id}", ref: "#/components/schemas/PasswordBroker_EntryId"),
            new PathParameter(parameter: "{field:field_id}", ref: "#/components/schemas/PasswordBroker_FieldId"),
            new PathParameter(parameter: "{fieldEditLog:field_edit_log_id}", ref: "#/components/schemas/PasswordBroker_EntryFieldHistory"),
        ],
        responses: [
            new ResponseOA(
                response: 200,
                description: "Decrypted value encoded in base64",
                content: new MediaType(
                    mediaType: "text/plain"
                )
            ),
            new ResponseOA(
                response: 422,
                description: "MasterPassword is wrong",
            ),
        ],
    )]
    public function showDecrypted(
        EntryGroup                 $entryGroup,
        Entry                      $entry,
        Field                      $field,
        EntryFieldHistory          $fieldHistory,
        EntryFieldDecryptedRequest $request
    ): JsonResponse|Response
    {
        try {
            return new Response(
                $this->base64Encoder->encodeString(
                    $this->entryGroupService->decryptFieldEditLog($fieldHistory, $request->getMasterPassword())
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
