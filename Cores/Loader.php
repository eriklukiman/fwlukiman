<?php
namespace Lukiman\Cores;

class Loader {
	protected static $_path     = 'Additional/';

	protected static $_config   = 'config/';

	protected static $_assets   = 'Assets/';
	protected static $_assetsHtml   = 'Html/';
	protected static $_assetsImage   = 'Images/';

	public static function Load ($module) {
		self::Include_File($module);
	}

	private static function Include_File ($module) {
		$file = self::$_path . $module . '/' . $module . '.php';
		if (is_readable($file)) include_once($file);
        else if (is_readable(static::getRootFolder() . $file)) return include_once(static::getRootFolder() . $file);
		else if (is_readable(ROOT_PATH . $file)) include_once(ROOT_PATH . $file);
		else if (is_readable(LUKIMAN_ROOT_PATH . $file)) include_once(LUKIMAN_ROOT_PATH . $file);
	}

    /**
     * Function for load file configuration
     * @param type $file
     */
    public static function Config($file = '') {
        $file = self::$_config . $file . '.php';
		if (is_readable($file)) return include($file);
		else if (is_readable(ROOT_PATH . $file)) return include_once(ROOT_PATH . $file);
		else if (is_readable(static::getRootFolder() . $file)) return include_once(static::getRootFolder() . $file);
		else if (is_readable(LUKIMAN_ROOT_PATH . $file)) return include_once(LUKIMAN_ROOT_PATH . $file);
    }

     /**
     * Function for load file AssetsHtml
     * @param type $file
     */
    public static function AssetsHtml($file = '') {
        $file = self::$_assets . self::$_assetsHtml . $file . '.htm';
        if (is_readable($file)) return file_get_contents($file);
        else if (is_readable(static::getRootFolder() . $file)) return file_get_contents(static::getRootFolder() . $file);

    }

	public static function Include_Assets($file = '', $type = 'htm') {
        $file = self::$_assets . self::$_assetsHtml . $file . '.' . $type;
        if (is_readable($file)) return include($file);
        else if (is_readable(static::getRootFolder() . $file)) return include_once(static::getRootFolder() . $file);
    }

    /**
     * Function for load file AssetsImage
     * @param type $file
     */
    public static function AssetsImage($file = '') {
        $file = self::$_assets . self::$_assetsImage . $file;
            if (is_readable($file)) return $file;
    }

    public static function getRootFolder() {
        return dirname(__DIR__) . '/';
    }

}
