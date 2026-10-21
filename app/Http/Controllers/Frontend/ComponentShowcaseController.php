<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Stage 2 component showcase — presentation only with mock data.
 */
class ComponentShowcaseController extends Controller
{
    /**
     * @var list<string>
     */
    private const UI_ROLES = ['candidate', 'company', 'interviewer', 'admin'];

    public function __invoke(Request $request): View
    {
        $uiRole = $request->query('role', 'company');

        if (! in_array($uiRole, self::UI_ROLES, true)) {
            $uiRole = 'company';
        }

        return view('frontend.components.showcase', [
            'uiRole' => $uiRole,
            'showMockBanner' => true,
            'kanbanColumns' => $this->mockKanbanColumns(),
            'interviewSlots' => $this->mockInterviewSlots(),
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function mockKanbanColumns(): array
    {
        return [
            [
                'title' => 'Applied',
                'cards' => [
                    ['name' => 'Sofia Nguyen', 'meta' => 'Systems · Research', 'source' => 'Direct hire', 'owner' => 'Olivia Bennett / Applied Oct 12'],
                    ['name' => 'Marcus Reed', 'meta' => 'Product · Strategy', 'source' => 'Agency-managed', 'owner' => 'Olivia Bennett / Applied Oct 11'],
                    ['name' => 'Zoe Park', 'meta' => 'Design systems', 'source' => 'Direct hire', 'owner' => 'Olivia Bennett / Applied Oct 10'],
                ],
            ],
            [
                'title' => 'Screening',
                'cards' => [
                    ['name' => 'Noah Williams', 'meta' => 'UX · Research', 'source' => 'Direct hire', 'owner' => 'Olivia Bennett / Applied Oct 9'],
                    ['name' => 'Ella Davis', 'meta' => 'Visual · Brand', 'source' => 'Agency-managed', 'owner' => 'Olivia Bennett / Applied Oct 8'],
                    ['name' => 'Ben Carter', 'meta' => 'Interaction', 'source' => 'Direct hire', 'owner' => 'Olivia Bennett / Applied Oct 7'],
                ],
            ],
            [
                'title' => 'Interview',
                'cards' => [
                    [
                        'name' => 'Maya Chen',
                        'meta' => 'Systems · Research',
                        'source' => 'Direct hire',
                        'owner' => 'Olivia Bennett / Applied Oct 12',
                        'highlighted' => true,
                        'flag' => 'Oct 22 · 10:00 AM · Portfolio interview',
                    ],
                    ['name' => 'Luis Romero', 'meta' => 'Product design', 'source' => 'Agency-managed', 'owner' => 'Olivia Bennett / Applied Oct 6'],
                    ['name' => 'Aisha Patel', 'meta' => 'Research · Ops', 'source' => 'Direct hire', 'owner' => 'Olivia Bennett / Applied Oct 5'],
                ],
            ],
            [
                'title' => 'Offer',
                'cards' => [
                    [
                        'name' => 'Ethan Cole',
                        'meta' => 'Senior design',
                        'source' => 'Agency-managed',
                        'owner' => 'Olivia Bennett / Applied Oct 4',
                        'badge' => ['variant' => 'pending', 'label' => 'Draft offer'],
                    ],
                    ['name' => 'Nora Kim', 'meta' => 'Product · Growth', 'source' => 'Direct hire', 'owner' => 'Olivia Bennett / Applied Oct 3'],
                ],
            ],
            [
                'title' => 'Hired',
                'cards' => [
                    [
                        'name' => 'Grace Liu',
                        'meta' => 'Design lead',
                        'source' => 'Direct hire',
                        'owner' => 'Olivia Bennett / Applied Sep 28',
                        'badge' => ['variant' => 'success', 'label' => 'Hired Oct 16'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function mockInterviewSlots(): array
    {
        return [
            ['value' => '2026-10-22T10:00', 'label' => 'Thu, Oct 22 · 10:00 AM', 'meta' => '45 min · Central Time'],
            ['value' => '2026-10-22T14:00', 'label' => 'Thu, Oct 22 · 2:00 PM', 'meta' => '45 min · Central Time'],
            ['value' => '2026-10-23T09:00', 'label' => 'Fri, Oct 23 · 9:00 AM', 'meta' => '45 min · Central Time', 'unavailable' => true],
            ['value' => '2026-10-23T11:30', 'label' => 'Fri, Oct 23 · 11:30 AM', 'meta' => '45 min · Central Time'],
            ['value' => '2026-10-24T15:00', 'label' => 'Sat, Oct 24 · 3:00 PM', 'meta' => 'Unavailable weekend', 'unavailable' => true],
            ['value' => '2026-10-27T10:00', 'label' => 'Mon, Oct 27 · 10:00 AM', 'meta' => '45 min · Central Time'],
        ];
    }
}
