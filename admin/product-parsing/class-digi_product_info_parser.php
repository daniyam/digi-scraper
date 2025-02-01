<?php


/**
 * The file that defines the core plugin class
 *
 * A class define for get product info like product name , product price , product brand , product variations attribute
 *
 * @link       http://www.beyranplugins.ir/
 * @since      1.0.0
 *
 * @package    digi-scraper/admin/
 * @subpackage product-parsing/
 */



class Digi_product_info_parser
{
    use Digi_static_functions;

    /**
     * Function to convert html object to Readable information include product name , product price , product brand , product variations attribute.
     *
     * @param $html_object
     * @param $gt google translate object
     * @return array
     */

    public function get_product_info($html_object , $gt)
    {
        $variation_attr_name = array();
        $variation_attr_slug = array();
        $variation_attr_color = array();
        $variation_attr_size = array();
        $product_price = 0;
        $product_sale_price = 0;
        $product_name = '';
        $product_brand = '';
        $attr_brand_translated = '';
        $bread_step = 0;
        $product_category_term = array();
        $product_tag_ids = array();
        $product_title_en = '';
        $product_stock_st = '';
        $product_produce_st = '';
        $product_properties_tab = '';
        $product_important_properties = '';
        $product_main_features = array();

        $scrap_detailes = get_option('progress_status_details', true);
        $scrap_name = $scrap_detailes['scrap-name'];
        $current_scrap = get_option($scrap_name , true);

        if(get_option('digi_import_options'))
        {
            $digi_import_options = get_option('digi_import_options', true);
        }
        $selected_categories = $digi_import_options['import_product_to_categories'];

        /*product name , product price , product brand , product variations*/

        $product_name = $html_object->data->product->title_fa;
        $before_title = $digi_import_options['before_title'];
        $after_title = $digi_import_options['after_title'];
        $dgs_scrape_title_regex_finds = $digi_import_options['dgs_scrape_title_regex_finds'];
        $dgs_scrape_title_regex_replaces = $digi_import_options['dgs_scrape_title_regex_replaces'];
        $dgs_combined_regex = array();
        if(!empty($dgs_scrape_title_regex_finds))
            $dgs_combined_regex = array_combine($dgs_scrape_title_regex_finds, $dgs_scrape_title_regex_replaces);
        if(!empty($dgs_scrape_title_regex_finds)) : foreach($dgs_combined_regex as $regex => $replace): if(!empty($regex)):

            $product_name = str_replace($regex, $replace, $product_name);

        endif; endforeach; endif;

        if(!empty($before_title))
        {
            $product_name = $before_title.$product_name;
        }
        if(!empty($after_title))
        {
            $product_name = $product_name.$after_title;
        }

        // brand
        if($html_object->data->product->brand)
        {
            $product_brand = $html_object->data->product->brand->title_fa;
        }
        else
        {
            $product_brand = 'متفرقه';
        }

        if (array_key_exists("schedule_task_options",$current_scrap))
        {
            $attr_brand_translated =  $product_brand;
        }
        else
        {
            if( $product_brand == 'متفرقه')
            {
                $attr_brand_translated = 'other';
            }
            else
            {
                $current_term = get_term_by('name', $product_brand, 'pa_brand');
                $term = term_exists( $product_brand, 'pa_brand' );
                if(! is_wp_error( $current_term ) && $term !== 0 && $term !== null)
                {
                    $attr_brand_translated = $current_term->slug;
                }
                else
                {
                    // $attr_brand_translated = $gt->setSource('fa')->setTarget('en')->translate($product_brand);
                    $attr_brand_translated = $this->converttoenglish($product_brand);
                }

            }
        }
        $attr_brand_translated = str_replace(' ','-',$attr_brand_translated);
        $attr_brand_translated = strtolower($attr_brand_translated);
        // price
        if($html_object->data->product->status != 'out_of_stock')
        {
            $product_price = ($html_object->data->product->default_variant->price->rrp_price)/10;
            $product_sale_price = ($html_object->data->product->default_variant->price->selling_price)/10;
            if(absint($product_price) == absint($product_sale_price))
            {
                $product_price = 0;
            }
        }
        /*color variations*/
        $total_attr_color = $html_object->data->product->colors;
        if(!empty($total_attr_color))
        {
            foreach ($total_attr_color as $attr_item)
            {

                $attr_name = $attr_item->title;
                array_push($variation_attr_name,$attr_name);
            }
            /*generate color attribute slug*/
            if(!empty($variation_attr_name))
            {
                if (array_key_exists("schedule_task_options",$current_scrap))
                {
                    foreach ($variation_attr_name as $attr_name_translated)
                    {
                        $attr_name_translated = str_replace(' ','-',$attr_name_translated);
                        $attr_name_translated = strtolower($attr_name_translated);
                        array_push($variation_attr_slug,$attr_name_translated);
                    }
                }
                else
                {
                    foreach ($variation_attr_name as $variation_attr_nam)
                    {
                        $current_term = get_term_by('name', $variation_attr_nam, 'pa_color');
                        $term = term_exists( $variation_attr_nam, 'pa_color' );
                        if(! is_wp_error( $current_term ) && $term !== 0 && $term !== null)
                        {
                            $term_slug = $current_term->slug;
                            if(empty($term_slug))
                            {
                                $term_slug = $variation_attr_nam;
                                $term_slug = str_replace(' ','-',$term_slug);
                            }
                            array_push($variation_attr_slug,$term_slug);
                        }
                        else
                        {
                            //$term_slug = $gt->setSource('fa')->setTarget('en')->translate($variation_attr_nam);
                            $term_slug = $this->converttoenglish($variation_attr_nam);
                            $term_slug = str_replace(' ','-',$term_slug);
                            $term_slug = strtolower($term_slug);
                            array_push($variation_attr_slug,$term_slug);
                        }
                    }
                }
            }

            $variation_attr_color = array_combine($variation_attr_slug,$variation_attr_name);
        }

        /*size varations*/
        $variation_attr_name = array();
        $variation_attr_slug = array();
        $total_attr_size = $html_object->data->product->variants;
        $total_att_sizes = array();
        if(!empty($total_attr_size))
        {
            foreach ($total_attr_size as $attr_item)
            {
                if(property_exists($attr_item, 'size'))
                {
                    $att_size = $attr_item->size;
                    if(!empty($att_size))
                    {
                        $attr_name = $att_size->title;
                        array_push($total_att_sizes,$attr_name);
                    }
                }
            }
            $total_att_sizes = array_unique($total_att_sizes);
            foreach ($total_att_sizes as $attr_item)
            {
                $attr_name = $attr_item;
                $attr_name = $this->convert2english($attr_name);
                array_push($variation_attr_name,$attr_name);
                $attr_name_translated = 'size_'.$this->converttoenglish($attr_name);
                array_push($variation_attr_slug,$attr_name_translated);
            }
            $variation_attr_size = array_combine($variation_attr_slug,$variation_attr_name);
        }

        /*get category term in breadcrumb*/
        $cat_term_names = array();
        $cat_term_names_translate = array();
        $breadcrumb = $html_object->data->product->breadcrumb;
        if(!empty($breadcrumb))
        {
            $breadcrumb_len = count($breadcrumb);
            foreach($breadcrumb as $cat_term)
            {
                if($bread_step == 0 || $bread_step >= $breadcrumb_len-1)
                {
                    $bread_step += 1;
                    continue;
                }
                else
                {
                    $cat_term_name = $cat_term->title;
                    array_push($cat_term_names, $cat_term_name);
                    $bread_step += 1;
                }
            }
        }
        if(!empty($cat_term_names))
        {
            if (array_key_exists("schedule_task_options",$current_scrap))
            {
                foreach ($cat_term_names as $cat_term_name)
                {
                    $cat_term_name_translated = str_replace(' ','-',$cat_term_name);
                    array_push($cat_term_names_translate,$cat_term_name_translated);
                }
            }
            else
            {
                if(empty($selected_categories))
                {
                    foreach ($cat_term_names as $cat_term_name)
                    {
                        $current_term = get_term_by('name', $cat_term_name, 'product_cat');
                        $term = term_exists( $cat_term_name, 'product_cat' );
                        if(! is_wp_error( $current_term ) && $term !== 0 && $term !== null)
                        {
                            $term_slug = $current_term->slug;
                            if(empty($term_slug))
                            {
                                $term_slug = $cat_term_name;
                                $term_slug = str_replace(' ','-',$term_slug);
                            }
                            array_push($cat_term_names_translate,$term_slug);
                        }
                        else
                        {
                            //$term_slug = $gt->setSource('fa')->setTarget('en')->translate($cat_term_name);
                            $term_slug = $this->converttoenglish($cat_term_name);
                            $term_slug = str_replace(' ','-',$term_slug);
                            $term_slug = strtolower($term_slug);
                            array_push($cat_term_names_translate,$term_slug);
                        }
                    }
                }
                else
                {
                    foreach ($cat_term_names as $cat_term_name)
                    {
                        $cat_term_name_translated = str_replace(' ','-',$cat_term_name);
                        array_push($cat_term_names_translate,$cat_term_name_translated);
                    }
                }

            }
            $product_category_term = array_combine($cat_term_names_translate,$cat_term_names);
        }

        /*product english title*/
        $product_title_en = $html_object->data->product->title_en;
        $product_title_en = rtrim($product_title_en);
        $product_title_en = ltrim($product_title_en);

        /*product stock check and stop produce check*/
        if($html_object->data->product->status == 'out_of_stock')
        {
            $product_stock_st = 'ناموجود';
        }

        /*get product properties tab content*/
        if($html_object->data->product->specifications)
        {
            $specifications = $html_object->data->product->specifications;
            if(!empty($specifications))
            {
                $product_properties_tab = '<div class="c-params"><article>';
                foreach($specifications as $specification)
                {
                    $head_title = $specification->title;
                    $product_properties_tab .= '<section><h3 class="c-params__title">'.$head_title.'</h3>';
                    $specification_key_value = $specification->attributes;
                    if(!empty($specification_key_value))
                    {
                        $product_properties_tab .= '<ul class="c-params__list">';
                        foreach ($specification_key_value as $attribute)
                        {
                            $title = $attribute->title;
                            if(property_exists($attribute, 'values'))
                            {

                                $values = $attribute->values;
                                $value_step = 0;
                                $product_properties_tab .= '<li><div class="c-params__list-key"><span class="block">'.$title.'</span></div>';
                                foreach ($values as $value)
                                {
                                    if($value_step == 0)
                                    {
                                        $product_properties_tab .= '<div class="c-params__list-value"><span class="block">'.$value.'</span></div></li>';
                                    }
                                    else
                                    {
                                        $product_properties_tab .= '<li><div class="c-params__list-key"></div><div class="c-params__list-value"><span class="block">'.$value.'</span></div></li>';
                                    }
                                    $value_step += 1;
                                }
                            }
                        }
                        $product_properties_tab .= '</ul>';

                    }
                    $product_properties_tab .= '</section>';
                }
                $product_properties_tab .= '</article></div>';
            }
        }

        /*get products important properties */
        if($html_object->data->product->review->attributes)
        {
            $attributes = $html_object->data->product->review->attributes;
            if(!empty($attributes))
            {
                $attributes_len = count($attributes);
                $attr_step = 0;
                $product_important_properties = '<div class="c-product__config-wrapper"> <div class="c-product__params js-is-expandable" data-collapse-count="2"> <ul data-title="ویژگی&zwnj;های کالا">';
                foreach ($attributes as $attribute)
                {
                    $attribute_title = $attribute->title;
                    $values = $attribute->values;
                    $values_len = count($values);
                    $attribute_value = '';
                    foreach ($values as $value)
                    {
                        if($values_len > 1)
                        {
                            $attribute_value .=  $value.',' ;
                        }
                        else
                        {
                            $attribute_value .=  $value;
                        }

                    }
                    if($attr_step > 4)
                    {
                        $product_important_properties .= '<li class="js-more-attrs c-product__params-more"><span>'.$attribute_title.' : </span><span>'.$attribute_value.'</span></li>';
                    }
                    else
                    {
                        $product_important_properties .= '<li><span>'.$attribute_title.' : </span><span>'.$attribute_value.'</span></li>';
                    }
                    $attr_step += 1;
                }
                if($attributes_len > 5)
                {
                    $product_important_properties .= '<li class="c-product__params-more-handler" data-sign="+"><button data-snt-event="dkProductPageClick" data-snt-params="{&quot;item&quot;:&quot;more-attributes&quot;,&quot;item_option&quot;:null}" class="btn-link-spoiler js-more-attr-button c-product__show-more-btn">موارد بیشتر</button></li>';
                }
                $product_important_properties .= '</ul></div></div>';
            }
        }

        $feature_step = 1;
        $product_main_features = array();
        if($html_object->data->product->review->attributes)
        {
            $attributes = $html_object->data->product->review->attributes;
            if(!empty($attributes))
            {
                $attributes_len = count($attributes);
                foreach ($attributes as $attribute)
                {
                    $attribute_title = $attribute->title;
                    $values = $attribute->values;
                    $values_len = count($values);
                    $attribute_value = '';
                    foreach ($values as $value)
                    {
                        if($values_len > 1)
                        {
                            $attribute_value .=  $value.',' ;
                        }
                        else
                        {
                            $attribute_value .=  $value;
                        }

                    }
                    $new_feature = array(
                        'title' => $attribute_title,
                        'value' => $attribute_value
                    );
                    $product_main_features[$feature_step] = $new_feature;
                    $feature_step++;
                }
            }
        }

        /*generate product tags*/
        $product_tag_terms = array();
        $tag_term_names_translate = array();
        $product_tags = $digi_import_options['dgs_product_tags'];
        if(!empty($product_tags))
        {
            $product_tags = explode('،' , $product_tags);

            if (array_key_exists("schedule_task_options",$current_scrap))
            {
                foreach ($product_tags as $product_tag) {
                    $tag_term_name_translated = str_replace(' ', '-', $product_tag);
                    array_push($tag_term_names_translate, $tag_term_name_translated);
                }
            }
            else
            {
                foreach ($product_tags as $product_tag)
                {
                    $current_term = get_term_by('name', $product_tag, 'product_tag');
                    $term = term_exists( $product_tag, 'product_tag' );
                    if(! is_wp_error( $current_term ) && $term !== 0 && $term !== null)
                    {
                        $term_slug = $current_term->slug;
                        if(empty($term_slug))
                        {
                            $term_slug = $product_tag;
                            $term_slug = str_replace(' ','-',$term_slug);
                        }
                        array_push($tag_term_names_translate,$term_slug);
                    }
                    else
                    {
                        //$term_slug = $gt->setSource('fa')->setTarget('en')->translate($product_tag);
                        $term_slug = $this->converttoenglish($product_tag);
                        $term_slug = str_replace(' ','-',$term_slug);
                        $term_slug = strtolower($term_slug);
                        array_push($tag_term_names_translate,$term_slug);
                    }
                }

            }
            $product_tag_terms = array_combine($tag_term_names_translate,$product_tags);


            foreach ($product_tag_terms as $product_tag_translated => $product_tag)
            {
                $term = term_exists($product_tag,'product_tag');
                if($term)
                {
                    $tag_term_id = $term['term_id'];
                    array_push($product_tag_ids,absint($tag_term_id));
                }
                else
                {
                    $in_term = wp_insert_term(
                        $product_tag, // the term
                        'product_tag', // the taxonomy
                        array(
                            'description'=> '',
                            'slug' => $product_tag_translated,
                        )
                    );
                    if($in_term)
                    {
                        $tag_term_id = $in_term['term_id'];
                        array_push($product_tag_ids,absint($tag_term_id));
                    }
                }
            }
        }

        if(empty($product_brand))
        {
            $product_brand = 'متفرقه';
            $attr_brand_translated = 'other';
        }

        if ($product_price != 0 && $product_sale_price == 0) {
            $product_sale_price = $product_price;
            $product_price = 0;
        }

        $total_product_info = array(
            'product_name' => $product_name,
            'product_brand' => $product_brand,
            'product_brand_slug' => $attr_brand_translated,
            'product_price' => $product_price,
            'product_cats' => $product_category_term,
            'product_tags' => $product_tag_ids,
            'product_english_title' => $product_title_en,
            'product_stock_status' => $product_stock_st,
            'product_produce_status' => $product_produce_st,
            'product_sale_price' => $product_sale_price,
            'product_properties_tab' => $product_properties_tab,
            'product_important_properties' => $product_important_properties,
            'product_main_features' => $product_main_features,
            'variation_attr_color' => $variation_attr_color,
            'variation_attr_size' => $variation_attr_size,
        );


        return $total_product_info;

    }
}