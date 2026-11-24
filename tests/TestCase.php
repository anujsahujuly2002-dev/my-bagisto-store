<?php

namespace Tests;

use EcomForYou\Core\Tests\Concerns\ConfiguresSettings;
use EcomForYou\Core\Tests\Concerns\CreatesUploads;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use ConfiguresSettings, CreatesUploads, DatabaseTransactions;
}
