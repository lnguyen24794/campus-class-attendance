<?php

namespace Tests\Feature\Web;

use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\TestCase;

class CoverageReportTest extends TestCase
{
    public function test_local_coverage_html_is_served_when_report_exists(): void
    {
        $dir = base_path('docs/coverage/html');
        File::ensureDirectoryExists($dir);
        File::put($dir.'/index.html', '<html><body>coverage-ok</body></html>');

        $response = $this->get('/coverage');

        $response->assertOk();
        $this->assertInstanceOf(BinaryFileResponse::class, $response->baseResponse);
        $this->assertSame(
            realpath($dir.'/index.html'),
            $response->baseResponse->getFile()->getPathname(),
        );
    }

    public function test_coverage_rejects_path_traversal(): void
    {
        $dir = base_path('docs/coverage/html');
        File::ensureDirectoryExists($dir);
        File::put($dir.'/index.html', '<html></html>');

        $this->get('/coverage/'.urlencode('../README.md'))->assertNotFound();
    }
}
