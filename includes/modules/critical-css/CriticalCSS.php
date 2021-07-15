<?php


class CriticalCSS
{
    use RapidLoad_Utils;

    public $base;
    public $file_system;
    public $options = [];

    public $async = true;

    public static $base_dir;

    public function __construct()
    {
        $this->options = RapidLoad_Base::fetch_options();

        if(!isset($this->options['uucss_enable_cpcss'])){
            return;
        }

        $this->file_system = new RapidLoad_FileSystem();

        if( ! $this->initFileSystem() ){
            return;
        }

        add_action('rapidload/vanish', [ $this, 'vanish' ]);

        $this->cache_trigger_hooks();

        add_action('rapidload/job/handle', [$this, 'cache_cpcss'], 10, 2);

        add_action('rapidload/job/handle', [$this, 'enqueue_cpcss'], 20, 2);

        add_action('cpcss_async_queue', [$this, 'init_async_store'], 10, 2);

        add_filter('uucss/link', [$this, 'update_link']);

        new CriticalCSS_Queue();
    }

    public function cache_trigger_hooks() {
        add_action( 'save_post', [ $this, 'cache_on_actions' ], 110, 3 );
        add_action( 'untrash_post', [ $this, 'cache_on_actions' ], 10, 1 );
        add_action( 'wp_trash_post', [ $this, 'clear_on_actions' ], 10, 1 );
        add_action('wp_ajax_cpcss_purge_url', [$this, 'cpcss_purge_url']);
    }

    public function vanish() {

        CriticalCSS_DB::clear_data();

        if ( $this->file_system->exists( self::$base_dir ) ){
            $this->file_system->delete( self::$base_dir, true );
        }

    }

    public function refresh( $url, $args = [] ) {

        $job = null;

        if(isset($url)){

            $job = new RapidLoad_Job([
               'url' => $url
            ]);

        }

        $this->clear_cache( $job );
        $this->cache_cpcss( $job, $args );
    }

    public function clear_on_actions($post_ID)
    {
        $link = get_permalink($post_ID);

        if($link){

            $job = new RapidLoad_Job([
               'url' => $link
            ]);

            if($job->exist()){

                $this->clear_cache($job);

            }
        }
    }

    public function cache_on_actions($post_id, $post = null, $update = null)
    {
        $post = get_post($post_id);

        if($post->post_status == "publish") {

            $this->clear_on_actions( $post->ID );

            $job = new RapidLoad_Job([
                'url' => get_permalink( $post )
            ]);

            if(!$job->exist()){

                $job->save();

            }

            $this->cache_cpcss($job);

        }
    }

    function clear_cache($job = null, $args = []){

        if($job){

            $job_data = new RapidLoad_Job_Data($job, 'cpcss');

            if($job_data->exist()){

                $this->clear_files($job_data);
                $job_data->requeue();
                $job_data->save();

            }

        }else{

            CriticalCSS_DB::clear_data(isset($args['soft']));
            $this->clear_files();

        }

    }

    function clear_files($job_data = null){

        if($job_data){

            if(!empty($job_data->data)){

                $count = CriticalCSS_DB::data_used_elsewhere($job_data->id, $job_data->data);

                if($count == 0){

                    $this->file_system->delete( self::$base_dir . '/' .  $job_data->data);

                }
            }

        }else{

            $this->file_system->delete( self::$base_dir );

        }

    }

    function cpcss_purge_url()
    {

        if (isset($_REQUEST['url']) && !empty($_REQUEST['url'])) {

            $url = $_REQUEST['url'];

            if(!$this->is_url_allowed($url)){
                wp_send_json_error('url not allowed');
            }

            $job = new RapidLoad_Job([
                'url' => $this->transform_url($url)
            ]);

            if (!$job->exist()) {
                $job->save();
            }

            $this->cache_cpcss($job, ['immediate' => true]);

        }

        if (isset($_REQUEST['post_type'])){

            switch ($_REQUEST['post_type']) {

                case 'url':
                case 'post':
                case 'page':
                case 'site_map':
                {
                    break;
                }
                case 'warnings':
                {
                    CriticalCSS_DB::requeue_where(" WHERE status ='success' AND warnings IS NOT NULL ");
                    break;
                }
                case 'failed':
                {
                    CriticalCSS_DB::requeue_where(" WHERE status ='failed' ");
                    break;
                }
                case 'processing':
                {
                    CriticalCSS_DB::requeue_where(" WHERE status ='processing' ");
                    break;
                }
                default:
                {
                    CriticalCSS_DB::requeue_where();
                    break;
                }
            }
        }

        if ( isset( $_REQUEST['clear'] ) && boolval($_REQUEST['clear'] == 'true') ) {

            $this->clear_cache();

        }

        $this->cleanCacheFiles();

        wp_send_json_success('Successfully purged');
    }

