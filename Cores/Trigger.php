<?php
namespace Lukiman\Cores;

use Lukiman\Cores\Trigger\Factory;

class Trigger implements Interfaces\Trigger {
	static protected array $config;
	static protected Trigger $instance;

	protected Interfaces\Trigger $trigger;

    public function __construct(
        ?string $engine = 'resource',
        int $connectionTimeout = 5
    ) {
        $this->trigger = Factory::instantiate(
            $engine, 
            $connectionTimeout
        );
    }

    public static function getInstance(
        ?string $engine = null,
        int $connectionTimeout = 5
    ) :static {
		if (Factory::allowSingleton($engine)) {
			if(!isset(static::$instance)) {
				static::$instance = new static(
                    $engine, 
                    $connectionTimeout
                );
			}
            
			return static::$instance;
		} else {
			return new static(
                $engine, 
                $connectionTimeout
            );
		}
	}

	public function get(String $url, String|array $params = '') : void {
        $this->trigger->get($url, $params);
    }

	public function post(String $url, String|array $params = '') : void {
        $this->trigger->post($url, $params);
    }

	public function put(String $url, String|array $params = '') : void {
        $this->trigger->put($url, $params);
    }

	public function patch(String $url, String|array $params = '') : void {
        $this->trigger->patch($url, $params);
    }

	public function delete(String $url, String|array $params = '') : void {
        $this->trigger->delete($url, $params);
    }

    public function addHeaders(array $headers, bool $isOverwrite = false) : void {
        $this->trigger->addHeaders($headers, $isOverwrite);
    }
}
