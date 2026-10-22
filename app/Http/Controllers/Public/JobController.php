<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Support\Frontend\MockPublicData;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class JobController extends Controller
{
    public function index(): View
    {
        return view('public.jobs.index', [
            'pageTitle' => 'Find your next role · Hirebridge',
            'jobs' => MockPublicData::publicCatalog(),
            'isMockData' => true,
        ]);
    }

    public function show(string $job): View
    {
        $record = MockPublicData::job($job);

        if ($record === null || ($record['visibility'] ?? 'public') !== 'public') {
            throw new NotFoundHttpException('Demo job not found.');
        }

        if (($record['publication_status'] ?? 'published') !== 'published') {
            throw new NotFoundHttpException('Demo job not found.');
        }

        return view('public.jobs.show', [
            'pageTitle' => $record['title'].' · Hirebridge',
            'job' => $record,
            'isMockData' => true,
        ]);
    }
}
