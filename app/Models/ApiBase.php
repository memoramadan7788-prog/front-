<?php

namespace App\Models;

use App\Frontend\Api\CurlClient;
use App\Frontend\Auth\ApiSession;

/**
 * Base class for every *API class.
 * One place that talks to the backend and returns the same array shape:
 *   ["message" => string, "data" => mixed, "errors" => array, "meta" => ?array, "statusCode" => int]
 *
 * statusCode is 200 for ANY successful (2xx) reply, so controllers can keep using `=== 200`
 * (the backend answers 201 for creates). The real code is in "httpStatus".
 * Failures keep their real code (401, 403, 409, 422, 503...).
 */
abstract class ApiBase
{
    public $api = null;

    public function __construct()
    {
        $this->api = new CurlClient(baseUrl: rtrim(config('services.backend.url', env('API_URL', 'http://localhost:8000/api')), '/'));
    }

    /**
     * @param array{json?: array, form?: array, query?: array, auth?: bool, file?: array{0:string,1:string}} $opts
     */
    protected function call(string $method, string $url, array $opts = []): array
    {
        $req = $this->api->reset()->method($method)->url($url);

        if ($opts['auth'] ?? true) {
            $req->bearer(ApiSession::token() ?? '');
        }
        if (!empty($opts['query'])) {
            $req->query($opts['query']);
        }
        if (isset($opts['json'])) {
            $req->data($opts['json']);
        }
        if (isset($opts['form'])) {
            $req->form($opts['form']);
        }
        if (isset($opts['file'])) {
            $req->file($opts['file'][0], $opts['file'][1]);
        }

        $res = $req->send();

        return [
            'message'    => $res->message,
            'data'       => $res->data,
            'errors'     => $res->errors ?? [],
            'meta'       => $res->meta,
            'statusCode' => $res->success ? 200 : $res->statusCode,
            'httpStatus' => $res->statusCode,
        ];
    }
}
