<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Support\Frontend\MockCandidateData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Figma 21 — Candidate messages.
 * Frontend presentation only. No persistence or delivery.
 */
class MessageController extends Controller
{
    public function index(Request $request): View
    {
        $messages = MockCandidateData::messages();
        $threadId = $request->query('thread');

        if (is_string($threadId) && $threadId !== '' && isset($messages['threads'])) {
            $messages['threads'] = array_map(
                static function (array $thread) use ($threadId): array {
                    $thread['selected'] = ($thread['id'] ?? null) === $threadId;

                    return $thread;
                },
                $messages['threads']
            );
        }

        return view('candidate.messages.index', [
            'pageTitle' => 'Messages · Hirebridge',
            'uiRole' => 'candidate',
            'showMockBanner' => false,
            'inbox' => $messages,
            'frontendOnly' => true,
        ]);
    }
}
