<?php

namespace App\Support\Frontend;

use Illuminate\Support\Facades\Route;

/**
 * Frontend-only breadcrumb parsing and safe ancestor linking.
 * Links only when a named route exists and matches the active UI role.
 */
class BreadcrumbNavigator
{
    /**
     * Strip leading Figma-style frame numbers ("25 / …") while keeping real path text.
     */
    public static function stripFrameNumber(string $breadcrumb): string
    {
        return trim((string) preg_replace('/^\d+\s*\/\s*/u', '', trim($breadcrumb)));
    }

    /**
     * @return list<array{label: string, href: ?string, current: bool}>
     */
    public static function segments(string $breadcrumb, string $uiRole = 'candidate'): array
    {
        $cleaned = self::stripFrameNumber($breadcrumb);
        if ($cleaned === '') {
            return [];
        }

        $parts = array_values(array_filter(
            array_map(static fn (string $part): string => trim($part), preg_split('/\s*\/\s*/u', $cleaned) ?: []),
            static fn (string $part): bool => $part !== '',
        ));

        if ($parts === []) {
            return [];
        }

        $map = self::routeMapForRole($uiRole);
        $lastIndex = count($parts) - 1;
        $segments = [];

        foreach ($parts as $index => $label) {
            $isCurrent = $index === $lastIndex;
            $routeName = $map[$label] ?? null;
            $href = null;

            if (
                ! $isCurrent
                && is_string($routeName)
                && Route::has($routeName)
            ) {
                try {
                    $href = route($routeName);
                } catch (\Throwable) {
                    $href = null;
                }
            }

            $segments[] = [
                'label' => $label,
                'href' => $href,
                'current' => $isCurrent,
            ];
        }

        return $segments;
    }

    /**
     * @return array<string, string>
     */
    private static function routeMapForRole(string $uiRole): array
    {
        return match ($uiRole) {
            'company' => self::companyMap(),
            'candidate' => self::candidateMap(),
            'interviewer' => self::interviewerMap(),
            'admin' => self::adminMap(),
            default => [],
        };
    }

    /**
     * @return array<string, string>
     */
    private static function adminMap(): array
    {
        return [
            'Platform' => 'admin.dashboard',
            'المنصة' => 'admin.dashboard',
            __('navigation.admin.overview') => 'admin.dashboard',
            __('navigation.admin.organizations') => 'admin.organizations.index',
            __('navigation.admin.users') => 'admin.users.index',
            __('navigation.admin.moderation') => 'admin.jobs.index',
            __('navigation.admin.agency') => 'admin.agencies.index',
            __('navigation.admin.settings') => 'admin.settings.index',
            'Overview' => 'admin.dashboard',
            'نظرة عامة' => 'admin.dashboard',
            'Organizations' => 'admin.organizations.index',
            'المؤسسات' => 'admin.organizations.index',
            'Users & access' => 'admin.users.index',
            'المستخدمون والوصول' => 'admin.users.index',
            'Job moderation' => 'admin.jobs.index',
            'مراجعة الوظائف' => 'admin.jobs.index',
            'Agency operations' => 'admin.agencies.index',
            'عمليات الوكالة' => 'admin.agencies.index',
            'Settings & audit' => 'admin.settings.index',
            'الإعدادات والتدقيق' => 'admin.settings.index',
            __('admin.dashboard.breadcrumb') => 'admin.dashboard',
            __('admin.organizations.breadcrumb') => 'admin.organizations.index',
            __('admin.users.breadcrumb') => 'admin.users.index',
            __('admin.jobs.breadcrumb') => 'admin.jobs.index',
            __('admin.agencies.breadcrumb') => 'admin.agencies.index',
            __('admin.settings.breadcrumb') => 'admin.settings.index',
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function interviewerMap(): array
    {
        return [
            'Interviewer workspace' => 'interviewer.dashboard',
            'مساحة عمل المقابل' => 'interviewer.dashboard',
            __('navigation.interviewer.overview') => 'interviewer.dashboard',
            __('navigation.interviewer.schedule') => 'interviewer.schedule.index',
            'Overview' => 'interviewer.dashboard',
            'نظرة عامة' => 'interviewer.dashboard',
            'My schedule' => 'interviewer.schedule.index',
            'جدولي' => 'interviewer.schedule.index',
            'Scorecards' => 'interviewer.evaluations.index',
            'بطاقات التقييم' => 'interviewer.evaluations.index',
            __('navigation.interviewer.scorecards') => 'interviewer.evaluations.index',
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function candidateMap(): array
    {
        return [
            // Workspace roots
            __('candidate.workspace') => 'candidate.dashboard',
            'My workspace' => 'candidate.dashboard',
            'مساحة عملي' => 'candidate.dashboard',

            __('navigation.candidate.overview') => 'candidate.dashboard',

            __('navigation.candidate.discover_jobs') => 'candidate.jobs.index',
            __('navigation.candidate.saved_jobs') => 'candidate.jobs.saved',
            __('navigation.candidate.applications') => 'candidate.applications.index',
            __('navigation.candidate.interviews') => 'candidate.interviews.index',
            __('navigation.candidate.messages') => 'candidate.messages.index',
            __('navigation.candidate.assessments') => 'candidate.assessments.index',
            __('navigation.candidate.profile') => 'candidate.profile',
            __('navigation.candidate.preferences') => 'candidate.preferences',
            __('navigation.candidate.offers') => 'candidate.offers.index',
            __('navigation.candidate.notifications') => 'candidate.notifications.index',

            // Assessment / notification breadcrumb prefixes used in lang files
            __('candidate.assessments.breadcrumb_index') => 'candidate.assessments.index',
            'Assessments' => 'candidate.assessments.index',
            'التقييمات' => 'candidate.assessments.index',
            'Notifications' => 'candidate.notifications.index',
            'الإشعارات' => 'candidate.notifications.index',
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function companyMap(): array
    {
        return [
            'Company workspace' => 'company.dashboard',
            'مساحة عمل الشركة' => 'company.dashboard',

            __('navigation.company.overview') => 'company.dashboard',
            __('navigation.company.profile') => 'company.profile',
            __('navigation.company.jobs') => 'company.jobs.index',
            __('navigation.company.applicants') => 'company.applicants.index',
            __('navigation.company.interviews') => 'company.interviews.index',
            __('navigation.company.talent_pool') => 'company.talent-pool.index',
            __('navigation.company.team') => 'company.employees.index',
            __('navigation.company.assessments') => 'company.assessments.index',
            __('navigation.company.analytics') => 'company.analytics.index',

            __('company.employees.requests_breadcrumb') => 'company.employees.requests',
            'Employee join requests' => 'company.employees.requests',
            'طلبات انضمام الموظفين' => 'company.employees.requests',

            __('company.assessments.create_breadcrumb') => 'company.assessments.create',
            'Create' => 'company.assessments.create',
            'إنشاء' => 'company.assessments.create',
        ];
    }
}
