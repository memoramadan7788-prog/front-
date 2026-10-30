<?php

namespace App\Models;

use App\Frontend\Auth\ApiSession;

/**
 * Public job discovery.
 * Backend: GET /jobs, GET /jobs/{id}, GET /salary-benchmark
 * Candidate saved jobs: POST/DELETE /candidate/saved-jobs/{jobId}
 *
 * /jobs filters (JobIndexRequest): q, title, company_name, location,
 * employment_type (scalar or array), work_mode (scalar or array),
 * company_id, category_id, discipline (aliased to category_id), skills[],
 * max_experience, salary_min, salary_max, published_from, published_to,
 * sort_by (published_at|title|salary_min|deadline), sort_dir, page, per_page (max 50)
 */
class JobAPI extends ApiBase
{
    /** Paginated list. Pagination lives in $result['meta']['pagination']. */
    public function getJobs($filters = [])
    {
        $filters = array_filter((array) $filters, fn ($v) => $v !== null && $v !== '' && $v !== []);

        // Authenticated candidates receive saved_by_me / applied_by_me decorations.
        $auth = ApiSession::check() && ApiSession::accountType() === 'candidate';

        return $this->call('GET', '/jobs', ['query' => $filters, 'auth' => $auth]);
    }

    public function getJob($jobId)
    {
        $auth = ApiSession::check() && ApiSession::accountType() === 'candidate';

        return $this->call('GET', '/jobs/' . (int) $jobId, ['auth' => $auth]);
    }

    /** Keyword search = the normal list with q=... (the backend has no separate search endpoint). */
    public function searchJobs($keyword)
    {
        return $this->getJobs(['q' => $keyword]);
    }

    public function getSalaryBenchmark($jobTitle, $categoryId = null)
    {
        return $this->call('GET', '/salary-benchmark', [
            'query' => array_filter(['job_title' => $jobTitle, 'category_id' => $categoryId]),
            'auth'  => false,
        ]);
    }

    public function saveJob(int $jobId): array
    {
        return $this->call('POST', '/candidate/saved-jobs/' . $jobId, ['auth' => true]);
    }

    public function unsaveJob(int $jobId): array
    {
        return $this->call('DELETE', '/candidate/saved-jobs/' . $jobId, ['auth' => true]);
    }
}
