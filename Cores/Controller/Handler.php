<?php

namespace Lukiman\Cores\Controller;

use \Lukiman\Cores\Exception\Base as ExceptionBase;
use Lukiman\Cores\Exception\NotFoundException;

function hasClassMethod($objectOrClass, string $methodName): bool {
    $reflection = new \ReflectionClass($objectOrClass);
    return $reflection->hasMethod($methodName);
}

class Handler {

    public static function run(): void {
        $fullPath = (!empty($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : (!empty($_SERVER['REQUEST_URI']) ? strtok($_SERVER['REQUEST_URI'], '?') : ''));

        if (!empty($fullPath)) {
            $path = explode('/', $fullPath);
            if (empty($path[0])) array_shift($path);
            if (end($path) == '') array_pop($path);
            
            $_path = $path;
            foreach ($path as $k => $v) {
                $path[$k] = preg_replace_callback('/(_[a-z])/', function ($word) {
                    return strtoupper($word[1]);
                }, ucwords(strtolower($v)));
            }
            $class = implode('\\', $path);
            
            $retVal = null;
            $action = '';
            $_param = '';
            $params = array();
            while (!Base::exists($class) AND !empty($class)) {
                if (!empty($action)) array_unshift($params, $_param);
                $action = array_pop($path);
                $_param = array_pop($_path);
                $class = implode('\\', $path);
            }

            try {
                if (empty($class)) {
                    throw new NotFoundException('Handler not found!', 404);
                }
                Base::set_action($action);

                $ctrl = Base::load($class);
                $retVal = $ctrl->execute($action, $params);
                $ctrl->sendHeaders();
                echo $retVal;
            } catch (\Throwable | ExceptionBase $e) {
                if (!headers_sent()) {
                    $httpCode = 500;
                    
                    if (hasClassMethod($e, 'getHttpCode')) {
                        $httpCode = $e->getHttpCode();
                    }
                    http_response_code($httpCode);
                    header('Content-Type: application/json');
                }
                echo json_encode([
                    'status' => [
                        'error'     => true,
                        'errorCode' => $e->getCode(),
                        'message'   => $e->getMessage()
                    ]
                ]);
            }
        }
    }
}