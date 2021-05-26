<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit0846b02cb01ca9de966b580779093b89
{
    public static $classMap = array (
        'Cache_Enabler_Compatible' => __DIR__ . '/../..' . '/services/third-party/plugins/cache-enabler/Cache_Enabler_Compatible.php',
        'Cloudflare_Compatible' => __DIR__ . '/../..' . '/services/third-party/plugins/cloudflare/Cloudflare_Compatible.php',
        'Cookie_Notice_Compatible' => __DIR__ . '/../..' . '/services/third-party/plugins/cookie-notice/Cookie_Notice_Compatible.php',
        'Elementor_Compatible' => __DIR__ . '/../..' . '/services/third-party/plugins/elementor/Elementor_Compatible.php',
        'GoogleModPageSpeedCompatible' => __DIR__ . '/../..' . '/services/third-party/plugins/kagg-pagespeed-module/GoogleModPageSpeedCompatible.php',
        'Kinsta_Cache_Compatible' => __DIR__ . '/../..' . '/services/third-party/plugins/kinsta-cache/Kinsta_Cache_Compatible.php',
        'LiteSpeed_Cache_Compatible' => __DIR__ . '/../..' . '/services/third-party/plugins/litespeed-cache/LiteSpeed_Cache_Compatible.php',
        'Nginx_Helper_Compatible' => __DIR__ . '/../..' . '/services/third-party/plugins/nginx-helper/Nginx_Helper_Compatible.php',
        'Optimole_WP_Compatible' => __DIR__ . '/../..' . '/services/third-party/plugins/optimole-wp/Optimole_WP_Compatible.php',
        'RankMathSEO_Compatible' => __DIR__ . '/../..' . '/services/third-party/plugins/seo-by-rank-math/RankMathSEO_Compatible.php',
        'RapidLoad_Api' => __DIR__ . '/../..' . '/services/RapidLoad_Api.php',
        'RapidLoad_Base' => __DIR__ . '/../..' . '/services/RapidLoad_Base.php',
        'RapidLoad_DB' => __DIR__ . '/../..' . '/services/RapidLoad_DB.php',
        'RapidLoad_Enqueue' => __DIR__ . '/../..' . '/services/RapidLoad_Enqueue.php',
        'RapidLoad_FileSystem' => __DIR__ . '/../..' . '/services/RapidLoad_FileSystem.php',
        'RapidLoad_Queue' => __DIR__ . '/../..' . '/services/RapidLoad_Queue.php',
        'RapidLoad_Settings' => __DIR__ . '/../..' . '/services/RapidLoad_Settings.php',
        'RapidLoad_Sitemap' => __DIR__ . '/../..' . '/services/RapidLoad_Sitemap.php',
        'RapidLoad_Store' => __DIR__ . '/../..' . '/services/RapidLoad_Store.php',
        'RapidLoad_ThirdParty' => __DIR__ . '/../..' . '/services/third-party/RapidLoad_ThirdParty.php',
        'RapidLoad_Utils' => __DIR__ . '/../..' . '/services/RapidLoad_Utils.php',
        'UnusedCSS' => __DIR__ . '/../..' . '/services/unused-css/UnusedCSS.php',
        'UnusedCSS_Admin' => __DIR__ . '/../..' . '/services/unused-css/UnusedCSS_Admin.php',
        'UnusedCSS_Autoptimize' => __DIR__ . '/../..' . '/services/unused-css/autoptimize/UnusedCSS_Autoptimize.php',
        'UnusedCSS_Autoptimize_Admin' => __DIR__ . '/../..' . '/services/unused-css/autoptimize/UnusedCSS_Autoptimize_Admin.php',
        'UnusedCSS_Autoptimize_Onboard' => __DIR__ . '/../..' . '/services/unused-css/autoptimize/UnusedCSS_Autoptimize_Onboard.php',
        'UnusedCSS_DB' => __DIR__ . '/../..' . '/services/unused-css/UnusedCSS_DB.php',
        'UnusedCSS_Feedback' => __DIR__ . '/../..' . '/services/utils/Feedback/UnusedCSS_Feedback.php',
        'UnusedCSS_Job' => __DIR__ . '/../..' . '/services/unused-css/UnusedCSS_Job.php',
        'UnusedCSS_Path' => __DIR__ . '/../..' . '/services/unused-css/UnusedCSS_Path.php',
        'UnusedCSS_Rule' => __DIR__ . '/../..' . '/services/unused-css/UnusedCSS_Rule.php',
        'W3_Total_Cache_Compatible' => __DIR__ . '/../..' . '/services/third-party/plugins/w3-total-cache/W3_Total_Cache_Compatible.php',
        'WP_Engine_Common_Compatible' => __DIR__ . '/../..' . '/services/third-party/plugins/wpengine-common/WP_Engine_Common_Compatible.php',
        'WP_Fastest_Cache_Compatible' => __DIR__ . '/../..' . '/services/third-party/plugins/wp-fastest-cache/WP_Fastest_Cache_Compatible.php',
        'WP_Optimize_Compatible' => __DIR__ . '/../..' . '/services/third-party/plugins/wp-optimize/WP_Optimize_Compatible.php',
        'WP_Rocket_Compatible' => __DIR__ . '/../..' . '/services/third-party/plugins/wp-rocket/WP_Rocket_Compatible.php',
        'WP_Super_Cache_Compatible' => __DIR__ . '/../..' . '/services/third-party/plugins/wp-super-cache/WP_Super_Cache_Compatible.php',
        'Woocommerce_Compatible' => __DIR__ . '/../..' . '/services/third-party/plugins/woocommerce/Woocommerce_Compatible.php',
        'YoastSEO_Compatible' => __DIR__ . '/../..' . '/services/third-party/plugins/wordpress-seo/YoastSEO_Compatible.php',
        'simplehtmldom\\Debug' => __DIR__ . '/..' . '/simplehtmldom/simplehtmldom/Debug.php',
        'simplehtmldom\\HtmlDocument' => __DIR__ . '/..' . '/simplehtmldom/simplehtmldom/HtmlDocument.php',
        'simplehtmldom\\HtmlNode' => __DIR__ . '/..' . '/simplehtmldom/simplehtmldom/HtmlNode.php',
        'simplehtmldom\\HtmlWeb' => __DIR__ . '/..' . '/simplehtmldom/simplehtmldom/HtmlWeb.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInit0846b02cb01ca9de966b580779093b89::$classMap;

        }, null, ClassLoader::class);
    }
}
