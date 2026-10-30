<?php

namespace Tests\Unit;

use App\Models\AdminAPI;
use ReflectionClass;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Prevents a second getCompany() declaration from landing again (class-load fatal).
 */
class AdminAPIGetCompanyMethodsTest extends TestCase
{
    public function test_admin_api_exposes_distinct_get_company_and_get_company_details_methods(): void
    {
        $reflection = new ReflectionClass(AdminAPI::class);

        $this->assertTrue($reflection->hasMethod('getCompany'));
        $this->assertTrue($reflection->hasMethod('getCompanyDetails'));

        $methods = array_map(
            static fn (ReflectionMethod $m): string => $m->getName(),
            $reflection->getMethods(ReflectionMethod::IS_PUBLIC)
        );

        $this->assertSame(1, count(array_filter($methods, static fn (string $n): bool => $n === 'getCompany')));
        $this->assertSame(1, count(array_filter($methods, static fn (string $n): bool => $n === 'getCompanyDetails')));
    }

    public function test_get_company_and_details_source_target_distinct_backend_paths(): void
    {
        $source = file_get_contents((new ReflectionClass(AdminAPI::class))->getFileName());

        $this->assertNotFalse($source);
        $this->assertSame(1, preg_match_all('/function\s+getCompany\s*\(/', $source));
        $this->assertSame(1, preg_match_all('/function\s+getCompanyDetails\s*\(/', $source));
        $this->assertStringContainsString("/admin/companies/'.\$companyId", $source);
        $this->assertStringContainsString('/admin/companies/{$companyId}/details', $source);
    }
}
