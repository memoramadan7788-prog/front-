<?php

namespace App\Frontend\Ats;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use App\Models\AtsAPI;
use App\Support\Frontend\AiScreeningPresentation;
use App\Support\Frontend\SafeApiMessage;

class AtsController extends Controller
{
    private $api;

    public function __construct(AtsAPI $api)
    {
        $this->api = $api;
    }

    /** GET /company/applications/{applicationId}/ats */
    function show(Request $request, $applicationId)
    {
        $response = $this->api->getEvaluation($applicationId);

        if ($response['statusCode'] !== 200) {
            // NOT redirect()->back(): a failed GET would bounce back to this same page forever
            return redirect()->route('frontend.company.vacancies.index')->withErrors($this->errorsFrom($response));
        }

        // data = null => this application has not been evaluated yet
        $data = $response['data'];
        $ats = (is_array($data) && $data !== []) ? AiScreeningPresentation::fromApi($data, $applicationId) : null;

        $canRerun = $ats === null || $ats['can_rerun'];
        $autoRefresh = $ats !== null && $ats['auto_refresh'];

        return view('frontend.company.ats-evaluation', get_defined_vars());
    }

    /** POST /company/applications/{applicationId}/ats  (queue a new evaluation) */
    function start(Request $request, $applicationId)
    {
        $response = $this->api->startEvaluation($applicationId);
        $page = redirect()->route('frontend.company.applications.ats', ['applicationId' => $applicationId]);

        if ($response['statusCode'] === 200) {
            return $page->with('success_key', 'ats.flash.queued');
        }

        // 409 = an evaluation is already running: not an error for the user
        if ((int) ($response['httpStatus'] ?? 0) === 409) {
            return $page->with('success_key', 'ats.flash.already_running');
        }

        return $page->withErrors($this->errorsFrom($response));
    }

    /** POST /company/applications/{applicationId}/ats/decision */
    function decide(Request $request, $applicationId)
    {
        $fields = $request->validate([
            'decision' => 'required|in:accept,override',
            'reason' => 'required_if:decision,override|nullable|string|max:1000',
        ]);

        $response = $this->api->decide($applicationId, [
            'decision' => $fields['decision'],
            'reason' => $fields['reason'] ?? null,
        ]);

        $page = redirect()->route('frontend.company.applications.ats', ['applicationId' => $applicationId]);

        if ($response['statusCode'] === 200) {
            return $page->with('success_key', 'ats.flash.reviewed');
        }

        return $page->withErrors($this->errorsFrom($response))->withInput();
    }

    /** Field errors from the Backend if any, otherwise one safe message (never raw debug/SQL text) */
    private function errorsFrom(array $response): array
    {
        $errors = $response['errors'] ?? [];
        if (is_array($errors) && $errors !== []) {
            return $errors;
        }

        return ['error' => SafeApiMessage::forUser($response['message'] ?? '', __('ats.error_generic'), ['screen' => 'ats'])];
    }
}
