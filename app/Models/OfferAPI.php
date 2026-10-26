<?php

namespace App\Models;

use App\Frontend\Api\CurlClient;
use App\Frontend\Auth\ApiSession;

class OfferAPI
{
    public $api = null;

    public function __construct()
    {
        $this->api = new CurlClient(
            baseUrl: config('services.backend.url'),
            defaults: ['token_key' => ApiSession::token() ?? '']
        );
    }

    public function getOffers()
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('GET')
            ->url('/candidate/offers')
            ->bearer(ApiSession::token() ?? '')->send();

        if ($res->ok()) {
            $message = $res->message;
            $statusCode = 200; // any 2xx (the backend answers 201 for creates) counts as success
        } else {
            $message = "Error: {$res->message}\n";
            $statusCode = $res->statusCode;
        }

        return ["message" => $message, "data" => $res->data, "statusCode" => $statusCode];
    }

    public function getOffer($offerId)
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('GET')
            ->url("/offers/{$offerId}")
            ->bearer(ApiSession::token() ?? '')->send();

        if ($res->ok()) {
            $message = $res->message;
            $statusCode = 200; // any 2xx (the backend answers 201 for creates) counts as success
        } else {
            $message = "Error: {$res->message}\n";
            $statusCode = $res->statusCode;
        }

        return ["message" => $message, "data" => $res->data, "statusCode" => $statusCode];
    }

    public function acceptOffer($offerId)
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('POST')
            ->url("/candidate/offers/{$offerId}/accept")
            ->bearer(ApiSession::token() ?? '')->send();

        if ($res->ok()) {
            $message = $res->message;
            $statusCode = 200; // any 2xx (the backend answers 201 for creates) counts as success
        } else {
            $message = "Error: {$res->message}\n";
            $statusCode = $res->statusCode;
        }

        return ["message" => $message, "data" => $res->data, "statusCode" => $statusCode];
    }

    public function rejectOffer($offerId, $data = [])
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('POST')
            ->url("/candidate/offers/{$offerId}/reject")
            ->bearer(ApiSession::token() ?? '')
            ->body($data)->send();

        if ($res->ok()) {
            $message = $res->message;
            $statusCode = 200; // any 2xx (the backend answers 201 for creates) counts as success
        } else {
            $message = "Error: {$res->message}\n";
            $statusCode = $res->statusCode;
        }

        return ["message" => $message, "data" => $res->data, "statusCode" => $statusCode];
    }

    public function createOffer($data)
    {
        $message = '';
        $statusCode = 200;

        $res = $this->api->reset()->method('POST')
            ->url('/company/offers')
            ->bearer(ApiSession::token() ?? '')
            ->body($data)->send();

        if ($res->ok()) {
            $message = $res->message;
            $statusCode = 200; // any 2xx (the backend answers 201 for creates) counts as success
        } else {
            $message = "Error: {$res->message}\n";
            $statusCode = $res->statusCode;
        }

        return ["message" => $message, "data" => $res->data, "statusCode" => $statusCode];
    }
}