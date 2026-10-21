<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Support\Frontend\MockCandidateData;
use Illuminate\Contracts\View\View;

/**
 * Figma 24 — Candidate job offers.
 * Frontend presentation only. No accept/decline persistence.
 */
class OfferController extends Controller
{
    public function index(): View
    {
        return view('candidate.offers.index', [
            'pageTitle' => 'Offers · Hirebridge',
            'uiRole' => 'candidate',
            'showMockBanner' => true,
            'offers' => MockCandidateData::offers(),
            'frontendOnly' => true,
        ]);
    }

    public function show(string $offer): View
    {
        $record = MockCandidateData::offer($offer);

        abort_unless($record !== null, 404);

        return view('candidate.offers.show', [
            'pageTitle' => 'Your offer from '.$record['company'].' · Hirebridge',
            'uiRole' => 'candidate',
            'showMockBanner' => true,
            'offer' => $record,
            'frontendOnly' => true,
        ]);
    }
}
