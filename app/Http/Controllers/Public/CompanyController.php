<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Support\Frontend\MockPublicData;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CompanyController extends Controller
{
    public function show(string $company): View
    {
        $record = MockPublicData::company($company);

        if ($record === null) {
            throw new NotFoundHttpException('Demo company not found.');
        }

        return view('public.companies.show', [
            'pageTitle' => $record['name'].' · Hirebridge',
            'company' => $record,
            'openRoles' => MockPublicData::jobsForCompany($company),
            'isMockData' => true,
        ]);
    }
}
