<?php

/**
 * The file that defines the core plugin class
 *
 * A class define for get product short description and description from html object
 *
 * @link       http://www.beyranplugins.ir/
 * @since      1.0.0
 *
 * @package    digi-scraper/admin/
 * @subpackage product-parsing/
 */

class Digi_product_description_parser
{

    /**
     * Function to parsing short description and description from html object.
     *
     * @param $html_object
     * @return array
     */
    public function get_product_description($html_object)
    {

        /*product excerpt and description*/
        $product_description = false;
        $short_description = false;
        if($html_object->data->product->review->description)
        {
            $short_description = $html_object->data->product->review->description;
            $product_description = '<h4>معرفی</h4>'.'<p>'.$short_description.'</p>';
        }
        if($html_object->data->product->expert_reviews->review_sections)
        {
            $review_sections = $html_object->data->product->expert_reviews->review_sections;
            foreach ($review_sections as $review_section)
            {
                $head_title = $review_section->title;
                $product_description .= '<h4>'.$head_title.'</h4>';
                $sections  = $review_section->sections;
                foreach ($sections as $section)
                {
                    if($section->template == 'text-image' || $section->template == 'image-text' || $section->template == 'text' )
                    {
                        $text = $section->text;
                        if(!empty($text) && strlen($text) > 90)
                        {
                            $product_description .= '<p>'.$text.'</p>';
                        }
                    }
                }
            }
        }

        if(get_option('digi_import_options'))
        {
            $digi_import_options = get_option('digi_import_options', true);
        }

        $dgs_scrape_content_regex_finds = $digi_import_options['dgs_scrape_content_regex_finds'];
        $dgs_scrape_content_regex_replaces = $digi_import_options['dgs_scrape_content_regex_replaces'];
        $dgs_combined_regex = array();
        if(!empty($dgs_scrape_content_regex_finds))
            $dgs_combined_regex = array_combine($dgs_scrape_content_regex_finds, $dgs_scrape_content_regex_replaces);
        if(!empty($dgs_scrape_content_regex_finds)) : foreach($dgs_combined_regex as $regex => $replace): if(!empty($regex)):

            $product_description = str_replace($regex, $replace, $product_description);
            $short_description = str_replace($regex, $replace, $short_description);

        endif; endforeach; endif;



        return array(
            'product_excerpt' => $short_description,
            'product_description' => $product_description,
        );

    }

}