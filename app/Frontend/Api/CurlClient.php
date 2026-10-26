<?php

namespace App\Frontend\Api;

use InvalidArgumentException;
use JsonException;

/**
 * CurlClient — a cURL-flavoured global API request class.
 * Always returns an ApiResponse (or throws ApiError on failure).
 *
 * Usage:
 *   $res = $api->reset()
 *       ->method('POST')
 *       ->url('/users')
 *       ->bearer($token)
 *       ->data(['name' => 'John'])
 *       ->send();
 *
 *   if ($res->ok()) {
 *       echo $res->data['id'];
 *   }
 */
class CurlClient
{
    private string $baseUrl;
    private string $tokenKey = 'auth_token';

    // Per-request state
    private string $url = '';
    private string $method = 'GET';
    private array  $headers = [];
    private array  $query = [];
    private mixed  $body = null;
    private ?array $form = null;
    private int    $timeout = 30;
    private int    $retries = 0;
    private int    $retryDelay = 500;
    private bool   $verifySsl = true;
    private ?string $token = null;
    private ?array $basicAuth = null;
    private array  $cookies = [];
    private ?string $cookieFile = null;

    public function __construct(string $baseUrl = '', array $defaults = [])
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->headers = array_merge([
            'Accept'           => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ], $defaults['headers'] ?? []);

