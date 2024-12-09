<?php

namespace PasswordBroker\Application\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Identity\Domain\User\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenApi\Attributes\Delete;
use OpenApi\Attributes\Get;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\MediaType;
use OpenApi\Attributes\PathParameter;
use OpenApi\Attributes\Post;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Put;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Response as ResponseOA;
use OpenApi\Attributes\Schema;
use PasswordBroker\Application\Events\FieldDecrypted;
use PasswordBroker\Application\Http\Requests\EntryFieldDecryptedRequest;
use PasswordBroker\Application\Http\Requests\EntryFieldDestroyRequest;
use PasswordBroker\Application\Http\Requests\EntryFieldStoreRequest;
use PasswordBroker\Application\Http\Requests\EntryFieldUpdateRequest;
use PasswordBroker\Application\Services\EncryptionService;
use PasswordBroker\Application\Services\EntryGroupService;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Fields\Attributes\TOTPHashAlgorithm;
use PasswordBroker\Domain\Entry\Models\Fields\Field;
use PasswordBroker\Domain\Entry\Models\Fields\Password;
use PasswordBroker\Domain\Entry\Services\AddFieldToEntry;
use PasswordBroker\Domain\Entry\Services\DestroyEntryField;
use PasswordBroker\Domain\Entry\Services\UpdateField;
use PasswordBroker\Infrastructure\Services\TimeBasedOneTimePasswordGenerator;
use phpseclib3\Exception\NoKeyLoadedException;
use Symfony\Component\Mime\Encoder\Base64Encoder;

class EntryFieldController extends Controller
{
    public function __construct(
        private readonly EntryGroupService $entryGroupService,
        private readonly Base64Encoder     $base64Encoder,
    ) {
        $this->authorizeResource(Field::class, ['field']);
    }

    protected function resourceAbilityMap(): array
    {
        $resourceAbilityMap = parent::resourceAbilityMap();
        $resourceAbilityMap['showDecrypted'] = 'view';

        return $resourceAbilityMap;
    }


    #[Get(
        path      : "/passwordBroker/api/entryGroups/{entryGroup:entry_group_id}/entries/{entry:entry_id}/fields",
        summary   : "List of Fields that belong to an Entry",
        tags      : ["PasswordBroker_EntryFieldController"],
        parameters: [
            new PathParameter(name  : "entryGroup:entry_group_id", required: true,
                              schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntryGroupId")),
            new PathParameter(name  : "entry:entry_id", required: true,
                              schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntryId")),
        ],
        responses : [
            new ResponseOA(
                response   : 200,
                description: "List of Fields",
                content    : new JsonContent(
                    type : "array",
                    items: new Items(oneOf: [
                        new Schema(ref: "#/components/schemas/PasswordBroker_File"),
                        new Schema(ref: "#/components/schemas/PasswordBroker_Link"),
                        new Schema(ref: "#/components/schemas/PasswordBroker_Note"),
                        new Schema(ref: "#/components/schemas/PasswordBroker_Password"),
                        new Schema(ref: "#/components/schemas/PasswordBroker_TOTP"),
                    ]),
                ),
            ),
        ],
    )]
    public function index(EntryGroup $entryGroup, Entry $entry): JsonResponse
    {
        return new JsonResponse($entry->fields(), 200);
    }

    #[Get(
        path      : "/passwordBroker/api/entryGroups/{entryGroup:entry_group_id}/entries/{entry:entry_id}/fields/{field:field_id}",
        summary   : "Get a Field",
        tags      : ["PasswordBroker_EntryFieldController"],
        parameters: [
            new PathParameter(name  : "entryGroup:entry_group_id", required: true,
                              schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntryGroupId")),
            new PathParameter(name  : "entry:entry_id", required: true,
                              schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntryId")),
            new PathParameter(name  : "field:field_id", required: true,
                              schema: new Schema(ref: "#/components/schemas/PasswordBroker_FieldId")),
        ],
        responses : [
            new ResponseOA(
                response   : 200,
                description: "Field object",
                content    : new JsonContent(
                    type : "object",
                    oneOf: [
                        new Schema(ref: "#/components/schemas/PasswordBroker_File"),
                        new Schema(ref: "#/components/schemas/PasswordBroker_Link"),
                        new Schema(ref: "#/components/schemas/PasswordBroker_Note"),
                        new Schema(ref: "#/components/schemas/PasswordBroker_Password"),
                        new Schema(ref: "#/components/schemas/PasswordBroker_TOTP"),
                    ],
                ),
            ),
        ],
    )]
    public function show(EntryGroup $entryGroup, Entry $entry, Field $field): JsonResponse
    {
        return new JsonResponse($field, 200);
    }

