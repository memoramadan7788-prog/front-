<?php

namespace App\Frontend\Lookup;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

use App\Models\LookupAPI;

class LookupController extends Controller
{
    private $api = null;

    public function __construct()
    {
        $this->api = new LookupAPI();
    }

    function getCountries(Request $request)
    {
        $response = $this->api->getCountries();

        if ($response['statusCode'] === 200) {
            // Countries retrieval successful, you can perform additional actions here if needed
            return response()->json(['countries' => $response['data']], 200);
        } else {
            // Countries retrieval failed, return the error message
            return response()->json(['error' => $response['message']], $response['statusCode']);
        }
    }

    function getCategories(Request $request)
    {
        $response = $this->api->getCategories();

        if ($response['statusCode'] === 200) {
            // Categories retrieval successful, you can perform additional actions here if needed
            return response()->json(['categories' => $response['data']], 200);
        } else {
            // Categories retrieval failed, return the error message
            return response()->json(['error' => $response['message']], $response['statusCode']);
        }
    }

    function getSkills(Request $request)
    {
        $keyword = (string) $request->query('q', '');

        $response = $this->api->getSkills($keyword);

        if ($response['statusCode'] === 200) {
            // Skills retrieval successful, you can perform additional actions here if needed
            return response()->json(['skills' => $response['data']], 200);
        } else {
            // Skills retrieval failed, return the error message
            return response()->json(['error' => $response['message']], $response['statusCode']);
        }
    }
}
