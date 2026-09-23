<?php

namespace Tests\Feature;

use App\Http\Controllers\SupervisionController;
use App\Services\AuditLogger;
use Error;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class StaffImportFailureHandlingTest extends TestCase
{
    public function test_audit_request_data_contains_safe_uploaded_file_metadata(): void
    {
        $file = UploadedFile::fake()->create('staff-list.csv', 12, 'text/csv');
        $request = Request::create('/staff/import-staff-data', 'POST', [], [], [
            'staff_file' => $file,
        ]);

        $metadata = app(AuditLogger::class)->sanitizedRequestData($request);

        $this->assertSame('staff-list.csv', $metadata['staff_file']['original_name']);
        $this->assertSame('csv', $metadata['staff_file']['extension']);
        $this->assertSame('text/csv', $metadata['staff_file']['client_mime_type']);
        $this->assertTrue($metadata['staff_file']['upload_valid']);
        $this->assertArrayNotHasKey('path', $metadata['staff_file']);
        $this->assertArrayNotHasKey('contents', $metadata['staff_file']);
    }

    public function test_import_converts_a_parser_error_into_a_friendly_referenced_message(): void
    {
        Excel::shouldReceive('import')->once()->andThrow(new Error('Simulated parser failure'));

        $file = UploadedFile::fake()->create('staff-list.csv', 12, 'text/csv');
        $request = Request::create('/staff/import-staff-data', 'POST', [], [], [
            'staff_file' => $file,
        ]);
        $request->setLaravelSession(app('session')->driver());
        $request->attributes->set('request_id', 'staff-import-test-reference');
        app()->instance('request', $request);

        $response = app(SupervisionController::class)->importStaff($request);

        $this->assertTrue($response->isRedirect());
        $this->assertStringContainsString(
            'staff-import-test-reference',
            (string) $request->session()->get('error')
        );
        $this->assertStringNotContainsString(
            'Simulated parser failure',
            (string) $request->session()->get('error')
        );
    }
}
