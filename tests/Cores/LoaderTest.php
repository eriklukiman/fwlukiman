<?php

declare(strict_types=1);

namespace Lukiman\tests\Cores;

use PHPUnit\Framework\TestCase;
use Lukiman\Cores\Loader;

final class LoaderTest extends TestCase {
  public function testResolveConfigFileWithoutEnv(): void {
    $file = 'Cache';
    $expected = 'config/Cache.php';
    $this->assertEquals($expected, Loader::resolveConfigFile($file));
  }

  public function testResolveConfigFileWithEnv(): void {
    $file = 'Database';
    $expected = 'config/Database.staging.php';
    $this->assertEquals($expected, Loader::resolveConfigFile($file));
  }
}
