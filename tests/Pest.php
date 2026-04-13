<?php

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)
    ->beforeEach(function () {
        $this->seed(RolePermissionSeeder::class);
    })
    ->in('Feature');

uses(TestCase::class)->in('Unit/Seo');

uses(TestCase::class, RefreshDatabase::class)
    ->beforeEach(function () {
        $this->seed(RolePermissionSeeder::class);
    })
    ->in('Unit/AccountDeletionRequestTest.php');
