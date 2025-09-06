<?php

namespace Lukiman\Cores;

enum Env {
  case PRODUCTION;
  case STAGING;
  case DEVELOPMENT;

  /**
   * Get environment path name
   *
   * @return string
   * */
  public function getPathname(): string {
    return match ($this) {
      self::PRODUCTION => '.production',
      self::STAGING => '.staging',
      self::DEVELOPMENT => '',
    };
  }
}
