<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;

class ApiResponse implements Responsable
{
    /**
     * @var bool
     */
    protected $success;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var array|null
     */
    protected $errors;

    /**
     * @var int
     */
    protected $statusCode;

    /**
     * @var array
     */
    protected $headers;

    /**
     * @var array
     */
    protected $meta;

    /**
     * @var string
     */
    protected $customStatusCode;
    
    /**
     * @var string|null
     */
    protected $translationKey;

    /**
     * Create a new API response instance.
     *
     * @param bool $success
     * @param string $message
     * @param mixed $data
     * @param array|null $errors
     * @param int $statusCode
     * @param array $headers
     * @param array $meta
     * @param string $customStatusCode
     * @param string|null $translationKey
     */
    public function __construct(
        bool $success = true,
        string $message = '',
        mixed $data = null,
        ?array $errors = null,
        int $statusCode = 200,
        array $headers = [],
        array $meta = [],
        string $customStatusCode = '',
        ?string $translationKey = null
    ) {
        $this->success = $success;
        $this->message = $message;
        $this->data = $data;
        $this->errors = $errors;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->meta = $meta;
        $this->customStatusCode = $customStatusCode ?: (string) $statusCode;
        $this->translationKey = $translationKey ?: $this->customStatusCode;
    }

    /**
     * Create an API success response.
     *
     * @param string $message
     * @param mixed $data
     * @param int $statusCode
     * @param array $headers
     * @param array $meta
     * @param string $customStatusCode
     * @param string|null $translationKey
     * @return self
     */
    public static function success(string $message = 'Success', mixed $data = null, int $statusCode = 200, array $headers = [], array $meta = [], string $customStatusCode = '', ?string $translationKey = null): self
    {
        return new self(true, $message, $data, null, $statusCode, $headers, $meta, $customStatusCode, $translationKey);
    }

    /**
     * Create an API error response.
     *
     * @param string $message
     * @param array|null $errors
     * @param int $statusCode
     * @param array $headers
     * @param array $meta
     * @param string $customStatusCode
     * @param string|null $translationKey
     * @return self
     */
    public static function error(string $message = 'Error', ?array $errors = null, int $statusCode = 400, array $headers = [], array $meta = [], string $customStatusCode = '', ?string $translationKey = null): self
    {
        return new self(false, $message, null, $errors, $statusCode, $headers, $meta, $customStatusCode, $translationKey);
    }

    /**
     * Return a 200 OK response.
     *
     * @param mixed $data
     * @param string $message
     * @param array $meta
     * @param string $customStatusCode
     * @param string|null $translationKey
     * @return self
     */
    public static function ok(mixed $data = null, string $message = 'OK', array $meta = [], string $customStatusCode = 'OK_200', ?string $translationKey = null): self
    {
        return self::success($message, $data, 200, [], $meta, $customStatusCode, $translationKey);
    }

    /**
     * Return a 201 Created response.
     *
     * @param mixed $data
     * @param string $message
     * @param array $meta
     * @param string $customStatusCode
     * @param string|null $translationKey
     * @return self
     */
    public static function created(mixed $data = null, string $message = 'Created successfully', array $meta = [], string $customStatusCode = 'CREATED_201', ?string $translationKey = null): self
    {
        return self::success($message, $data, 201, [], $meta, $customStatusCode, $translationKey);
    }

    /**
     * Return a 202 Accepted response.
     *
     * @param mixed $data
     * @param string $message
     * @param array $meta
     * @param string $customStatusCode
     * @param string|null $translationKey
     * @return self
     */
    public static function accepted(mixed $data = null, string $message = 'Accepted', array $meta = [], string $customStatusCode = 'ACCEPTED_202', ?string $translationKey = null): self
    {
        return self::success($message, $data, 202, [], $meta, $customStatusCode, $translationKey);
    }

    /**
     * Return a 204 No Content response.
     *
     * @param string $message
     * @param string $customStatusCode
     * @param string|null $translationKey
     * @return self
     */
    public static function noContent(string $message = 'No content', string $customStatusCode = 'NO_CONTENT_204', ?string $translationKey = null): self
    {
        return self::success($message, null, 204, [], [], $customStatusCode, $translationKey);
    }

