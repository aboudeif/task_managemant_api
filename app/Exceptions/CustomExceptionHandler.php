<?php

// app/Exceptions/CustomExceptionHandler.php

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
        // Your custom logic to handle AuthorizationException
        $message = $exception->getMessage();
        $statusCode = 403; // You can customize the status code

        if ($request->expectsJson()) {
            return response()->json(['error' => $message], $statusCode);
        }

        // For non-JSON responses, you can redirect or return a view
        // Example: return redirect()->route('login');
        // Example: return view('errors.custom', ['message' => $message, 'status' => $statusCode]);

        // Fallback to the default Laravel behavior for non-JSON responses
        return parent::render($request, $exception);
    }
}
