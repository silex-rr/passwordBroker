<?php

namespace PasswordBroker\Application\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use PasswordBroker\Application\Http\Requests\EntryMoveRequest;
use PasswordBroker\Application\Http\Requests\EntryRequest;
use PasswordBroker\Application\Services\EntryService;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Services\AddEntry;
use PasswordBroker\Domain\Entry\Services\DestroyEntry;
use PasswordBroker\Domain\Entry\Services\MoveEntry;
use PasswordBroker\Domain\Entry\Services\UpdateEntry;
use PasswordBroker\Infrastructure\Validation\Handlers\EntryValidationHandler;

class EntryController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct(private readonly EntryService $entryService)
    {
        $this->authorizeResource(Entry::class, ['entry']);
    }

    public function index(EntryGroup $entryGroup): JsonResponse
    {
        return new JsonResponse($entryGroup->entries()->get(), 200);
    }

    public function store(EntryRequest $request): JsonResponse
    {

        /**
         * @var Entry $entry
         */
        $entry = Entry::hydrate([$request->all()])->first();
        $entry->exists = false;

        $response = $this->dispatchSync(new AddEntry($entry, $request->entryGroup, new EntryValidationHandler()));

        return new JsonResponse($response, 200);
    }

    public function show(): JsonResponse
    {
        return new JsonResponse(['data'], 200);
    }

    public function update(EntryGroup $entryGroup, Entry $entry, EntryRequest $request): JsonResponse
    {
        $response = $this->dispatchSync(new UpdateEntry(
                entry: $entry,
                entryGroup: $entryGroup,
                attributes: $request->allWithCasts(),
                entryValidationHandler: new EntryValidationHandler())
        );
        return new JsonResponse($response, 200);
    }

    public function move(EntryGroup $entryGroupSource, Entry $entry, EntryMoveRequest $entryMoveRequest): JsonResponse
    {
        $response = $this->dispatchSync(new MoveEntry(
                entry: $entry,
                entryGroupSource: $entryGroupSource,
                entryGroupTarget: $entryMoveRequest->entryGroupTarget(),
                master_password: $entryMoveRequest->get('master_password')
            )
        );

        return new JsonResponse($response, 200);
    }

    public function destroy(EntryGroup $entryGroup, Entry $entry): JsonResponse
    {
        $response = $this->dispatchSync(new DestroyEntry(
            entry: $entry,
            entryGroup: $entryGroup
        ));

        return new JsonResponse($response, 200);
    }

    protected function resourceAbilityMap(): array
    {
        $resourceAbilityMap = parent::resourceAbilityMap();
        $resourceAbilityMap['move'] = 'move';
        return $resourceAbilityMap;
    }
}