    #[Post(
        path       : "/passwordBroker/api/entryGroups/{entryGroup:entry_group_id}/entries/{entry:entry_id}/fields/{field:field_id}/totp",
        summary    : "Get a Temporary One Time Password",
        requestBody: new RequestBody(
            content: new MediaType(
                mediaType: "multipart/form-data",
                schema   : new Schema(ref: "#/components/schemas/PasswordBroker_EntryFieldDecryptedRequest"),
            ),
        ),
        tags       : ["PasswordBroker_EntryFieldController"],
        parameters : [
            new PathParameter(name  : "entryGroup:entry_group_id", required: true,
                              schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntryGroupId")),
            new PathParameter(name  : "entry:entry_id", required: true,
                              schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntryId")),
            new PathParameter(name  : "field:field_id", required: true,
                              schema: new Schema(ref: "#/components/schemas/PasswordBroker_FieldId")),
        ],
        responses  : [
            new ResponseOA(
                response   : 200,
                description: "TOTP with some data",
                content    : new JsonContent(
                    properties: [
                        new Property(property: "time", type: "string", format: "date-time",),
                        new Property(property: "code", type: "integer",),
                        new Property(property: "epoch", type: "string",),
                        new Property(property: "period", type: "string",),
                        new Property(property: "expiresIn", type: "integer",),
                        new Property(property: "expiresAt", type: "string", format: "date-time",),
                    ],
                    type      : "object",
                ),
            ),
            new ResponseOA(
                response   : 422,
                description: "MasterPassword is wrong",
            ),
        ],
    )]
    public function showTOTP(EntryGroup                 $entryGroup,
                             Entry                      $entry,
                             Field                      $field,
                             EntryFieldDecryptedRequest $request,
    ): JsonResponse {
        try {
            $decryptField = $this->entryGroupService->decryptField(field          : $field,
                                                                   master_password: $request->getMasterPassword());

            event(new FieldDecrypted(field: $field));


            $timeBasedOneTimePasswordGenerator = new TimeBasedOneTimePasswordGenerator();
            $TOTP = $timeBasedOneTimePasswordGenerator->generate($decryptField);

            $carbon = Carbon::now();

            return new JsonResponse(
                [
                    'time' => $carbon->format('c'),
                    'code' => $TOTP->now(),
                    'epoch' => $TOTP->getEpoch(),
                    'period' => $TOTP->getPeriod(),
                    'expiresIn' => $TOTP->expiresIn(),
                    'expiresAt' => $carbon->add('second', $TOTP->expiresIn())->format('c'),
                ]
                , 200);
        } catch (NoKeyLoadedException $exception) {
            return new JsonResponse([
                'message' => "MasterPassword is invalid",
                'errors' => [
                    'master_password' => 'invalid',
                ],
            ], 422);
        }

    }

