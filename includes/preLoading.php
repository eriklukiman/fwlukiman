<?php
// require_once('vendor/autoload.php');
echo '<pre>';

function loadClass($className) {
	$prefix = 'Cores';
	$NsPrefix = 'Lukiman';
	$fileName = '';
	$namespace = '';

	// Sets the include path as the "src" directory
	$includePath = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR;//.'src';
	// echo "\n";var_dump($className);
	//remove namespace prefix
	if (!empty($NsPrefix)) $className = substr($className, strlen($NsPrefix) + 1);
	// echo $includePath . '==';
	if (false !== ($lastNsPos = strripos($className, '\\'))) {
		$namespace = substr($className, 0, $lastNsPos);//echo $className;
		$className = substr($className, $lastNsPos + 1);
		$fileName = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
	}
	$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
	$fullFileName = $includePath . DIRECTORY_SEPARATOR . $fileName;
   // echo "\n";var_dump($fileName);
	if (is_readable($fullFileName)) {
		require_once $fullFileName;
	} else {
		// echo 'Class "'.$className.'" does not exist.';
	}
}
spl_autoload_register('loadClass'); // Registers the autoloader