    function update_link($link){

        if(isset($link['url'])){

            $job = new RapidLoad_Job([
               'url' => $link['url']
            ]);

            if($job->exist()){

                $job_data = new RapidLoad_Job_Data($job, 'cpcss');

                if($job_data->exist()){

                    $link['cpcss'] = (array) $job_data;

                }

            }
        }

        return $link;
    }

    function cache_cpcss($job, $args = []){

        if(!$job){
            return false;
        }

        if(!$this->is_url_allowed($job->url, $args)){
            return false;
        }

        $job_data = new RapidLoad_Job_Data($job, 'cpcss');

        if(!$job_data->exist()){

            $job_data->save();

        }

        if($job_data->status == 'failed' && $job_data->attempts > 2 && !isset($args['immediate'])){
            return false;
        }

        if(!in_array($job_data->status, ['success', 'waiting', 'processing']) || isset( $args['immediate'])){
            $job_data->requeue();
            $job_data->save();
        }

        $this->async = apply_filters('uucss/purge/async',true);

        if (! $this->async ) {

            $this->init_async_store($job_data, $args);

        }else if(isset( $args['immediate'] )){

            $spawned = false;

            $spawned = $this->schedule_cron('cpcss_async_queue', [
                'job_data' => $job_data,
                'args'     => $args
            ]);

            $job_data->status = 'processing';
            $job_data->save();

            if(!$spawned){
                $this->init_async_store($job_data, $args);
            }
        }

        return true;
    }

    function enqueue_cpcss($job, $args){

        $job_data = new RapidLoad_Job_Data($job, 'cpcss');

        if($job_data->exist() && $job_data->status == 'success'){

            new CriticalCSS_Enqueue($job_data);

        }

    }

    public function initFileSystem() {

        $cache_base = apply_filters('uucss/cache-base-dir', UUCSS_CACHE_CHILD_DIR);

        $cache_base_option = RapidLoad_Base::get_option('rapidload_cache_base', null);

        if(!isset($cache_base_option)){

            $cache_base_option = $cache_base;
            RapidLoad_Base::update_option('rapidload_cache_base', $cache_base_option);
        }

        $this->base = RapidLoad_ThirdParty::plugin_exists('autoptimize') ? $cache_base_option . 'cpcss' : $cache_base . 'cpcss';

        if ( ! $this->file_system ) {
            return false;
        }

        if ( ! $this->init_base_dir() ) {
            return false;
        }

        return true;
    }

    public function init_base_dir() {

        self::$base_dir = WP_CONTENT_DIR . $this->base;

        if ( $this->file_system->exists( self::$base_dir ) ) {
            return true;
        }

        // make dir if not exists
        $created = $this->file_system->mkdir( self::$base_dir );

        if (!$created || ! $this->file_system->is_writable( self::$base_dir ) || ! $this->file_system->is_readable( self::$base_dir ) ) {
            return false;
        }

        return true;
    }

    public function init_async_store($job_data, $args)
    {
        $store = new CriticalCSS_Store($job_data, $args);
        $store->purge_css();
    }

    public function cleanCacheFiles(){

        $data = CriticalCSS_DB::get_data_where(" WHERE status = 'success' ");

        $used_files = [];

        foreach ($data as $value){
            if(!empty($value->data)){
                array_push($used_files,$value->data);
            }
        }

        if($this->file_system->exists(CriticalCSS::$base_dir)){
            if ($handle = opendir(CriticalCSS::$base_dir)) {
                while (false !== ($file = readdir($handle))) {
                    if ('.' === $file) continue;
                    if ('..' === $file) continue;

                    if(!in_array($file, $used_files) && $this->file_system->exists(CriticalCSS::$base_dir . '/' . $file)){
                        $this->file_system->delete(CriticalCSS::$base_dir . '/' . $file);
                    }
                }
                closedir($handle);
            }
        }
    }
}