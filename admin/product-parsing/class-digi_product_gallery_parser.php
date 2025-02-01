<?php

/**
 * The file that defines the core plugin class
 *
 * A class define for get product gallery image from html object
 *
 * @link       http://www.beyranplugins.ir/
 * @since      1.0.0
 *
 * @package    digi-scraper/admin/
 * @subpackage product-parsing/
 */
class Digi_product_gallery_parser
{

    /**
     * Function to parse object html for get product gallery image.
     *
     * @param $html_object
     * @return array
     */

    public function get_product_gallery($html_object)
    {
        /*product image*/
        $product_image = '';
        if($html_object->data->product->images->main)
        {
            $product_image = (string)$html_object->data->product->images->main->url[0];
            $position_question_mark = strpos($product_image,'?');
            $product_image = substr($product_image,0,$position_question_mark);
        }

        /*product image gallery*/

        if(!empty($product_image))
        {
            $product_image_gallery = array($product_image);
        }
        else
        {
            $product_image_gallery = array();
        }

		
		$scrap_config = get_option('digi_scraper_options' , true);

		if($scrap_config['get_product_gallery_image'] != 0)
        {

            if($html_object->data->product->images->list)
            {
                $g_items = $html_object->data->product->images->list;
                $count_image = 0;
                $max_scrap = $scrap_config['count_of_scrap_gallery_image'];
                foreach ($g_items as $g_item)
                {
                    if($max_scrap == 0 || $max_scrap <= $count_image)
                    {
                        break;
                    }
                    $img_src = (string)$g_item->url[0];
                    $position_question_mark = strpos($img_src,'?');
                    $img_src = substr($img_src,0,$position_question_mark);
                    array_push($product_image_gallery,$img_src);
                    $count_image++;
                }
            }
		}
        return $product_image_gallery;
    }

}