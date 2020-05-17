<?php

function loadClass($className) {
	$NsPrefix = LUKIMAN_NAMESPACE_PREFIX;
	$fileName = '';
	$namespace = '';

	// Sets the include path as the "src" directory
	$includePath = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR;

	//remove namespace prefix
	if (!empty($NsPrefix)) $className = substr($className, strlen($NsPrefix) + 1);

	if (false !== ($lastNsPos = strripos($className, '\\'))) {
		$namespace = substr($className, 0, $lastNsPos);
		$className = substr($className, $lastNsPos + 1);
		$fileName = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
	}
	$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
	$fullFileName = $includePath . DIRECTORY_SEPARATOR . $fileName;

	if (is_readable($fullFileName)) {
		require_once $fullFileName;
	} else {
		// echo 'Class "'.$className.'" does not exist.';
	}
}
spl_autoload_register('loadClass'); // Registers the autoloader
