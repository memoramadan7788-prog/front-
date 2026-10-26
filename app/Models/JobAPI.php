<?php

namespace App\Models;

/**
 * Public job discovery (no login needed).
 * Backend: GET /jobs, GET /jobs/{id}, GET /salary-benchmark
 *
 * /jobs filters (see backend JobIndexRequest): q, title, company_name, location, employment_type,
 * work_mode (onsite|remote|hybrid), company_id, category_id, skills[], max_experience,
 * salary_min, salary_max, published_from, published_to, sort_by, sort_dir, page, per_page (max 50)
 */
class JobAPI extends ApiBase
{
    /** Paginated list. The page info comes back in $result['meta']['pagination']. */
    public function getJobs($filters = [])
    {
        // empty form fields ("?q=&location=") are not filters
        $filters = array_filter((array) $filters, fn ($v) => $v !== null && $v !== '' && $v !== []);

        return $this->call('GET', '/jobs', ['query' => $filters, 'auth' => false]);
    }

    public function getJob($jobId)
    {
        return $this->call('GET', '/jobs/' . (int) $jobId, ['auth' => false]);
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
}
