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

  public function testResolveConfigFileWithStagingEnv(): void {
    $file = 'DummyConfig';
    $envFile = 'config/Env.php';
    $expected = 'config/' . $file . '.staging.php';
    file_put_contents($envFile, '<?php use Lukiman\Cores\Env; return Env::STAGING;');

    touch($expected);
    $this->assertEquals($expected, Loader::resolveConfigFile($file));
    unlink($expected);
    unlink($envFile);
  }

  public function testResolveConfigFileDefaultProductionEnv(): void {
    $file = 'DummyConfig';
    $expected = 'config/' . $file . '.production.php';

    touch($expected);
    $this->assertEquals($expected, Loader::resolveConfigFile($file));
    unlink($expected);
  }
}
