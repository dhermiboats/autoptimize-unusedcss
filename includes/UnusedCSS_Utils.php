<?php

defined( 'ABSPATH' ) or die();

/**
 * Class UnusedCSS
 */
trait UnusedCSS_Utils {

    private static $log_file_system = null;

	public function url_origin( $s, $use_forwarded_host = false ) {
		$ssl      = ( ! empty( $s['HTTPS'] ) && $s['HTTPS'] == 'on' );
		$sp       = strtolower( $s['SERVER_PROTOCOL'] );
        $protocol = substr( $sp, 0, strpos( $sp, '/' ) ) . ( ( $ssl ) ? 's' : '' );
        $port     = $s['SERVER_PORT'];
        $port     = ( ( ! $ssl && $port=='80' ) || ( $ssl && $port=='443' ) ) ? '' : ':'.$port;
        $host     = ( $use_forwarded_host && isset( $s['HTTP_X_FORWARDED_HOST'] ) ) ? $s['HTTP_X_FORWARDED_HOST'] : ( isset( $s['HTTP_HOST'] ) ? $s['HTTP_HOST'] : null );
        $host     = isset( $host ) ? $host : $s['SERVER_NAME'] . $port;
        return $protocol . '://' . $host;
    }

    public function get_current_url($post_id = null)
    {
	    if ( $post_id ) {
		    return get_permalink( get_post( $post_id ) );
	    }


	    global $wp;

	    if ( $wp ) {

		    $query = ( isset( $_SERVER['QUERY_STRING'] ) && ! empty( $_SERVER['QUERY_STRING'] ) ) ? '?' . $_SERVER['QUERY_STRING'] : '';
		    $url   = home_url( $wp->request );

		    if ( ! empty( $url ) && substr( $url, - 1 ) !== '/' ) {
			    $url = $url . '/';
		    }

		    $url = $url . $query;

		    if ( ! empty( $url ) ) {
			    return $url;
		    }


	    }

	    return $this->url_origin( $_SERVER, false ) . $_SERVER['REQUEST_URI'];
    }

    public function is_cli(){

        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            return true;
        }

