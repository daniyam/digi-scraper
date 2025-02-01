<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

trait Digi_list_parser
{
    use Digi_product_parser;
    use Digi_Request;

    /**
     * Function to get products link in archive product from other site.
     *
     * @param $archive_link
     * @return array of links
     */

    protected function get_archive_products_link($archive_link) {


        $url_link = $archive_link;

        //Encode Characters
       /* $strPosLastPart  = strrpos($url_link, '/') + 1;
        $lastPart        = substr($url_link, $strPosLastPart);
        $encodedLastPart = rawurlencode($lastPart);
        $url_link        = str_replace($lastPart, $encodedLastPart, $url_link);*/
        $product_list_item_link = $url_link;

        $link_split = explode("/",$product_list_item_link);
        $l_part0 = $link_split[0];
        $l_part1 = $link_split[1];
        $l_part2 = $link_split[2]; //www.digikala.com
        $l_part3 = $link_split[3];  //search
        $l_part4 = $link_split[4]; //category
        $l_part5 = '';
        if(array_key_exists(5, $link_split))
        {
            $l_part5 = $link_split[5];
        }
        //https://www.digikala.com/search/category-monitor/?brands%5B0%5D=18
        // https://www.digikala.com/search/category-mobile-phone/apple/
        // https://www.digikala.com/search/category-mobile-phone/product-list/
		//https://www.digikala.com/search/category-monitor/
        //https://www.digikala.com/search/category-laptop-accessories/
        //https://www.digikala.com/search/category-accessories-main/?brands%5B0%5D=1662
        //https://www.digikala.com/search/category-mobile-phone/product-list/?brands%5B0%5D=1662
        //https://www.digikala.com/search/category-mobile-phone/xiaomi/
        //https://www.digikala.com/brand/xiaomi/?categories%5B0%5D=11
         if($l_part3 == 'search')
        {
            if(array_key_exists(5, $link_split)) {
                $l_part5 = $link_split[5];
            }
            else
            {
                $l_part5 = '';
            }
		  $l_part4 = str_replace('category-', '', $l_part4);

          if($l_part5 == 'product-list')
          {
             //https://api.digikala.com/v1/categories/mobile-phone/search/?brands%5B0%5D=1662&page=1
              //https://api.digikala.com/v1/categories/mobile-phone/search/?page=1

              if(array_key_exists(6, $link_split))
              {
                  $l_part6 = $link_split[6];
                  $product_list_item_link = DGSCRAPER_CAT_API_URL.$l_part4.'/search/'.$l_part6;
              }
              else
              {
                  $product_list_item_link = DGSCRAPER_CAT_API_URL.$l_part4.'/search/?page=1';
              }
          }
          else
          {
              if(array_key_exists(5, $link_split) && (!empty($l_part5))) {
                  $l_part5_test = 'test'.$l_part5;
                  if(strpos($l_part5_test, '?'))
                  {
                      //https://api.digikala.com/v1/categories/monitor/search/?brands%5B0%5D=18&page=1
                      if(array_key_exists(5, $link_split))
                      {
                          $product_list_item_link = DGSCRAPER_CAT_API_URL.$l_part4.'/search/'.$l_part5;
                      }

                  }
                  else
                  {
                      //https://api.digikala.com/v1/categories/mobile-phone/brands/apple/search/?color_palettes%5B0%5D=8&page=1
                      //https://api.digikala.com/v1/categories/mobile-phone/brands/apple/search/?page=1
                      if(array_key_exists(6, $link_split))
                      {
                          $l_part6 = $link_split[6];
                          $product_list_item_link = DGSCRAPER_CAT_API_URL.$l_part4.'/brands/'.$l_part5.'/search/'.$l_part6;
                      }
                      else
                      {
                          $product_list_item_link = DGSCRAPER_CAT_API_URL.$l_part4.'/brands/'.$l_part5.'/search/?page=1';
                      }
                  }
              }
              else
              {
                  $product_list_item_link = DGSCRAPER_CAT_API_URL.$l_part4.'/search/?page=1';
              }
          }

        }
        if($l_part3 == 'brand')
        {
            //https://api.digikala.com/v1/brands/xiaomi/?categories%5B0%5D=11&page=1
            if(array_key_exists(5, $link_split)) {
                $product_list_item_link = DGSCRAPER_BRAND_API_URL.$l_part4.'/'.$l_part5;
            }
            else
            {
                $product_list_item_link = DGSCRAPER_BRAND_API_URL.$l_part4.'/?page=1';
            }

        }
        if($l_part3 == 'seller')
        {

            if(array_key_exists(5, $link_split)) {
                $product_list_item_link = DGSCRAPER_SELLER_API_URL.$l_part4.'/'.$l_part5;
            }
            else
            {
                $product_list_item_link = DGSCRAPER_SELLER_API_URL.$l_part4.'/?page=1';
            }
        }

		$stream_opts = [
            'http' => [
                /*'method'=>"GET" test,*/
                'header' => 'User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13',
            ],
    "ssl" => [
        "verify_peer"=>false,
        "verify_peer_name"=>false,
    ]
];

        /*$product_list_item_content = file_get_contents($product_list_item_link,
               false, stream_context_create($stream_opts));
        $product_list_item = new simple_html_dom();
        $product_list_item->load($product_list_item_content);*/

        $data = array('home' => get_home_url());
        $set_ip = DGSCRAPER_IP;
        $product_list_item = $this->digi_request($data, $product_list_item_link, $set_ip);

        $product_list_item = json_decode($product_list_item);

        $step_crawl = 0;

        $products_link = array();

        $product_list_item = $product_list_item->data->products;

        foreach ($product_list_item as $product_item) {
            $product_title = $product_item->title_fa;
            $product_link = $product_item->url->uri;
            $product_link = 'https://www.digikala.com' . $product_link;
            array_push($products_link,$product_link);
            $step_crawl++;
        }

        return $products_link;

    }


}


function digi_get_width_height($dimension) {
    if ($dimension) {
        $dimension = $dimension[0];
        $width = explode(';', $dimension)[0];
        $height = explode(';', $dimension)[1];
    } else {
        $dimension = null;
        if (is_singular('product'))
            $width = 1000;
        $height = 1200;
    }
    $width = 1000;
    $height = 1000;
    return array('width' => $width, 'height' => $height);
}


function digi_amp_url($url, $width, $height) {
    $size = get_post_meta(get_the_ID(), 'digi_image_dimension');
    if (!empty($size)) {
        $size = explode(';', $size[0]);
        $width = $size[0];
        $height = $size[1];
    }
    return array(0 => $url, 1 => $width, 2 => $height);
}
function digi_is_mpd_active() {
    return is_plugin_active('multisite-post-duplicator/mpd.php');
}