<?php

namespace PasswordBroker\Application\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Bus\Dispatcher;
use Illuminate\Http\JsonResponse;
use PasswordBroker\Application\Http\Requests\ImportRequest;
use PasswordBroker\Domain\Entry\Services\ImportKeePassXML;

class ImportController extends Controller
{
    public function store(ImportRequest $request): JsonResponse
    {
        $this->dispatchSync(
            new ImportKeePassXML(
                filePath: $request->file('file')?->getRealPath(),
                masterPassword: $request->get('master_password'),
                dispatcher: app(Dispatcher::class)
            )
        );

        return new JsonResponse(null, 200);
    }
}
