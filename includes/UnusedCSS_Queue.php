<?php


class UnusedCSS_Queue
{

    use UnusedCSS_Utils;
    public static $interval = 600;
    public static $job_count = 4;
    public $async = false;
    public static $post_types = [];

    function __construct()
    {
        $this->init();
    }

    function init(){

        $options = UnusedCSS_Autoptimize_Admin::fetch_options();

        if(isset($options['uucss_queue_interval'])){
            self::$interval = (int) $options['uucss_queue_interval'];
        }

        if(isset($options['uucss_jobs_per_queue'])){
            self::$job_count = (int) $options['uucss_jobs_per_queue'];
        }

        add_action('uucss_cron_queue', [$this, 'cache'], 2 , 1);

        add_filter( 'cron_schedules', [$this, 'uucss_process_queue_schedule'] );

        $uucss_cron = $this->cron_exist();

        if ( ! wp_next_scheduled( 'cron_uucss_process_queue' ) && !$uucss_cron) {
            self::log([
                'log' => 'cron scheduled',
                'type' => 'uucss-cron'
            ]);
            wp_schedule_event( time(), 'uucss_cron_interval', 'cron_uucss_process_queue');
        }else{

            $interval = isset($uucss_cron['interval']) ? $uucss_cron['interval'] : '';
            self::log([
                'log' => 'scheduled cron exist, running at ' . $interval . ' interval',
                'type' => 'uucss-cron'
            ]);

        }

        add_action( 'cron_uucss_process_queue', [$this ,'uucss_process_queue'] );

        add_action('wp_ajax_uucss_queue', [$this, 'queue_posts']);

        $this->async = apply_filters('uucss/queue/async',false);

        self::$post_types = apply_filters('uucss/queue/post_types',array(
            'post',
            'page',
            'product',
        ));
    }

    function cron_exist(){

        $cron_array = _get_cron_array();

        if(!isset($cron_array) || empty($cron_array)){
            return false;
        }

        $uucss_cron = array_column($cron_array, 'cron_uucss_process_queue');

        if(!isset($uucss_cron) || empty($uucss_cron)){
            return false;
        }

        $uucss_cron = array_shift($uucss_cron);

        if(!isset($uucss_cron) || empty($uucss_cron)){
            return false;
        }

        $uucss_cron = array_shift($uucss_cron);

        if(!isset($uucss_cron) || empty($uucss_cron)){
            return false;
        }

        return $uucss_cron;
    }

    function queue_posts(){

    	if(!isset($_REQUEST['post_type'])) {
		    wp_send_json_error('post type not found');
	    }

        $post_type = sanitize_text_field($_REQUEST['post_type']);

        $posts = null;

        if($post_type == 'all'){

            $posts = new WP_Query(array(
                'post_type'=> self::$post_types,
	            'posts_per_page' => -1
            ));

        }else if($post_type == 'current'){

            UnusedCSS_Settings::clear_links(true);
            wp_send_json_success('successfully links added to the queue');

        }else if($post_type == 'pending'){

            UnusedCSS_DB::requeue_pending_jobs();
            wp_send_json_success('successfully links added to the queue');

        }else{

            $posts = new WP_Query(array(
                'post_type'=> $post_type,
                'posts_per_page' => -1
            ));

        }

        global $uucss;

        if($posts->have_posts()){
            while ($posts->have_posts()){
                $posts->the_post();

                $url = $uucss->transform_url(get_the_permalink(get_the_ID()));

                if(!UnusedCSS_DB::link_exists($url) && $uucss->is_url_allowed($url)){
                    UnusedCSS_DB::add_link(array(
                        'url' => $url,
                        'status' => 'queued',
                    ));
                }

            }
        }

        wp_reset_query();

        wp_send_json_success('successfully links added to the queue');

    }

    static function get_post_types(){
        return self::$post_types;
    }

    function uucss_process_queue(){

        $links = UnusedCSS_DB::get_links_by_status(["'queued'"], self::$job_count);

        if(!empty($links)){

            foreach ($links as $link){

                UnusedCSS_DB::update_status('processing', $link->url);

                self::log([
                    'log' => 'status updated to processing',
                    'url' => $link->url,
                    'type' => 'uucss-cron'
                ]);

                if($this->async){

                    wp_schedule_single_event( time(), 'uucss_cron_queue', [
                        'url'      => $link->url,
                    ] );

                }else{

                    $this->cache($link->url);

                }

            }

        }

    }

    function cache($url){
        global $uucss;

        $post_id = url_to_postid($url);

        self::log([
            'log' => 'caching initiated by cron',
            'url' => $url,
            'type' => 'uucss-cron'
        ]);

        $uucss->init_async_store( $uucss->provider, $url, [
            'options' => $uucss->api_options($post_id)
        ] );
    }

    function uucss_process_queue_schedule($schedules){
        $schedules['uucss_cron_interval'] = array(
            'interval' => self::$interval,
            'display'  => __( 'uucss cron interval' ),
        );
        return $schedules;
    }
}