    /**
     * Return a 400 Bad Request response.
     *
     * @param string $message
     * @param array|null $errors
     * @param string $customStatusCode
     * @param string|null $translationKey
     * @return self
     */
    public static function badRequest(string $message = 'Bad request', ?array $errors = null, string $customStatusCode = 'BAD_REQUEST_400', ?string $translationKey = null): self
    {
        return self::error($message, $errors, 400, [], [], $customStatusCode, $translationKey);
    }

    /**
     * Return a 401 Unauthorized response.
     *
     * @param string $message
     * @param array|null $errors
     * @param string $customStatusCode
     * @param string|null $translationKey
     * @return self
     */
    public static function unauthorized(string $message = 'Unauthorized', ?array $errors = null, string $customStatusCode = 'UNAUTHORIZED_401', ?string $translationKey = null): self
    {
        return self::error($message, $errors, 401, [], [], $customStatusCode, $translationKey);
    }

    /**
     * Return a 403 Forbidden response.
     *
     * @param string $message
     * @param array|null $errors
     * @param string $customStatusCode
     * @param string|null $translationKey
     * @return self
     */
    public static function forbidden(string $message = 'Forbidden', ?array $errors = null, string $customStatusCode = 'FORBIDDEN_403', ?string $translationKey = null): self
    {
        return self::error($message, $errors, 403, [], [], $customStatusCode, $translationKey);
    }

    /**
     * Return a 404 Not Found response.
     *
     * @param string $message
     * @param array|null $errors
     * @param string $customStatusCode
     * @param string|null $translationKey
     * @return self
     */
    public static function notFound(string $message = 'Not found', ?array $errors = null, string $customStatusCode = 'NOT_FOUND_404', ?string $translationKey = null): self
    {
        return self::error($message, $errors, 404, [], [], $customStatusCode, $translationKey);
    }

    /**
     * Return a 422 Unprocessable Entity response.
     *
     * @param string $message
     * @param array|null $errors
     * @param string $customStatusCode
     * @param string|null $translationKey
     * @return self
     */
    public static function validationError(string $message = 'Validation error', ?array $errors = null, string $customStatusCode = 'VALIDATION_ERROR_422', ?string $translationKey = null): self
    {
        return self::error($message, $errors, 422, [], [], $customStatusCode, $translationKey);
    }

    /**
     * Return a 500 Internal Server Error response.
     *
     * @param string $message
     * @param array|null $errors
     * @param string $customStatusCode
     * @param string|null $translationKey
     * @return self
     */
    public static function serverError(string $message = 'Server error', ?array $errors = null, string $customStatusCode = 'SERVER_ERROR_500', ?string $translationKey = null): self
    {
        return self::error($message, $errors, 500, [], [], $customStatusCode, $translationKey);
    }

    /**
     * Set a custom status code.
     *
     * @param string $customStatusCode
     * @return self
     */
    public function withCustomStatusCode(string $customStatusCode): self
    {
        $this->customStatusCode = $customStatusCode;
        return $this;
    }

    /**
     * Set a translation key.
     *
     * @param string $translationKey
     * @return self
     */
    public function withTranslationKey(string $translationKey): self
    {
        $this->translationKey = $translationKey;
        return $this;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toResponse($request): JsonResponse
    {
        // Check if Accept-Language header is present and set locale
        if ($request->hasHeader('Accept-Language')) {
            $locale = $request->header('Accept-Language');
            App::setLocale($locale);
        }

        // Try to translate the message using the translation key
        $translatedMessage = $this->message;
        if ($this->translationKey) {
            // Use Laravel's __ helper function to translate using JSON files
            $translated = __($this->translationKey);
            if ($translated !== $this->translationKey) {
                $translatedMessage = $translated;
            }
        }

        $response = [
            'success' => $this->success,
            'message' => $translatedMessage,
            'status_code' => $this->customStatusCode,
        ];

        if (!is_null($this->data)) {
            $response['data'] = $this->data;
        }

        if (!is_null($this->errors)) {
            $response['errors'] = $this->errors;
        }

        if (!empty($this->meta)) {
            $response['meta'] = $this->meta;
        }

        return new JsonResponse($response, $this->statusCode, $this->headers);
    }

    /**
     * Add pagination metadata from a paginator instance.
     *
     * @param \Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator
     * @return self
     */
    public function withPagination($paginator): self
    {
        $this->meta['pagination'] = [
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage()
        ];
        
        return $this;
    }

    /**
     * Add custom metadata.
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function withMeta(string $key, $value): self
    {
        $this->meta[$key] = $value;
        return $this;
    }
}
