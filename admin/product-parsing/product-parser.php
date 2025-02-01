<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
use Stichoza\GoogleTranslate\GoogleTranslate;

trait Digi_product_parser {

    use Digi_Request;
    /**
     * Really long running process
     *
     * @return int
     */
    public function really_long_running_task() {

         $import_options = get_option('digi_import_options', true);
         $import_speed = $import_options['digi_scrap_speed'];
         if($import_speed == 'slow')
         {
             return sleep( 30 );
         }
         if($import_speed == 'normal')
         {
             return sleep( 15 );
         }
         if($import_speed == 'fast')
         {
             return sleep( 2 );
         }
    }


    /**
     * Get lorem
     *
     * @param string $link
     *
     * @return array
     */
    protected function scraping_product( $product_link ) {

        $product_excerpt_description = array(
            'product_excerpt' => '',
            'product_description' => '',
        );
        $product_properties = '';
        $product_gallery = '';

        $gt = new GoogleTranslate();
        $url_link = $product_link;
        $strPosLastPart  = strrpos($url_link, '/') + 1;
        $lastPart        = substr($url_link, $strPosLastPart);
        $encodedLastPart = rawurlencode($lastPart);
        $product_link       = str_replace($lastPart, $encodedLastPart, $url_link);

        $link_split = explode("/",$product_link);
        $l_part0 = $link_split[0];
        $l_part1 = $link_split[1];
        $l_part2 = $link_split[2]; //www.digikala.com
        $l_part3 = $link_split[3];
        $l_part4 = $link_split[4]; //dkp-1841797

        $product_code = $l_part4;
        $p_code = str_replace('dkp-', '', $product_code);

        $share_linkk = 'https://'.$l_part2.'/product/'.$l_part4;
        $share_link = DGSCRAPER_API_URL.$p_code.'/';

        /*custom code here*/

        $scrap_detailes = get_option('progress_status_details', true);
        $scrap_name = $scrap_detailes['scrap-name'];
        $current_scrap = get_option($scrap_name , true);
        if (array_key_exists("schedule_task_options",$current_scrap) && get_option('skip_import')) {
            delete_option('skip_import');
        }
        else
        {
          if(get_option('skip_import'))
          {
              $skip_option = get_option('skip_import', true);
              if($skip_option == $product_code)
              {
                  update_option('skip_import', 'skip_import_this_product');
                  $total_product_info = array(
                      'product_name' => '',
                      'product_brand' => '',
                      'product_brand_slug' => '',
                      'product_price' => '',
                      'product_cats' => '',
                      'product_tags' => '',
                      'product_english_title' => '',
                      'product_stock_status' => '',
                      'product_produce_status' => '',
                      'product_sale_price' => '',
                      'product_properties_tab' => '',
                      'product_important_properties' => '',
                      'variation_attr_color' => '',
                      'variation_attr_size' => '',
                  );
                  $product_total_information = array(
                      'product_public_info' => $total_product_info,
                      'product_excerpt_description' => '',
                      'product_properties' => '',
                      'product_gallery' => '',
                      'product_code' => $product_code,
                      'share_link' => $share_link,
                  );
                  return $product_total_information;
              }
              else
              {
                  update_option('skip_import', $product_code);
              }
          }
        }

        /*custom code here*/


        $streamContext = stream_context_create([
            'http' => [
                /*'method'=>"GET", test*/
                'header' => 'User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13',
            ],
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false
            ]
        ]);



        /*$product_html_content = file_get_contents($share_link, false, $streamContext);
        $product_html = new simple_html_dom();
        $product_html->load($product_html_content);*/
        $data = array('home' => get_home_url());
        $set_ip = DGSCRAPER_IP;
        $product_html = $this->digi_request($data, $share_link, $set_ip);

        $product_html = json_decode($product_html);
		
		if($product_html->data->product->is_inactive)
        {
          $total_product_info = array(
                      'product_name' => '',
                      'product_brand' => '',
                      'product_brand_slug' => '',
                      'product_price' => '',
                      'product_cats' => '',
                      'product_tags' => '',
                      'product_english_title' => '',
                      'product_stock_status' => '',
                      'product_produce_status' => '',
                      'product_sale_price' => '',
                      'product_properties_tab' => '',
                      'product_important_properties' => '',
                      'variation_attr_color' => '',
                      'variation_attr_size' => '',
                  );
                  $product_total_information = array(
                      'product_public_info' => $total_product_info,
                      'product_excerpt_description' => '',
                      'product_properties' => '',
                      'product_gallery' => '',
                      'product_code' => $product_code,
                      'share_link' => $share_link,
                  );
                  return $product_total_information;  
        }

        /*product name , product price , product brand , product variations*/

        $scrapt_config = get_option('digi_scraper_options', true);

        $product_info_parser = new Digi_product_info_parser();
        $product_public_info = $product_info_parser->get_product_info($product_html , $gt);

        /*update scraping progress status*/

        $product_name = $product_public_info['product_name'];
        $scrap_detailes = get_option('progress_status_details', true);
        $scrap_name = $scrap_detailes['scrap-name'];
        $num_of_imported = $scrap_detailes['number_of_products_imported'];
        $num_of_to_queue = $scrap_detailes['number_of_products_in_the_queue'];
        $rate_of_progress = absint(($num_of_imported / $num_of_to_queue) * 100);
        $scrap_detailes['product_name_being_imported'] = $product_name;


        if($rate_of_progress == 0)
        {
            $rate_of_progress = 1;
        }
        if($rate_of_progress >= 100)
        {
            $rate_of_progress = 100;
        }
        $scrap_detailes['rate_of_progress'] = $rate_of_progress;
        update_option('progress_status_details',$scrap_detailes);

        /*current task update*/

        $update_task = array();

        if(get_option($scrap_name))
        {
            $current_scrap = get_option($scrap_name , true);
            $update_task = $current_scrap;
            $current_scrap['rate-of-progress'] = $rate_of_progress;
			if($rate_of_progress >= 100)
            {
              $current_scrap['scrap-completed'] = 1;  
            }
            update_option($scrap_name , $current_scrap);
        }


        if (!array_key_exists("schedule_task_options",$update_task))
        {
            $product_description_parser = new Digi_product_description_parser();
            $product_excerpt_description = $product_description_parser->get_product_description($product_html);
        }
        /*product excerpt and description*/



        /*total product properties*/
        if(!array_key_exists("schedule_task_options",$update_task))
        {
            $product_properties = '';
            if($scrapt_config['get_product_properties'] != 0  && $scrapt_config['get_product_properties_tab_info'] == 0)
            {
                $product_properties_parser = new Digi_product_properties_parser();
                $product_properties = $product_properties_parser->get_product_properties($product_html,$gt);
            }
        }


        /*product image , product gallery image*/

        if(!array_key_exists("schedule_task_options",$update_task))
        {
            $product_gallery_parser = new Digi_product_gallery_parser();
            $product_gallery = $product_gallery_parser->get_product_gallery($product_html);
        }

        $product_total_information = array(
            'product_public_info' => $product_public_info,
            'product_excerpt_description' => $product_excerpt_description,
            'product_properties' => $product_properties,
            'product_gallery' => $product_gallery,
            'product_code' => $product_code,
            'share_link' => $share_linkk,
        );

        //$product_html->clear();
        unset($product_html);

        error_log('scraping method \n');
        return $product_total_information;



    }

    /**
     * Log
     *
     * @param string $message
     */
    public function log( $message ) {
        error_log( $message );
    }

}