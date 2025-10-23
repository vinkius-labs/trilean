<?php

namespace VinkiusLabs\Trilean\Tests;

use Illuminate\Support\Facades\Artisan;
use Orchestra\Testbench\TestCase;
use VinkiusLabs\Trilean\TernaryLogicServiceProvider;

class ConsoleCommandsTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [TernaryLogicServiceProvider::class];
    }

    public function test_doctor_command_outputs_json(): void
    {
        $exitCode = Artisan::call('trilean:doctor', ['--json' => true]);

        $this->assertSame(0, $exitCode);

        $output = Artisan::output();
        $decoded = json_decode($output, true);

        $this->assertIsArray($decoded);
        $this->assertNotEmpty($decoded);
    }
}
