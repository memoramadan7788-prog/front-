<?php

namespace App\Models;


class AtsAPI extends ApiBase
{
    /** GET: 200 with data=null when this application has not been evaluated yet */
    public function getEvaluation($applicationId): array
    {
        return $this->call('GET', '/company/applications/' . (int) $applicationId . '/ats-evaluation');
    }

    /** POST: queues an evaluation (Backend answers 202), or 409 when one is already running */
    public function startEvaluation($applicationId): array
    {
        return $this->call('POST', '/company/applications/' . (int) $applicationId . '/ats-evaluation', ['json' => []]);
    }

    /** $data = ['decision' => 'accept'|'override', 'reason' => string|null] */
    public function decide($applicationId, array $data): array
    {
        return $this->call('POST', '/company/applications/' . (int) $applicationId . '/ats-evaluation/decision', ['json' => $data]);
    }
}