        if (isset($defaults['token_key'])) {
            $this->tokenKey = $defaults['token_key'];
        }
    }

    // ─────────────────────────────────────────────
    //  Token management
    // ─────────────────────────────────────────────
    public function setToken(string $token): self
    {
        $this->token = $token;
        return $this;
    }

    public function token(): ?string
    {
        return $this->token;
    }

    public function clearToken(): self
    {
        $this->token = null;
        return $this;
    }

    // ─────────────────────────────────────────────
    //  Fluent configuration
    // ─────────────────────────────────────────────
    public function reset(): self
    {
        $this->url        = '';
        $this->method     = 'GET';
        $this->query      = [];
        $this->body       = null;
        $this->form       = null;
        $this->timeout    = 30;
        $this->retries    = 0;
        $this->retryDelay = 500;
        $this->basicAuth  = null;
        // Preserve headers set at construction, but clear per-request ones
        $this->headers = array_filter(
            $this->headers,
            fn ($k) => !in_array($k, ['Content-Type', 'Authorization'], true),
            ARRAY_FILTER_USE_KEY
        );
        return $this;
    }

    /** -X */
    public function method(string $m): self
    {
        $this->method = strtoupper($m);
        return $this;
    }

    /** URL (relative or absolute) */
    public function url(string $u): self
    {
        $this->url = $u;
        return $this;
    }

    /** -H */
    public function header(string $k, string $v): self
    {
        $this->headers[$k] = $v;
        return $this;
    }

    public function headers(array $h): self
    {
        $this->headers = array_merge($this->headers, $h);
        return $this;
    }

    /** Bearer token */
    public function bearer(?string $token = null): self
    {
        $this->headers['Authorization'] = 'Bearer ' . ($token ?? $this->token ?? '');
        return $this;
    }

    /** -u Basic auth */
    public function basic(string $username, string $password): self
    {
        $this->basicAuth = [$username, $password];
        return $this;
    }

    /** -G --data-urlencode */
    public function query(array $params): self
    {
        $this->query = array_merge($this->query, $params);
        return $this;
    }

    /** -d JSON body */
    public function data(mixed $payload): self
    {
        $this->body = $payload;
        if (!isset($this->headers['Content-Type'])) {
            $this->headers['Content-Type'] = 'application/json';
        }
        return $this;
    }

    /** Alias of data(): JSON body */
    public function body(mixed $payload): self
    {
        return $this->data($payload);
    }

    /** Attach a file to a multipart request (can be combined with form()) */
    public function file(string $field, string $path, ?string $mime = null, ?string $name = null): self
    {
        $this->form = ($this->form ?? []) + [$field => ['path' => $path, 'mime' => $mime ?? (mime_content_type($path) ?: 'application/octet-stream'), 'name' => $name ?? basename($path)]];
        unset($this->headers['Content-Type']);
        return $this;
    }

    /** -F multipart */
    public function form(array $fields): self
    {
        $this->form = $fields;
        unset($this->headers['Content-Type']); // cURL sets boundary
        return $this;
    }

    /** --max-time */
    public function timeout(int $seconds): self
    {
        $this->timeout = $seconds;
        return $this;
    }

    /** --retry */
    public function retries(int $count, int $delayMs = 500): self
    {
        $this->retries = $count;
        $this->retryDelay = $delayMs;
        return $this;
    }

    /** -k / --insecure */
    public function insecure(): self
    {
        $this->verifySsl = false;
        return $this;
    }

    /** -b / --cookie */
    public function cookie(string $name, string $value): self
    {
        $this->cookies[$name] = $value;
        return $this;
    }

    /** Cookie jar file */
    public function cookieFile(string $path): self
    {
        $this->cookieFile = $path;
        return $this;
    }

    // ─────────────────────────────────────────────
    //  Verb shortcuts — all return ApiResponse
    // ─────────────────────────────────────────────
    public function get(string $url, array $query = []): ApiResponse
    {
        return $this->reset()->method('GET')->url($url)->query($query)->send();
    }

    public function post(string $url, mixed $data = null): ApiResponse
    {
        return $this->reset()->method('POST')->url($url)->data($data)->send();
    }

    public function put(string $url, mixed $data = null): ApiResponse
    {
        return $this->reset()->method('PUT')->url($url)->data($data)->send();
    }

    public function patch(string $url, mixed $data = null): ApiResponse
    {
        return $this->reset()->method('PATCH')->url($url)->data($data)->send();
    }

    public function delete(string $url, array $query = []): ApiResponse
    {
        return $this->reset()->method('DELETE')->url($url)->query($query)->send();
    }

    public function upload(string $url, array $files): ApiResponse
    {
        return $this->reset()->method('POST')->url($url)->form($files)->send();
    }

    // ─────────────────────────────────────────────
    //  Core execution
    // ─────────────────────────────────────────────
    /**
     * Always returns an ApiResponse (success=false for HTTP errors and network failures).
     * It does not throw, so callers can simply check `$res->ok()`.
     */
    public function send(): ApiResponse
    {
        $attempt = 0;
        while (true) {
            $res = $this->executeOnce();

            if ($res->statusCode >= 500 && $attempt < $this->retries) {
                $attempt++;
                usleep($this->retryDelay * 1000 * $attempt);
                continue;
            }

            return $res;
        }
    }

    private function executeOnce(): ApiResponse
    {
        $ch = curl_init();

        $fullUrl = $this->buildUrl();

        // --- Headers ---
        $headerLines = [];
        foreach ($this->headers as $k => $v) {
            $headerLines[] = "{$k}: {$v}";
        }
        if ($this->cookies) {
            $headerLines[] = 'Cookie: ' . http_build_query($this->cookies, '', '; ');
        }
        if ($this->basicAuth) {
            // Handle Basic separately via CURLOPT_USERPWD for correctness
            curl_setopt($ch, CURLOPT_USERPWD, "{$this->basicAuth[0]}:{$this->basicAuth[1]}");
        }

        // --- Options ---
        curl_setopt_array($ch, [
            CURLOPT_URL            => $fullUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,          // we parse status
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_SSL_VERIFYPEER => $this->verifySsl,
            CURLOPT_SSL_VERIFYHOST => $this->verifySsl ? 2 : 0,
            CURLOPT_HTTPHEADER     => $headerLines,
            CURLOPT_CUSTOMREQUEST  => $this->method,
            CURLOPT_ENCODING       => '',            // accept gzip/deflate
        ]);

        // Cookie jar
        if ($this->cookieFile) {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
        }

        // --- Body ---
        if ($this->form !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->prepareForm($this->form));
        } elseif ($this->body !== null) {
            try {
                $payload = is_string($this->body)
                    ? $this->body
                    : json_encode($this->body, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                throw new ApiError('Failed to encode JSON body: ' . $e->getMessage(), 0, null, $e);
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        }

        // --- Execute ---
        $raw = curl_exec($ch);

        if ($raw === false) {
            $errno  = curl_errno($ch);
            $errmsg = curl_error($ch);
            curl_close($ch);
            return new ApiResponse(
                false,
                $errno === 28 ? 'The server took too long to respond.' : 'Could not reach the server. Please try again.',
                null,
                null,
                'NETWORK_ERROR',
                ['curl_error' => "[{$errno}] {$errmsg}"],
                $errno === 28 ? 408 : 503
            );
        }

        $statusCode  = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $headerSize  = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        $body = substr($raw, $headerSize);

        // --- 204 No Content ---
        if ($statusCode === 204) {
            return new ApiResponse(true, 'No content', null, null, null, null, 204);
        }

        // --- Parse JSON ---
        $json = null;
        if ($body !== '' && $body !== false) {
            try {
                $json = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                $json = null;
            }
        }

        // Session expired / token rejected: forget it so the `auth` middleware sends the user to login
        if ($statusCode === 401 && isset($this->headers['Authorization'])) {
            \App\Frontend\Auth\ApiSession::forget();
        }

        return $this->normalise($json, $body, $statusCode);
    }

    /**
     * The backend returns plain Laravel JSON:
     *   success   {message?, <payload...>}  |  {data: {...}}  |  {data: [...], links, meta}
     *   failure   {message, errors?}
     * Turn every shape into one ApiResponse.
     */
    private function normalise(?array $json, string|bool $rawBody, int $statusCode): ApiResponse
    {
        $success = $statusCode >= 200 && $statusCode < 300;

        if (!is_array($json)) {
            return new ApiResponse(
                $success,
                $success ? 'OK' : "HTTP {$statusCode}",
                $success && is_string($rawBody) && $rawBody !== '' ? $rawBody : null,
                null,
                $success ? null : 'HTTP_ERROR',
                null,
                $statusCode
            );
        }

        $message = (string) ($json['message'] ?? ($success ? 'OK' : "HTTP {$statusCode}"));

        if (!$success) {
            $errors = isset($json['errors']) && is_array($json['errors']) ? $json['errors'] : null;
            $code = $statusCode === 422 ? 'VALIDATION_ERROR' : 'HTTP_ERROR';

            return new ApiResponse(false, $message, null, $errors, $code, null, $statusCode);
        }

        $meta = null;
        if (array_key_exists('data', $json)) {
            // API Resource: single {data: {...}} or paginated {data: [...], links, meta}
            $data = $json['data'];
            if (isset($json['meta']) && is_array($json['meta'])) {
                $meta = ['pagination' => [
                    'current_page' => $json['meta']['current_page'] ?? 1,
                    'last_page'    => $json['meta']['last_page'] ?? 1,
                    'per_page'     => $json['meta']['per_page'] ?? null,
                    'total'        => $json['meta']['total'] ?? null,
                    'links'        => $json['links'] ?? null,
                ]];
            }
        } else {
            // {message, user, token} / {message, application: {...}} ...
            $data = $json;
            unset($data['message']);
        }

        return new ApiResponse(true, $message, $data, null, null, $meta, $statusCode);
    }

    // ─────────────────────────────────────────────
    //  Internals
    // ─────────────────────────────────────────────
    private function buildUrl(): string
    {
        if ($this->url === '') {
            throw new InvalidArgumentException('URL is required');
        }

        $isAbsolute = (bool) preg_match('#^https?://#i', $this->url);
        $base = $isAbsolute ? $this->url : $this->baseUrl . $this->url;

        if (!$this->query) {
            return $base;
        }

        // Flatten arrays → key[]=a&key[]=b
        $pairs = [];
        foreach ($this->query as $k => $v) {
            if ($v === null) continue;
            if (is_array($v)) {
                foreach ($v as $item) {
                    $pairs[] = rawurlencode("{$k}[]") . '=' . rawurlencode((string) $item);
                }
            } else {
                $pairs[] = rawurlencode($k) . '=' . rawurlencode((string) $v);
            }
        }
        $sep = str_contains($base, '?') ? '&' : '?';
        return $base . $sep . implode('&', $pairs);
    }

    /**
     * Prepare multipart form fields for cURL.
     * Accepts file paths, CURLFile objects, or scalars.
     */
    private function prepareForm(array $fields): array
    {
        $out = [];
        foreach ($fields as $k => $v) {
            if ($v instanceof \CURLFile) {
                $out[$k] = $v;
            } elseif (is_array($v) && isset($v['path'])) {
                // ['path' => '/file', 'mime' => 'image/png', 'name' => 'x.png']
                $out[$k] = new \CURLFile(
                    $v['path'],
                    $v['mime'] ?? 'application/octet-stream',
                    $v['name'] ?? basename($v['path'])
                );
            } else {
                $out[$k] = is_scalar($v) ? (string) $v : json_encode($v);
            }
        }
        return $out;
    }
}