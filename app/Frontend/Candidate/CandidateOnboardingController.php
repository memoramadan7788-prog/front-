<?php

namespace App\Frontend\Candidate;

use App\Frontend\Auth\ApiSession;
use App\Models\CandidateAPI;
use App\Support\Frontend\CandidateSettings\CandidateSettingsGateway;
use App\Support\Rows;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

class CandidateOnboardingController extends Controller
{
    public function __construct(
        private readonly CandidateAPI $candidate,
        private readonly CandidateSettingsGateway $settings,
    ) {}

    public function show(Request $request): View|RedirectResponse
    {
        $step = (int) $request->query('step', 1);
        if ($step < 1 || $step > 3) {
            $step = 1;
        }

        $profileResponse = $this->candidate->getProfile();
        if ((int) ($profileResponse['statusCode'] ?? 500) === 401) {
            ApiSession::forget();

            return redirect()->route('frontend.account.login');
        }

        $profile = (int) ($profileResponse['statusCode'] ?? 500) === 200 && is_array($profileResponse['data'] ?? null)
            ? $profileResponse['data']
            : [];
        $skillsResponse = $this->candidate->getSkills();
        $preferencesResponse = $this->settings->getPreferences();
        $completed = [
            filled(data_get($profile, 'headline')) || filled(data_get($profile, 'location')),
            count(Rows::from(data_get($profile, 'cvs', []))) > 0,
            (int) ($skillsResponse['statusCode'] ?? 500) === 200 && count(Rows::from($skillsResponse['data'] ?? [])) > 0
                || (int) ($preferencesResponse['statusCode'] ?? 500) === 200 && filled(data_get($preferencesResponse, 'data.desired_titles')),
        ];
        $completion = (int) round(count(array_filter($completed)) * 100 / count($completed));

        return view('frontend.candidate.onboarding', [
            'step' => $step,
            'profile' => $profile,
            'profileLoaded' => (int) ($profileResponse['statusCode'] ?? 500) === 200,
            'completion' => $completion,
            'resumeCount' => count(Rows::from(data_get($profile, 'cvs', []))),
            'preferences' => (int) ($preferencesResponse['statusCode'] ?? 500) === 200 ? (array) ($preferencesResponse['data'] ?? []) : [],
            'preferencesLoaded' => (int) ($preferencesResponse['statusCode'] ?? 500) === 200,
            'skillsLoaded' => (int) ($skillsResponse['statusCode'] ?? 500) === 200,
            'skills' => (int) ($skillsResponse['statusCode'] ?? 500) === 200 ? Rows::from($skillsResponse['data'] ?? []) : [],
        ]);
    }

    public function save(Request $request): RedirectResponse
    {
        $step = (int) $request->input('step', 1);

        if ($step === 1) {
            $data = $request->validate([
                'headline' => ['nullable', 'string', 'max:255'],
                'location' => ['nullable', 'string', 'max:255'],
                'phone' => ['nullable', 'string', 'max:30'],
                'bio' => ['nullable', 'string', 'max:5000'],
                'years_experience' => ['nullable', 'integer', 'min:0', 'max:80'],
            ]);
            $response = $this->candidate->updateProfile($data);
        } elseif ($step === 2) {
            $request->validate(['resume' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240']]);
            $file = $request->file('resume');
            $response = $this->candidate->uploadResume($file->getRealPath(), $file->getClientMimeType(), $file->getClientOriginalName());
        } elseif ($step === 3) {
            $validated = $request->validate([
                'desired_titles' => ['nullable', 'string', 'max:1000'],
                'location' => ['nullable', 'string', 'max:255'],
                'employment_type' => ['nullable', 'string', 'max:50', Rule::in(array_merge([''], CandidatePreferencesController::EMPLOYMENT_TYPES))],
                'work_model' => ['nullable', 'string', Rule::in(array_merge([''], CandidatePreferencesController::WORK_MODELS))],
                'min_salary' => ['nullable', 'numeric', 'min:0'],
            ]);
            $response = $this->settings->updatePreferences([
                'desired_titles' => collect(preg_split('/[,;\n]+/', (string) ($validated['desired_titles'] ?? '')) ?: [])->map(fn ($title) => trim((string) $title))->filter()->take(20)->values()->all(),
                'location' => $validated['location'] ?? null,
                'employment_type' => ($validated['employment_type'] ?? '') ?: null,
                'work_model' => ($validated['work_model'] ?? '') ?: null,
                'min_salary' => $validated['min_salary'] ?? null,
                'open_to_relocation' => false,
                'include_agency' => false,
            ]);
        } else {
            return redirect()->route('frontend.candidate.onboarding')->withErrors(['step' => __('onboarding.candidate.validation')]);
        }

        if ((int) ($response['statusCode'] ?? 500) !== 200) {
            return back()->withErrors(($response['errors'] ?? []) ?: ['error' => __('onboarding.candidate.save_error')])->withInput($request->except('resume'));
        }

        return redirect()->route('frontend.candidate.onboarding', ['step' => min(3, $step + 1)])->with('success_key', 'onboarding.candidate.saved');
    }
}
