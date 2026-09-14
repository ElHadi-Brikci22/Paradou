<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Absolute safety guardrail: force isolated in-memory SQLite for all tests
        if (config('database.default') !== 'sqlite') {
            config(['database.default' => 'sqlite']);
            config(['database.connections.sqlite.database' => ':memory:']);
        }
    }
}
