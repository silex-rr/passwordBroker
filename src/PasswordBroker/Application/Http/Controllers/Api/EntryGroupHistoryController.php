<?php

namespace PasswordBroker\Application\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use PasswordBroker\Application\Http\Requests\EntryFieldHistorySearchRequest;
use PasswordBroker\Application\Services\SearchEntryFieldHistory;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Fields\EntryFieldHistory;
use PasswordBroker\Domain\Entry\Models\Fields\Field;

class EntryGroupHistoryController extends Controller
{

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function resourceAbilityMap(): array
    {
        $resourceAbilityMap = parent::resourceAbilityMap();
        $resourceAbilityMap['search'] = 'search';
        return $resourceAbilityMap;
    }

    public function index(EntryGroup $entryGroup): JsonResponse
    {
        return new JsonResponse(EntryFieldHistory::with(['field.entry', 'user'])->belongToEntryGroup($entryGroup)->get());
    }

    public function search(EntryFieldHistorySearchRequest $request): JsonResponse
    {
        $job = new SearchEntryFieldHistory(
            query: $request->getQuery(),
            perPage: $request->getPerPage(),
            page: $request->getPage(),
            entryGroupInclude: $request->getEntryGroupInclude(),
            entryGroupExclude: $request->getEntryGroupExclude()
        );

        return new JsonResponse($this->dispatchSync($job), 200);
    }
}
