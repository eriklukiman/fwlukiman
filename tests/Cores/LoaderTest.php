<?php

declare(strict_types=1);

namespace Lukiman\tests\Cores;

use Lukiman\Cores\Env;
use PHPUnit\Framework\TestCase;
use Lukiman\Cores\Loader;

final class LoaderTest extends TestCase {
  public function testResolveConfigFileWithoutEnv(): void {
    $file = 'Cache';
    $expected = 'config/Cache.php';
    $this->assertEquals($expected, Loader::resolveConfigFile($file));
  }

  public function testResolveEnvMethodOutput(): void {
    $ref = new \ReflectionClass(Loader::class);
    $method = $ref->getMethod('resolveEnv');
    $method->setAccessible(true);
    $result = $method->invoke(null, 'config/Env.php');
    $this->assertNull($result);

    $file = 'DummyConfig';
    $expected = 'config/' . $file . '.staging.php';
    $envFile = 'config/Env.php';
    file_put_contents($envFile, '<?php use Lukiman\Cores\Env; return Env::STAGING;');
    touch($expected);
    $result = $method->invoke(null, $envFile);
    $this->assertNotNull($result);
    $this->assertEquals(Env::STAGING, $result);
    $this->assertEquals('.staging', $result->getPathname());
    unlink($expected);
    unlink($envFile);

    file_put_contents($envFile, '<?php use Lukiman\Cores\Env; return Env::PRODUCTION;');
    touch($expected);
    $result = $method->invoke(null, $envFile);
    $this->assertNotNull($result);
    $this->assertEquals(Env::PRODUCTION, $result);
    $this->assertEquals('.production', $result->getPathname());
    unlink($expected);
    unlink($envFile);

    file_put_contents($envFile, '<?php use Lukiman\Cores\Env; return Env::DEVELOPMENT;');
    touch($expected);
    $result = $method->invoke(null, $envFile);
    $this->assertNotNull($result);
    $this->assertEquals(Env::DEVELOPMENT, $result);
    $this->assertEmpty($result->getPathname());
    unlink($expected);
    unlink($envFile);
  }

  public function testResolveEnvWhenTheEnvFileContentIsEmpty(): void {
    $ref = new \ReflectionClass(Loader::class);
    $method = $ref->getMethod('resolveEnv');
    $method->setAccessible(true);
    $envFile = 'config/Env.php';
    file_put_contents($envFile, '');
    $result = $method->invoke(null, $envFile);
    $this->assertIsInt($result);
    unlink($envFile);

    $file = 'DummyConfig';
    $expected = 'config/' . $file . '.php';
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
    copy('config/Env_example.php', 'config/Env.php');

    touch($expected);
    $this->assertEquals($expected, Loader::resolveConfigFile($file));
    unlink($expected);
    unlink('config/Env.php');
  }

  public function testResolveConfigFileDefaultDevelopmentEnv(): void {
    $file = 'DummyConfig';
    $envFile = 'config/Env.php';
    $expected = 'config/' . $file . '.php';
    file_put_contents($envFile, '<?php use Lukiman\Cores\Env; return Env::DEVELOPMENT;');

    touch($expected);
    $this->assertEquals($expected, Loader::resolveConfigFile($file));
    unlink($expected);
    unlink($envFile);
  }
}
