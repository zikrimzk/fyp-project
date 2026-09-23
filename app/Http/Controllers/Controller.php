<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Services\ExceptionReference;
use Throwable;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function friendlyException(Throwable $exception, string $action = 'complete this action'): string
    {
        return app(ExceptionReference::class)->friendlyMessage($exception, $action);
    }
}
