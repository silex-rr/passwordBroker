<?php

namespace PasswordBroker\Application\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Fields\EntryFieldHistory;

class EntryGroupHistoryController extends Controller
{
    public function index(EntryGroup $entryGroup): JsonResponse
    {
        return new JsonResponse(EntryFieldHistory::with(['field.entry', 'user'])->belongToEntryGroup($entryGroup)->get());
    }
}
