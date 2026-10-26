<?php

namespace Tests\Unit;

use App\Support\Frontend\CandidateReturnPath;
use Illuminate\Http\Request;
use Tests\TestCase;

class CandidateReturnPathTest extends TestCase
{
    public function test_vacancy_id_builds_safe_apply_path(): void
    {
        $request = Request::create('/candidate/resume/upload', 'GET', ['vacancy_id' => 42]);

        $this->assertSame(
            '/candidate/vacancies/42/apply',
            CandidateReturnPath::applyPathFromRequest($request)
        );
    }

    public function test_relative_apply_return_to_is_accepted(): void
    {
        $this->assertSame(
            '/candidate/vacancies/7/apply',
            CandidateReturnPath::sanitizeApplyPath('/candidate/vacancies/7/apply')
        );
    }

    public function test_rejects_absolute_and_protocol_relative_urls(): void
    {
        $this->assertNull(CandidateReturnPath::sanitizeApplyPath('https://evil.example/candidate/vacancies/1/apply'));
        $this->assertNull(CandidateReturnPath::sanitizeApplyPath('//evil.example/candidate/vacancies/1/apply'));
        $this->assertNull(CandidateReturnPath::sanitizeApplyPath('javascript:alert(1)'));
        $this->assertNull(CandidateReturnPath::sanitizeApplyPath('/admin/users'));
        $this->assertNull(CandidateReturnPath::sanitizeApplyPath('/candidate/vacancies/1'));
        $this->assertNull(CandidateReturnPath::sanitizeApplyPath('candidate/vacancies/1/apply'));
    }

    public function test_forged_return_to_ignored_when_vacancy_id_present(): void
    {
        $request = Request::create('/candidate/resume/upload', 'POST', [
            'vacancy_id' => 9,
            'return_to' => 'https://evil.example/phish',
        ]);

        $this->assertSame(
            '/candidate/vacancies/9/apply',
            CandidateReturnPath::applyPathFromRequest($request)
        );
    }
}
