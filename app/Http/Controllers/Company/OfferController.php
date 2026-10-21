<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Support\Frontend\MockCompanyData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Company offer draft UI (Batch 5E) — Frame 35.
 * Frontend presentation only.
 */
class OfferController extends Controller
{
    public function create(Request $request): View|Response
    {
        $id = $request->query('offer') ?? $request->query('applicant') ?? 'of-2026-021';
        $id = is_string($id) && $id !== '' ? $id : 'of-2026-021';

        $offer = MockCompanyData::offerDraft($id);

        if ($offer === null) {
            abort(404);
        }

        return view('company.offers.create', [
            'pageTitle' => __('company.offers.create_page_title', ['name' => $offer['applicant']]),
            'uiRole' => 'company',
            'showMockBanner' => false,
            'offer' => $offer,
            'frontendOnly' => true,
        ]);
    }
}
