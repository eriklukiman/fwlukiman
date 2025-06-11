<?php

namespace Lukiman\Cores;

enum Env {
  case PRODUCTION;
  case STAGING;

  /**
   * Get environment path name
   *
   * @return string
   * */
  public function getPathname(): string {
    return match ($this) {
      self::PRODUCTION => 'production',
      self::STAGING => 'staging',
    };
  }
}
