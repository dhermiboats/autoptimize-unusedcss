<?php


class CriticalCSS_Enqueue
{
    use RapidLoad_Utils;

    private $file_system;

    private $dom;
    private $inject;
    private $options;

    private $job_data;
    private $data;
    private $warnings;

    public function __construct($job_data)
    {
        $this->file_system = new RapidLoad_FileSystem();

        $this->job_data = $job_data;

        $this->data = $job_data->data;
        $this->warnings = $this->job_data->get_warnings();

        add_filter('uucss/enqueue/content/update', [$this, 'update_content'], 20);
    }

    function update_content($state){

        if(isset($state['dom'])){
            $this->dom = $state['dom'];
        }

        if(isset($state['inject'])){
            $this->inject = $state['inject'];
        }

        if(isset($state['options'])){
            $this->options = $state['options'];
        }

        if(!isset($this->job_data->id) || $this->job_data->status != 'success'){
            //$this->inject->rapidload = false;
            //$this->inject->successfully_injected = false;
            return [
                'dom' => $this->dom,
                'inject' => $this->inject,
                'options' => $this->options
            ];
        }

        if(!$this->file_system->exists(CriticalCSS::$base_dir . '/' . $this->data)) {
            $this->job_data->requeue();
            $this->job_data->save();
            //$this->inject->successfully_injected = false;
            return [
                'dom' => $this->dom,
                'inject' => $this->inject,
                'options' => $this->options
            ];
        }

        if($this->dom && $this->inject){

            $this->enqueue_cpcss();

            return [
                'dom' => $this->dom,
                'inject' => $this->inject,
                'options' => $this->options
            ];
        }

        return $state;

    }

    function update_noscript(){

        foreach ( $this->dom->find( 'link' ) as $key => $sheet ) {

            $parent = $sheet->parent();

            if(isset($parent) && $parent->tag == 'noscript' || !self::is_css($sheet)){
                continue;
            }

            if($sheet->tag != 'link'){
                continue;
            }

            if(is_numeric(strpos($sheet->outertext,'<style'))){
                continue;
            }

            $outer_text = $sheet->outertext;
            $sheet->onload = 'this.onload=null;this.media="' . $sheet->media . '";';
            $sheet->media = 'none';
            $sheet->outertext = '<noscript id="rapidload-noscript">' . $outer_text . '</noscript>' . $sheet->outertext;

        }
    }

    function enqueue_cpcss(){

            $critical_css_content = '';

            if($this->file_system->exists(CriticalCSS::$base_dir . '/' . $this->data)){
                $critical_css_content = $this->file_system->get_contents(CriticalCSS::$base_dir . '/' . $this->data );
            }

            if(isset($this->options['uucss_additional_css']) && !empty($this->options['uucss_additional_css'])){

                $critical_css_content .= $this->options['uucss_additional_css'];

            }

            if(empty($critical_css_content)){
                return;
            }

            $critical_css_content = '<style id="rapidload-critical-css">' . $critical_css_content . '</style>';

            if(isset($this->dom->find( 'title' )[0])){

                $title_content = $this->dom->find( 'title' )[0]->outertext;

                $this->dom->find( 'title' )[0]->outertext = $title_content . $critical_css_content;

                $this->update_noscript();

            }

            $this->job_data->mark_as_successful_hit();
            $this->job_data->save();

    }

    private static function is_css( $el ) {
        return $el->rel === 'stylesheet' || ($el->rel === 'preload' && $el->as === 'style');
    }
}