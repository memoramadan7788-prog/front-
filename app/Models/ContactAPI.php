<?php

namespace App\Models;

/**
 * General contact message API integration.
 */
class ContactAPI extends ApiBase
{
    /**
     * @param  array{name: string, email: string, title: string, description: string}  $data
     * @return array<string, mixed>
     */
    public function submitContactMessage(array $data, bool $authenticated): array
    {
        return $this->call('POST', '/contact', [
            'auth' => $authenticated,
            'json' => $data,
        ]);
    }
}
