<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Hard safety guard: tests MUST run on the dedicated test database.
     *
     * `RefreshDatabase` wipes whichever DB is connected — so if phpunit.xml
     * env vars fail to override `.env` (e.g. missing `force="true"`), the
     * dev database gets nuked. This guard catches that at the earliest
     * possible moment and aborts the run with a loud error instead.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $database = config('database.connections.pgsql.database');
        $allowed  = ['emergence_bassila_test'];

        if (! in_array($database, $allowed, true)) {
            throw new RuntimeException(
                "Refusing to run tests against database [$database]. " .
                'Expected one of [' . implode(', ', $allowed) . ']. ' .
                'Check phpunit.xml <env> tags (must have force="true") and .env.testing.'
            );
        }
    }
}
