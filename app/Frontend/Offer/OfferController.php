<?php

namespace App\Frontend\Offer;

use App\Models\OfferAPI;
use App\Support\Frontend\ApiListPage;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class OfferController extends Controller
{
    private $api;

    public function __construct(OfferAPI $api)
    {
        $this->api = $api;
    }

    public function getOffers(Request $request)
    {
        return ApiListPage::render(
            $this->api->getOffers(),
            'frontend.offer.offers',
            'offers',
        );
    }

    public function getOffer(Request $request, $offerId)
    {
        $response = $this->api->getOffer($offerId);

        if ($response['statusCode'] === 200) {
            // Offer details retrieval successful, you can perform additional actions here if needed
            return view('frontend.offer.offer-details', ['offer' => $response['data']]);
        } else {
            // Offer details retrieval failed, return the error message
            return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']]);
        }
    }

    public function acceptOffer(Request $request, $offerId)
    {
        $request->validate([
            'offer_reviewed' => 'required|accepted',
        ]);

        $response = $this->api->acceptOffer($offerId);

        if ($response['statusCode'] === 200) {
            // Offer acceptance successful, you can perform additional actions here if needed
            return redirect()->back()->with('success_key', 'flash.offer.accepted');
        } else {
            // Offer acceptance failed, return the error message
            return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']]);
        }
    }

    public function rejectOffer(Request $request, $offerId)
    {
        if ($request->isMethod('post')) {

            $fields = $request->validate([
                'reason' => 'nullable|string|max:1000',
            ]);

            $data = $request->only(['reason']);

            $response = $this->api->rejectOffer($offerId, $data);

            if ($response['statusCode'] === 200) {
                // Offer rejection successful, you can perform additional actions here if needed
                return redirect()->back()->with('success_key', 'flash.offer.rejected');
            } else {
                // Offer rejection failed, return the error message
                return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']])->withInput();
            }
        }

        return view('frontend.offer.reject', get_defined_vars());
    }

    public function createOffer(Request $request)
    {
        if ($request->isMethod('post')) {

            $fields = $request->validate([
                'application_id' => 'required|integer',
                'salary_amount' => 'required|numeric|min:0',
                'currency' => 'required|string|max:10',
                'salary_period' => 'required|string',
                'start_date' => 'required|date',
                'expires_at' => 'required|date|after:today',
            ]);

            $data = $request->only(['application_id', 'salary_amount', 'currency', 'salary_period', 'start_date', 'expires_at']);

            $response = $this->api->createOffer($data);

            if ($response['statusCode'] === 200) {
                // Offer creation successful, you can perform additional actions here if needed
                return redirect()->back()->with('success_key', 'flash.offer.created');
            } else {
                // Offer creation failed, return the error message
                return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']])->withInput();
            }
        }

        return view('frontend.offer.create', get_defined_vars());
    }
}
