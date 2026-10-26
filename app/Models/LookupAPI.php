<?php

namespace App\Models;

/**
 * Dropdown data (public).
 * Backend: GET /countries, GET /job-categories, GET /skills?q=&type=
 * Every answer is {data: [...]}, so $result['data'] is a plain list.
 */
class LookupAPI extends ApiBase
{
    public function getCountries()
    {
        return $this->call('GET', '/countries', ['auth' => false]);
    }

    public function getCategories()
    {
        return $this->call('GET', '/job-categories', ['auth' => false]);
    }

    public function getSkills($keyword = '')
    {
        return $this->call('GET', '/skills', [
            'query' => $keyword !== '' ? ['q' => $keyword] : [],
            'auth'  => false,
        ]);
    }
}
