<?php

namespace PasswordBroker\Application\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use PasswordBroker\Application\Http\Requests\EntrySearchRequest;
use PasswordBroker\Domain\Entry\Services\SearchEntry;


class EntrySearchController extends Controller
{

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public function index(EntrySearchRequest $request): JsonResponse
    {
        $searchEntry = new SearchEntry(
            query: $request->getQuery(),
            perPage: $request->getPerPage(),
            page: $request->getPage(),
        );

        return new JsonResponse($this->dispatchSync($searchEntry), 200);
    }
    //    public function index(UsersSearchRequest $request): JsonResponse
    //    {
    //
    //        $job = new SearchUsers(
    //            query: $request->getQuery(),
    //            perPage: $request->getPerPage(),
    //            page: $request->getPage(),
}
