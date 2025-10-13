<?php

namespace Lukiman\Cores\Controller;

use \Lukiman\Cores\Exception\Base as ExceptionBase;
use \Lukiman\Cores\HttpStatus;

function hasClassConstant($objectOrClass, string $constName): bool {
    $reflection = new \ReflectionClass($objectOrClass);
    return $reflection->hasConstant($constName);
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
                $path[$k] = preg_replace_callback('/(\_[a-z])/', function ($word) {
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
                    throw new ExceptionBase('Handler not found!', 404);
                }
                Base::set_action($action);

                $ctrl = Base::load($class);
                $retVal = $ctrl->execute($action, $params);
                $ctrl->sendHeaders();
                echo $retVal;
            } catch (ExceptionBase | \PDOException $e) {
                if (!headers_sent()) {
                    $httpCode = 404;
                    
                    if (hasClassConstant($e, 'HTTP_CODE')) {
                        if (HttpStatus::isValid($e::HTTP_CODE)) {
                            $httpCode = $e::HTTP_CODE;
                        }
                    }
                    $httpMessage = HttpStatus::getMessage($httpCode);
                    header("HTTP/1.0 $httpCode $httpMessage");
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