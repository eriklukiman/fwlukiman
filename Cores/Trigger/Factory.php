<?php

namespace Lukiman\Cores\Trigger;

use Lukiman\Cores\Interfaces\Trigger;

class Factory {
  private static String $path = '\\Lukiman\\Cores\\Trigger\\Engine\\';

  /**
   * instantiate trigger engine
   *
   * @param string $engine
   * @return Trigger
   * */
  public static function instantiate(string $engine): Trigger {
    $class = static::$path . ucfirst(strtolower($engine));
    return new $class();
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