        return false;
        
    }

    private static function get_log_instance(){
	    if(!self::$log_file_system){
	        return new UnusedCSS_FileSystem();
        }
	    return self::$log_file_system;
    }

    private static function get_log_option(){
        if(is_multisite()){

            $option = get_blog_option(get_current_blog_id(), 'autoptimize_uucss_settings', false);
            return isset($option['uucss_enable_debug']);
        }

        $option = get_site_option( 'autoptimize_uucss_settings', false );
        return  isset($option['uucss_enable_debug']);
    }

    public static function log( $object, $callee = false ) {

	    if ( ! self::get_log_option() || (defined( 'UUCSS_DEBUG' ) && UUCSS_DEBUG == false)) {
		    return false;
	    }

	    $data = is_string( $object ) ? $object : json_encode( $object, JSON_PRETTY_PRINT );
	    //error_log( "[UUCSS_LOG] " . $data );

        $data = is_string( $object ) ? [ 'log' => $object] : $object;

        if(!isset($data['time'])){
            $data['time'] = time();
        }

        if(!isset($data['type'])){
            $data['type'] = 'general';
        }

        $data = json_encode($data);

        $log_instance = self::get_log_instance();

        if($log_instance->exists(UUCSS_LOG_DIR .'debug.log') && !empty($log_instance->get_contents(UUCSS_LOG_DIR .'debug.log'))){
            $data = ",\n" . $data;
        }

        $log_instance->put_contents(UUCSS_LOG_DIR .'debug.log', $data, FILE_APPEND);

	    if ( $callee ) {

		    error_log( "[UUCSS_LOG] " . json_encode( [
				    "file" => debug_backtrace()[1]['file'],
				    "function" => debug_backtrace()[1]['function'],
				    "class" => debug_backtrace()[1]['class'],
				    "args" => debug_backtrace()[1]['args'],
			    ], JSON_PRETTY_PRINT));

	    }

	    return $object;
    }

    public static function uucss_log($object){

	    if ( ! self::get_log_option() || (defined( 'UUCSS_DEBUG' ) && UUCSS_DEBUG == false)) {
		    return false;
	    }

	    $data = is_string( $object ) ? $object : json_encode( $object, JSON_PRETTY_PRINT );
	    error_log( "[UUCSS_LOG] " . $data );
    }

    public static function add_admin_notice($message, $type='error') {

        add_action('admin_notices', function () use ($message, $type) {

            echo "<div class=\"notice notice-$type is-dismissible\">
                    <p>$message</p>
                 </div>";

        });

    }

    public static function add_advanced_admin_notice($notice) {

        if(file_exists(ABSPATH . PLUGINDIR . '/autoptimize-beta/autoptimize.php')){
            return;
        }

        if(!isset($notice)){
            return;
        }
        add_action('admin_notices', function () use ($notice) {

            if(!isset($notice['action'])){
                $notice['action'] = 'uucss-action';
            }

            if(!isset($notice['type'])){
                $notice['type'] = 'error';
            }

            if(!isset($notice['title'])){
                $notice['title'] = 'RapidLoad Power Up';
            }

            if(!isset($notice['message'])){
                $notice['message'] = null;
            }

            if(!isset($notice['actions'])){
                $notice['actions'] = [];
            }

	        if ( ! isset( $notice['main_action'] ) ) {
		        $notice['main_action'] = [];
	        }

	        ?>
            <div class="uucss-notice-action notice notice-action notice-action-<?php echo $notice['action']; ?> notice-<?php echo $notice['type']; ?>">
                <div class="notice-action-inner">
                    <div class="notice-icon">
                        <div class="logo-wrapper">
                            <img
                                    src="<?php echo UUCSS_PLUGIN_URL . 'assets/images/logo-icon.svg' ?>" width="40"
                                    alt="RapidLoad logo">
                        </div>
                    </div>
                    <div class="notice-icon-content">
		                <?php if ( isset( $notice['title'] ) ) : ?>
                            <h2 class="uucss-notice-title"><?php echo $notice['title'] ?></h2>
				        <?php endif; ?>
                        <p>
					        <?php echo $notice['message']; ?>
                        </p>
                        <?php if(!empty($notice['actions'])): ?>
                            <p>
                                <?php foreach ($notice['actions'] as $key => $value) : ?>
                                    <a class="button button-primary" href="<?php echo $value?>"><?php echo $key?></a>
                                <?php endforeach; ?>
                            </p>
                        <?php endif;  ?>

                    </div>
                    <?php if(!empty($notice['main_action'])): ?>
                    <div class="notice-main-action">
                        <p>
                            <a class="button button-primary" href="<?php echo $notice['main_action']['value'] ?>"><?php echo $notice['main_action']['key']?></a>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php

        });

    }

    protected function encode($data)
    {
        return rtrim(md5($data));
    }

    function dirSize($directory) {
        $size = 0;
        foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file){
            $size+=$file->getSize();
        }
        return $size;
    }


    function human_file_size($bytes, $decimals = 2) {
        $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }


    protected function file_name($file, $hash_suffix = null){
    	$file_hash = $this->encode($file . json_encode($hash_suffix));

	    $file_name = explode( "?", basename( $file ) )[0];

	    $final_file_name = 'uucss-' . $file_hash;

	    if ( apply_filters( 'uucss/suffix-original-filename', true ) ) {
            $final_file_name .= '-' . $file_name;
	    }

	    if ( !$this->str_contains( $final_file_name, '.' ) ) {
		    $final_file_name .= '.css';
	    }

	    return $final_file_name;
    }

	function str_contains( $string, $find ) {
		if ( strpos( $string, $find ) !== false ) {
			return true;
		}

		return false;
	}

	function endsWith( $haystack, $needle ) {
		$length = strlen( $needle );
		if( !$length ) {
			return true;
		}
		return substr( $haystack, -$length ) === $needle;
	}

	function transform_url( $url ) {

		$url_parts = parse_url( $url );

		$options = UnusedCSS_Autoptimize_Admin::fetch_options();

		if ( ! isset( $url_parts['host'] ) || ! isset( $url_parts['scheme'] ) ) {
			return $url;
		}

		if ( ! isset( $options['uucss_query_string'] ) || empty( $options['uucss_query_string'] ) ) {
			$path = ( isset( $url_parts['path'] ) ) ? $url_parts['path'] : '';
			$url  = $url_parts['scheme'] . '://' . $url_parts['host'] . $path;
		}

		if(apply_filters('uucss/url/trailingslash', false)){
		    return trailingslashit($url);
        }else{
		    return rtrim($url,'/');
        }
	}

	public static function activate_plugin( $plugin, $action = 'activate' ) {

		if ( strpos( $plugin, '/' ) ) {
			$plugin = str_replace( '\/', '%2F', $plugin );
		}

		$url = sprintf( admin_url( 'plugins.php?action=' . $action . '&plugin=%s&plugin_status=all&paged=1&s' ), $plugin );

		$_REQUEST['plugin'] = sanitize_text_field( $plugin );
		$url                = wp_nonce_url( $url, $action . '-plugin_' . $plugin );

		return $url;
	}

	public function is_uucss_file( $url = null ) {

		if ( ! $url ) {
			$url = $this->url;
		}

		return preg_match( '/uucss\/uucss-[a-z0-9]{32}-/', $url );
	}

    public static function activation_url( $action, $to = 'options-general.php?page=uucss' ) {

	    if ( ! defined( 'UUCSS_ACTIVATION_URL' ) ) {
		    define( 'UUCSS_ACTIVATION_URL', 'https://app.rapidload.io/activate' );
	    }

	    return UUCSS_ACTIVATION_URL . '?' . build_query( [
			    'action' => $action,
			    'nonce'  => wp_create_nonce( 'uucss_activation' ),
			    'site'   => trailingslashit(get_site_url()),
			    'back'   => admin_url( $to ),
			    'goto'   => UUCSS_ACTIVATION_URL
		    ] );
    }

    public static function serialize($data){
        if(isset($data)){
            return serialize($data);
        }else{
            return null;
        }
    }

    public static function unserialize($data){
        if(isset($data)){
            return unserialize($data);
        }else{
            return null;
        }
    }

    public function is_file_excluded( $options, $file ) {

        if ( isset( $options['uucss_excluded_files'] ) && !empty($options['uucss_excluded_files']) ) {
            $files = explode( ',', $options['uucss_excluded_files'] );

            foreach ( $files as $excluded_file ) {

                if($this->str_contains( trim($excluded_file), '*' ) && $this->is_path_glob_matched($file, trim($excluded_file))){
                    return true;
                }else if ( $this->str_contains( $file, trim($excluded_file) ) ) {
                    return true;
                }

            }
        }

        return false;
    }

    public function is_path_glob_matched($path, $pattern, $ignoreCase = FALSE) {

        $expr = preg_replace_callback('/[\\\\^$.[\\]|()?*+{}\\-\\/]/', function($matches) {
            switch ($matches[0]) {
                case '*':
                    return '.*';
                case '?':
                    return '.';
                default:
                    return '\\'.$matches[0];
            }
        }, $pattern);

        $expr = '/'.$expr.'/';
        if ($ignoreCase) {
            $expr .= 'i';
        }

        return (bool) preg_match($expr, $path);

    }

    public static function remove_white_space($str){

        return preg_replace('/[\n\s+]/', '', $str);
    }

    public function schedule_cron($hook_name, $args){
        return wp_schedule_single_event( time() + 5, $hook_name, $args);
    }
}