<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CustomExceptionHandler extends ExceptionHandler
{
    public function render($request, Exception $exception)
    {
        if ($exception instanceof AuthorizationException) {
            return $this->handleAuthorizationException($exception, $request);
        }

        return parent::render($request, $exception);
    }

    protected function handleAuthorizationException(AuthorizationException $exception, $request)
    {
   =
        $message = 'this action is unautorized';
        $statusCode = 403; =

        return response()->json(['error' => $message], $statusCode);
    }
}