    #[Post(
        path       : "/passwordBroker/api/entryGroups/{entryGroup:entry_group_id}/entries/{entry:entry_id}/fields/{field:field_id}/decrypted",
        summary    : "Get a decrypted value",
        requestBody: new RequestBody(
            content: new MediaType(
                mediaType: "multipart/form-data",
                schema   : new Schema(ref: "#/components/schemas/PasswordBroker_EntryFieldDecryptedRequest"),
            ),
        ),
        tags       : ["PasswordBroker_EntryFieldController"],
        parameters : [
            new PathParameter(name  : "entryGroup:entry_group_id", required: true,
                              schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntryGroupId")),
            new PathParameter(name  : "entry:entry_id", required: true,
                              schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntryId")),
            new PathParameter(name  : "field:field_id", required: true,
                              schema: new Schema(ref: "#/components/schemas/PasswordBroker_FieldId")),
        ],
        responses  : [
            new ResponseOA(
                response   : 200,
                description: "Decrypted value encoded in base64",
                content    : new MediaType(
                    mediaType: "text/plain"
                )
            ),
            new ResponseOA(
                response   : 422,
                description: "MasterPassword is wrong",
            ),
        ],
    )]
    public function showDecrypted(
        EntryGroup                 $entryGroup,
        Entry                      $entry,
        Field                      $field,
        EntryFieldDecryptedRequest $request,
    ): JsonResponse|Response {
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
                ],
            ], 422);
        }
    }

    #[Post(
        path       : "/passwordBroker/api/entryGroups/{entryGroup:entry_group_id}/entries/{entry:entry_id}/fields",
        summary    : "Add new Field to an Entry",
        requestBody: new RequestBody(
            content: new MediaType(
                mediaType: "multipart/form-data",
                schema   : new Schema(ref: "#/components/schemas/PasswordBroker_EntryFieldStoreRequest"),
            ),
        ),
        tags       : ["PasswordBroker_EntryFieldController"],
        parameters : [
            new PathParameter(name  : "entryGroup:entry_group_id", required: true,
                              schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntryGroupId")),
            new PathParameter(name  : "entry:entry_id", required: true,
                              schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntryId")),
        ],
        responses  : [
            new ResponseOA(
                response   : 200,
                description: "Field successfully created",
            ),
            new ResponseOA(
                response   : 422,
                description: "MasterPassword is wrong",
            ),
        ],
    )]
    public function store(EntryGroup $entryGroup, Entry $entry, EntryFieldStoreRequest $request): JsonResponse
    {
        try {
            $result = $this->dispatchSync(new AddFieldToEntry(
                entry                : $entry,
                entryGroup           : $entryGroup,
                type                 : $request->get('type'),
                title                : $request->get('title', ''),
                value_encrypted      : $request->get('value_encrypted'),
                initialization_vector: $request->get('initialization_vector'),
                value                : base64_encode($request->get('value')),
                file                 : $request->file('file'),
                file_name            : null,
                file_size            : null,
                file_mime            : null,
                login                : $request->get('login'),
                totp_hash_algorithm  : $request->get('totp_hash_algorithm'),
                totp_timeout         : $request->get('totp_timeout'),
                master_password      : $request->get('master_password'),
            ));
        } catch (NoKeyLoadedException $exception) {
            return new JsonResponse([
                'message' => "MasterPassword is invalid",
                'errors' => [
                    'master_password' => 'invalid',
                ],
            ], 422);
        }


        return new JsonResponse($result, 200);
    }

    #[Put(
        path       : "/passwordBroker/api/entryGroups/{entryGroup:entry_group_id}/entries/{entry:entry_id}/fields/{field:field_id}",
        summary    : "Update a Field",
        requestBody: new RequestBody(
            content: new MediaType(
                mediaType: "multipart/form-data",
                schema   : new Schema(ref: "#/components/schemas/PasswordBroker_EntryFieldUpdateRequest"),
            ),
        ),
        tags       : ["PasswordBroker_EntryFieldController"],
        parameters : [
            new PathParameter(name  : "entryGroup:entry_group_id", required: true,
                              schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntryGroupId")),
            new PathParameter(name  : "entry:entry_id", required: true,
                              schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntryId")),
            new PathParameter(name  : "field:field_id", required: true,
                              schema: new Schema(ref: "#/components/schemas/PasswordBroker_FieldId")),
        ],
        responses  : [
            new ResponseOA(
                response   : 200,
                description: "A field was updated",
            ),
            new ResponseOA(
                response   : 422,
                description: "MasterPassword is wrong",
            ),
        ],
    )]
    public function update(EntryGroup              $entryGroup,
                           Entry                   $entry,
                           Field                   $field,
                           EntryFieldUpdateRequest $request,
    ): JsonResponse {
        /**
         * @var User $user
         */
        $user = auth()->user();
        $result = $this->dispatchSync(new UpdateField(
            user                 : $user,
            entry                : $entry,
            entryGroup           : $entryGroup,
            field                : $field,
            title                : $request->get('title'),
            value_encrypted      : $request->get('value_encrypted'),
            initialization_vector: $request->get('initialization_vector'),
            login                : $field->getType() === Password::TYPE ? $request->get('login') : null,
            value                : $request->get('value'),
            master_password      : $request->get('master_password'),
        ));

        return new JsonResponse($result, 200);
    }

    #[Delete(
        path       : "/passwordBroker/api/entryGroups/{entryGroup:entry_group_id}/entries/{entry:entry_id}/fields/{field:field_id}",
        summary    : "Delete a Field (mark as deleted)",
        requestBody: new RequestBody(
            content: new MediaType(
                mediaType: "multipart/form-data",
                schema   : new Schema(ref: "#/components/schemas/PasswordBroker_EntryFieldDestroyRequest"),
            ),
        ),
        tags       : ["PasswordBroker_EntryFieldController"],
        parameters : [
            new PathParameter(name  : "entryGroup:entry_group_id", required: true,
                              schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntryGroupId")),
            new PathParameter(name  : "entry:entry_id", required: true,
                              schema: new Schema(ref: "#/components/schemas/PasswordBroker_EntryId")),
            new PathParameter(name  : "field:field_id", required: true,
                              schema: new Schema(ref: "#/components/schemas/PasswordBroker_FieldId")),
        ],
        responses  : [
            new ResponseOA(
                response   : 200,
                description: "A field was deleted (marked as deleted)",
            ),
            new ResponseOA(
                response   : 422,
                description: "MasterPassword is wrong",
            ),
        ],
    )]
    public function destroy(EntryGroup               $entryGroup,
                            Entry                    $entry,
                            Field                    $field,
                            EntryFieldDestroyRequest $request,
    ): JsonResponse {
        try {
            $this->entryGroupService->decryptField(field: $field, master_password: $request->getMasterPassword());
        } catch (NoKeyLoadedException $exception) {
            return new JsonResponse([
                'message' => "MasterPassword is invalid",
                'errors' => [
                    'master_password' => 'invalid',
                ],
            ], 422);
        }

        $result = $this->dispatchSync(new DestroyEntryField(
            field          : $field,
            entry          : $entry,
            entryGroup     : $entryGroup,
            master_password: $request->master_password
        ));

        return new JsonResponse($result, 200);
    }
}
