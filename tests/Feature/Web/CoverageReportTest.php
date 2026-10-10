<?php

namespace Tests\Feature\Web;

use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\TestCase;

class CoverageReportTest extends TestCase
{
    public function test_coverage_index_injects_base_href_for_relative_links(): void
    {
        $dir = public_path('coverage');
        File::ensureDirectoryExists($dir);
        File::put($dir.'/index.html', '<!DOCTYPE html><html><head><title>c</title></head><body>coverage-root-ok</body></html>');

        $this->get('/coverage')
            ->assertOk()
            ->assertSee('coverage-root-ok', false)
            ->assertSee('<base href="'.url('/coverage/').'">', false);
    }

    public function test_coverage_serves_folder_pages_and_assets(): void
    {
        $dir = public_path('coverage');
        File::ensureDirectoryExists($dir.'/Enums');
        File::put($dir.'/Enums/index.html', '<!DOCTYPE html><html><head></head><body>enums-folder-ok</body></html>');
        File::ensureDirectoryExists($dir.'/_css');
        File::put($dir.'/_css/style.css', 'body{}');

        $this->get('/coverage/Enums/index.html')
            ->assertOk()
            ->assertSee('enums-folder-ok', false)
            ->assertSee('<base href="'.url('/coverage/').'">', false);

        // Directory URL → index.html
        $this->get('/coverage/Enums')
            ->assertOk()
            ->assertSee('enums-folder-ok', false);

        $asset = $this->get('/coverage/_css/style.css');
        $asset->assertOk();
        $this->assertInstanceOf(BinaryFileResponse::class, $asset->baseResponse);
    }
}
