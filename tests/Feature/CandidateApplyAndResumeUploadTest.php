<?php

namespace Tests\Feature;

use App\Frontend\Auth\ApiSession;
use App\Models\CandidateAPI;
use App\Models\JobAPI;
use Illuminate\Http\UploadedFile;
use Mockery;
use Tests\TestCase;

class CandidateApplyAndResumeUploadTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_apply_form_without_resume_shows_empty_state_and_job_context(): void
    {
        $this->actingAsCandidate();
        $this->mockJob(55, 'Staff Engineer', 'Northstar');
        $this->mockProfileCvs([]);

        $html = $this->get(route('frontend.candidate.vacancies.apply', ['vacancyId' => 55]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Staff Engineer', $html);
        $this->assertStringContainsString('Northstar', $html);
        $this->assertStringContainsString(__('candidate.apply_form.no_resume_title'), $html);
        $this->assertStringContainsString(
            route('frontend.candidate.resume.upload', ['vacancy_id' => 55], false),
            $html
        );
        $this->assertStringNotContainsString('name="cv_id"', $html);
        $this->assertStringNotContainsString('name="consent"', $html);
        $this->assertStringNotContainsString(__('candidate.apply_form.submit'), $html);
    }

    public function test_apply_form_with_resume_shows_selector_and_optional_cover_note(): void
    {
        $this->actingAsCandidate();
        $this->mockJob(55, 'Staff Engineer', 'Northstar');
        $this->mockProfileCvs([
            ['id' => 3, 'original_filename' => 'sara-cv.pdf'],
        ]);

        $html = $this->get(route('frontend.candidate.vacancies.apply', ['vacancyId' => 55]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Staff Engineer', $html);
        $this->assertStringContainsString('name="cv_id"', $html);
        $this->assertStringContainsString('sara-cv.pdf', $html);
        $this->assertStringContainsString('name="cover_note"', $html);
        $this->assertStringContainsString(__('candidate.apply_form.cover_note_hint'), $html);
        $this->assertStringContainsString(__('candidate.apply_form.submit'), $html);
        $this->assertStringNotContainsString(__('candidate.apply_form.no_resume_title'), $html);
    }

    public function test_apply_post_failure_does_not_claim_success(): void
    {
        $this->actingAsCandidate();

        $api = Mockery::mock(CandidateAPI::class);
        $api->shouldReceive('applyForVacancy')->once()->andReturn([
            'message' => 'You already applied to this job.',
            'data' => null,
            'errors' => ['error' => 'You already applied to this job.'],
            'statusCode' => 422,
        ]);
        $this->app->instance(CandidateAPI::class, $api);

        $this->from(route('frontend.candidate.vacancies.apply', ['vacancyId' => 55]))
            ->post(route('frontend.candidate.vacancies.apply.submit', ['vacancyId' => 55]), [
                'cv_id' => 3,
                'cover_note' => 'Hello',
                'consent' => '1',
            ])
            ->assertRedirect(route('frontend.candidate.vacancies.apply', ['vacancyId' => 55]))
            ->assertSessionHasErrors()
            ->assertSessionMissing('success_key')
            ->assertSessionMissing('success');
    }

    public function test_standalone_upload_page_uses_backend_10mb_limits_copy(): void
    {
        $this->actingAsCandidate();

        $html = $this->get(route('frontend.candidate.resume.upload'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(__('candidate.resume_upload.file_label', ['max' => 10]), $html);
        $this->assertStringContainsString('data-max-bytes="10485760"', $html);
        $this->assertStringContainsString('data-accept-ext="pdf,doc,docx"', $html);
        $this->assertStringContainsString(route('frontend.candidate.profile', [], false), $html);
        $this->assertStringNotContainsString('name="vacancy_id"', $html);
        $this->assertStringNotContainsString('max 2 MB', $html);
    }

    public function test_upload_with_vacancy_context_preserves_return_and_cancel(): void
    {
        $this->actingAsCandidate();

        $html = $this->get(route('frontend.candidate.resume.upload', ['vacancy_id' => 55]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('name="vacancy_id"', $html);
        $this->assertStringContainsString('value="55"', $html);
        $this->assertStringContainsString('/candidate/vacancies/55/apply', $html);
        $this->assertStringContainsString(__('candidate.resume_upload.context_apply'), $html);
    }

    public function test_successful_upload_redirects_back_to_apply_when_vacancy_set(): void
    {
        $this->actingAsCandidate();

        $api = Mockery::mock(CandidateAPI::class);
        $api->shouldReceive('uploadResume')->once()->andReturn([
            'message' => 'OK',
            'data' => ['cv' => ['id' => 12, 'original_filename' => 'new.pdf']],
            'errors' => [],
            'statusCode' => 200,
        ]);
        $this->app->instance(CandidateAPI::class, $api);

        $file = UploadedFile::fake()->create('new.pdf', 100, 'application/pdf');

        $this->post(route('frontend.candidate.resume.upload.submit'), [
            'resume' => $file,
            'vacancy_id' => 55,
            'return_to' => '/candidate/vacancies/55/apply',
        ])
            ->assertRedirect('/candidate/vacancies/55/apply')
            ->assertSessionHas('success_key', 'candidate.resume_upload.flash_success');
    }

    public function test_successful_standalone_upload_redirects_to_profile(): void
    {
        $this->actingAsCandidate();

        $api = Mockery::mock(CandidateAPI::class);
        $api->shouldReceive('uploadResume')->once()->andReturn([
            'message' => 'OK',
            'data' => ['cv' => ['id' => 12]],
            'errors' => [],
            'statusCode' => 200,
        ]);
        $this->app->instance(CandidateAPI::class, $api);

        $file = UploadedFile::fake()->create('cv.docx', 80, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $this->post(route('frontend.candidate.resume.upload.submit'), [
            'resume' => $file,
        ])
            ->assertRedirect(route('frontend.candidate.profile'))
            ->assertSessionHas('success_key', 'candidate.resume_upload.flash_success');
    }

    public function test_upload_rejects_disallowed_extension_server_side(): void
    {
        $this->actingAsCandidate();

        $api = Mockery::mock(CandidateAPI::class);
        $api->shouldNotReceive('uploadResume');
        $this->app->instance(CandidateAPI::class, $api);

        $file = UploadedFile::fake()->create('malware.exe', 50, 'application/octet-stream');

        $this->from(route('frontend.candidate.resume.upload'))
            ->post(route('frontend.candidate.resume.upload.submit'), [
                'resume' => $file,
            ])
            ->assertRedirect(route('frontend.candidate.resume.upload'))
            ->assertSessionHasErrors('resume');
    }

    public function test_upload_rejects_oversized_file_server_side(): void
    {
        $this->actingAsCandidate();

        $api = Mockery::mock(CandidateAPI::class);
        $api->shouldNotReceive('uploadResume');
        $this->app->instance(CandidateAPI::class, $api);

        // Laravel max:10240 is kilobytes → 10 MB. Create just over that.
        $file = UploadedFile::fake()->create('huge.pdf', 10241, 'application/pdf');

        $this->from(route('frontend.candidate.resume.upload'))
            ->post(route('frontend.candidate.resume.upload.submit'), [
                'resume' => $file,
            ])
            ->assertRedirect(route('frontend.candidate.resume.upload'))
            ->assertSessionHasErrors('resume');
    }

    public function test_upload_failure_from_backend_does_not_redirect_to_apply_as_success(): void
    {
        $this->actingAsCandidate();

        $api = Mockery::mock(CandidateAPI::class);
        $api->shouldReceive('uploadResume')->once()->andReturn([
            'message' => 'Upload failed',
            'data' => null,
            'errors' => ['cv' => ['File too large']],
            'statusCode' => 422,
        ]);
        $this->app->instance(CandidateAPI::class, $api);

        $file = UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf');

        $this->from(route('frontend.candidate.resume.upload', ['vacancy_id' => 55]))
            ->post(route('frontend.candidate.resume.upload.submit'), [
                'resume' => $file,
                'vacancy_id' => 55,
            ])
            ->assertRedirect(route('frontend.candidate.resume.upload', ['vacancy_id' => 55]))
            ->assertSessionHasErrors()
            ->assertSessionMissing('success_key');
    }

    public function test_unsafe_return_to_is_ignored_on_successful_upload(): void
    {
        $this->actingAsCandidate();

        $api = Mockery::mock(CandidateAPI::class);
        $api->shouldReceive('uploadResume')->once()->andReturn([
            'message' => 'OK',
            'data' => ['cv' => ['id' => 1]],
            'errors' => [],
            'statusCode' => 200,
        ]);
        $this->app->instance(CandidateAPI::class, $api);

        $file = UploadedFile::fake()->create('ok.pdf', 40, 'application/pdf');

        $this->post(route('frontend.candidate.resume.upload.submit'), [
            'resume' => $file,
            'return_to' => 'https://evil.example/steal',
        ])
            ->assertRedirect(route('frontend.candidate.profile'))
            ->assertSessionHas('success_key', 'candidate.resume_upload.flash_success');
    }

    public function test_guest_cannot_access_apply_or_upload(): void
    {
        $this->get(route('frontend.candidate.vacancies.apply', ['vacancyId' => 1]))
            ->assertRedirect(route('frontend.account.login'));

        $this->get(route('frontend.candidate.resume.upload'))
            ->assertRedirect(route('frontend.account.login'));
    }

    private function actingAsCandidate(): void
    {
        ApiSession::store('cand-token', [
            'id' => 11,
            'email' => 'candidate@example.com',
            'name' => 'Candidate',
            'account_type' => 'candidate',
            'roles' => ['candidate'],
        ]);
    }

    private function mockJob(int $id, string $title, string $company): void
    {
        $payload = [
            'message' => 'OK',
            'data' => [
                'id' => $id,
                'title' => $title,
                'company' => ['name' => $company],
                'description' => 'Role details',
            ],
            'errors' => [],
            'statusCode' => 200,
        ];

        $jobs = Mockery::mock(JobAPI::class);
        $jobs->shouldReceive('getJob')
            ->once()
            ->withArgs(fn ($jobId) => (int) $jobId === $id)
            ->andReturn($payload);
        $this->app->instance(JobAPI::class, $jobs);
    }

    /** @param  list<array<string, mixed>>  $cvs */
    private function mockProfileCvs(array $cvs): void
    {
        $api = Mockery::mock(CandidateAPI::class);
        $api->shouldReceive('getProfile')->once()->andReturn([
            'message' => 'OK',
            'data' => [
                'name' => 'Candidate',
                'cvs' => $cvs,
            ],
            'errors' => [],
            'statusCode' => 200,
        ]);
        $this->app->instance(CandidateAPI::class, $api);
    }
}
