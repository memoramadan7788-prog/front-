<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Support\Frontend\MockCandidateData;
use Illuminate\Contracts\View\View;

/**
 * Figma 21 — Candidate messages.
 * Frontend presentation only. No persistence or delivery.
 */
class MessageController extends Controller
{
    public function index(): View
    {
        $messages = MockCandidateData::messages();

        return view('candidate.messages.index', [
            'pageTitle' => 'Messages · Hirebridge',
            'uiRole' => 'candidate',
            'showMockBanner' => false,
            'inbox' => $messages,
            'frontendOnly' => true,
        ]);
    }
}
