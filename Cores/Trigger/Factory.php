<?php

namespace Lukiman\Cores\Trigger;

use Lukiman\Cores\Interfaces\Trigger;

class Factory {
  private static String $path = '\\Lukiman\\Cores\\Trigger\\Engine\\';

  /**
   * instantiate trigger engine
   *
   * @param string $engine
   * @param int $connectionTimeout
   * @return Trigger
   * */
  public static function instantiate(string $engine, int $connectionTimeout): Trigger {
    $class = static::$path . ucfirst(strtolower($engine));
    return new $class($connectionTimeout);
  }

  /**
   * check if allow singleton
   *
   * @param string $engine
   * @return bool
   * */
  public static function allowSingleton(string $engine): bool {
    $class = static::$path . ucfirst(strtolower($engine));
    return $class::allowSingleton();
  }
}
