<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitc084725c6f55fd18ad00c31b5a736e39 {
	public static $files = array(
		'691395991fe1099c3e8332668bd4347c' => __DIR__ . '/..' . '/simplehtmldom/simplehtmldom/simple_html_dom.php',
	);

	public static $classMap = array(
		'UnusedCSS'                     => __DIR__ . '/../..' . '/includes/UnusedCSS.php',
		'UnusedCSS_Admin'               => __DIR__ . '/../..' . '/includes/UnusedCSS_Admin.php',
		'UnusedCSS_Api'                 => __DIR__ . '/../..' . '/includes/UnusedCSS_Api.php',
		'UnusedCSS_Autoptimize'         => __DIR__ . '/../..' . '/includes/Autoptimize/UnusedCSS_Autoptimize.php',
		'UnusedCSS_Autoptimize_Admin'   => __DIR__ . '/../..' . '/includes/Autoptimize/UnusedCSS_Autoptimize_Admin.php',
		'UnusedCSS_Autoptimize_Onboard' => __DIR__ . '/../..' . '/includes/Autoptimize/UnusedCSS_Autoptimize_Onboard.php',
		'UnusedCSS_Settings'            => __DIR__ . '/../..' . '/includes/UnusedCSS_Settings.php',
		'UnusedCSS_Store'               => __DIR__ . '/../..' . '/includes/UnusedCSS_Store.php',
		'UnusedCSS_Utils'               => __DIR__ . '/../..' . '/includes/UnusedCSS_Utils.php',
		'simplehtmldom\\Debug'          => __DIR__ . '/..' . '/simplehtmldom/simplehtmldom/Debug.php',
		'simplehtmldom\\HtmlDocument'   => __DIR__ . '/..' . '/simplehtmldom/simplehtmldom/HtmlDocument.php',
		'simplehtmldom\\HtmlNode'       => __DIR__ . '/..' . '/simplehtmldom/simplehtmldom/HtmlNode.php',
		'simplehtmldom\\HtmlWeb'        => __DIR__ . '/..' . '/simplehtmldom/simplehtmldom/HtmlWeb.php',
	);

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
	        $loader->classMap = ComposerStaticInitc084725c6f55fd18ad00c31b5a736e39::$classMap;

        }, null, ClassLoader::class);
    }
}
