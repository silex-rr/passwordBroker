<?php

namespace PasswordBroker\Application\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use PasswordBroker\Application\Http\Requests\EntryFieldStoreRequest;
use PasswordBroker\Application\Http\Requests\EntryFieldUpdateRequest;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Fields\Field;
use PasswordBroker\Domain\Entry\Services\AddFieldToEntry;
use PasswordBroker\Domain\Entry\Services\DestroyEntryField;
use PasswordBroker\Domain\Entry\Services\UpdateField;

class EntryFieldController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Field::class, ['field']);
    }

    public function index(EntryGroup $entryGroup, Entry $entry): JsonResponse
    {
        return new JsonResponse($entry->fields(), 200);
    }

    public function show(EntryGroup $entryGroup, Entry $entry, Field $field): JsonResponse
    {
        return new JsonResponse($field, 200);
    }

    public function store(EntryGroup $entryGroup, Entry $entry, EntryFieldStoreRequest $request): JsonResponse
    {
        $result = $this->dispatchSync(new AddFieldToEntry(
            entry: $entry,
            entryGroup: $entryGroup,
            type: $request->get('type'),
            title: $request->get('title', ''),
            value_encrypted: $request->get('value_encrypted'),
            initialization_vector: $request->get('initialization_vector'),
            value: $request->get('value'),
            master_password: $request->get('master_password'),
        ));

        return new JsonResponse($result, 200);
    }

    public function update(EntryGroup $entryGroup, Entry $entry, Field $field, EntryFieldUpdateRequest $request): JsonResponse
    {
        $result = $this->dispatchSync(new UpdateField(
            entry: $entry,
            entryGroup: $entryGroup,
            field:  $field,
            title: $request->get('title'),
            value_encrypted: $request->get('value_encrypted'),
            initialization_vector: $request->get('initialization_vector'),
            value: $request->get('value'),
            master_password: $request->get('master_password'),
        ));

        return new JsonResponse($result, 200);
    }

    public function destroy(EntryGroup $entryGroup, Entry $entry, Field $field): JsonResponse
    {
        $result = $this->dispatchSync(new DestroyEntryField(
            field: $field,
            entry: $entry,
            entryGroup: $entryGroup
        ));

        return new JsonResponse($result, 200);
    }
}
