<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\DatabaseBackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class BackupTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        $settingsFile = storage_path('app/backup_settings.json');
        if (File::exists($settingsFile)) {
            File::delete($settingsFile);
        }
        parent::tearDown();
    }

    public function test_database_backup_service_creates_backup_file()
    {
        $service = new DatabaseBackupService();
        $testDir = storage_path('testing_backups');
        
        $result = $service->createBackup($testDir);

        $this->assertTrue($result['success']);
        $this->assertFileExists($result['path']);
        $this->assertGreaterThan(500, filesize($result['path']));

        // Clean up test dir
        File::deleteDirectory($testDir);
    }

    public function test_can_configure_custom_backup_directory()
    {
        $service = new DatabaseBackupService();
        $customDir = storage_path('custom_test_backups');

        $savedDir = $service->setBackupDirectory($customDir);
        $this->assertEquals($customDir, $savedDir);
        $this->assertEquals($customDir, $service->getBackupDirectory());

        $result = $service->createBackup();
        $this->assertFileExists($result['path']);
        $this->assertStringStartsWith($customDir, $result['path']);

        $list = $service->getBackupsList();
        $this->assertNotEmpty($list);
        $this->assertEquals($result['filename'], $list[0]['filename']);

        // Clean up
        File::deleteDirectory($customDir);
    }

    public function test_admin_can_access_backup_endpoints()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Index
        $response = $this->actingAs($admin)->getJson(route('admin.backups.index'));
        $response->assertOk();
        $response->assertJsonStructure(['directory', 'is_default', 'backups']);

        // Update directory
        $newDir = storage_path('admin_test_backups');
        $updateResponse = $this->actingAs($admin)->postJson(route('admin.backups.directory'), [
            'directory' => $newDir,
        ]);
        $updateResponse->assertOk();
        $updateResponse->assertJson(['success' => true]);

        // Run backup
        $runResponse = $this->actingAs($admin)->postJson(route('admin.backups.run'));
        $runResponse->assertOk();
        $runResponse->assertJson(['success' => true]);
        $data = $runResponse->json();
        $filename = $data['backup']['filename'];

        // Download
        $downloadResponse = $this->actingAs($admin)->get(route('admin.backups.download', ['filename' => $filename]));
        $downloadResponse->assertOk();
        $downloadResponse->assertHeader('content-disposition');

        // Cleanup
        File::deleteDirectory($newDir);
    }

    public function test_cashier_cannot_access_backup_endpoints()
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $this->actingAs($cashier)->getJson(route('admin.backups.index'))->assertForbidden();
        $this->actingAs($cashier)->postJson(route('admin.backups.directory'), ['directory' => 'C:\test'])->assertForbidden();
        $this->actingAs($cashier)->postJson(route('admin.backups.run'))->assertForbidden();
    }

    public function test_artisan_backup_command_works()
    {
        $testDir = storage_path('artisan_test_backups');
        $this->artisan('paradou:backup', ['--dir' => $testDir])
            ->assertExitCode(0);

        $this->assertDirectoryExists($testDir);
        $files = File::files($testDir);
        $this->assertNotEmpty($files);

        // Cleanup
        File::deleteDirectory($testDir);
    }
}
