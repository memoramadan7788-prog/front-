<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Support\Frontend\MockPublicData;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $featured = array_slice(MockPublicData::jobs(), 0, 3);

        return view('public.landing', [
            'pageTitle' => 'Hirebridge · Your next chapter starts here',
            'featuredJobs' => $featured,
            'demoCompanies' => ['Northstar Labs', 'Meridian Studio', 'Atlas Health', 'Lumen', 'Grove & Co.'],
        ]);
    }
}
