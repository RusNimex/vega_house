<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * {@inheritdoc}
     */
    protected $dontReport = [
        ValidationException::class,
        NotFoundHttpException::class,
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        //
    }

    /**
     * Render an exception into an HTTP response.
     * @throws Throwable
     */
    public function render($request, Throwable $e): JsonResponse|Response
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            return $this->handleApiException($e, $request);
        }

        return parent::render($request, $e);
    }

    /**
     * Обработка исключений для API
     *
     * @throws Throwable
     */
    protected function handleApiException(Throwable $e, $request): JsonResponse
    {

        if ($e instanceof ValidationException) {
            $errors = $e->errors();
            $firstError = !empty($errors) && !empty(reset($errors))
                ? reset($errors)[0]
                : 'The given data was invalid.';

            return response()->json([
                'message' => $firstError,
                'errors' => $errors
            ], 422);
        }

        if ($e instanceof AuthenticationException) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($e instanceof NotFoundHttpException) {
            return response()->json([
                'error' => 'Resource not found',
                'message' => config('app.debug')
                    ? $e->getMessage()
                    : 'The requested resource could not be found.'
            ], 404);
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            return response()->json([
                'error' => 'Method not allowed',
                'message' => 'The HTTP method is not allowed for this endpoint.'
            ], 405);
        }

        if ($e instanceof QueryException) {
            $message = config('app.debug')
                ? $e->getMessage()
                : 'Database error occurred.';

            $response = [
                'error' => 'Database error',
                'message' => $message,
                'user' => $request->user(),
                'trace' => $e->getTrace()
            ];

            if (config('app.debug')) {
                $response['trace'] = $e->getTrace();
                $response['user'] = $request->user();
            }

            return response()->json($response, 500);
        }

        // Общая обработка
        $statusCode = method_exists($e, 'getStatusCode')
            ? $e->getStatusCode()
            : 500;

        $response = [
            'error' => $this->getErrorType($statusCode),
            'message' => config('app.debug') ? $e->getMessage() : 'An error occurred'
        ];

        if (config('app.debug')) {
            $response['exception'] = get_class($e);
            $response['file'] = $e->getFile();
            $response['line'] = $e->getLine();
            $response['trace'] = $e->getTrace();
            $response['user'] = $request->user();
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Тип ошибки по HTTP коду
     */
    private function getErrorType(int $code): string
    {
        return match($code) {
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            422 => 'Validation Error',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            503 => 'Service Unavailable',
            default => 'Error'
        };
    }
}

