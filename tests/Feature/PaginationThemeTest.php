<?php

namespace Tests\Feature;

use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class PaginationThemeTest extends TestCase
{
    public function test_application_uses_numbered_bootstrap_pagination_without_tailwind_svg_arrows(): void
    {
        $paginator = new LengthAwarePaginator(
            range(1, 50),
            120,
            50,
            2,
            ['path' => '/staff/audit-logs']
        );

        $html = (string) $paginator->links();

        $this->assertStringContainsString('<ul class="pagination">', $html);
        $this->assertStringContainsString('aria-current="page"><span class="page-link">2</span>', $html);
        $this->assertStringNotContainsString('<svg', $html);
    }
}
