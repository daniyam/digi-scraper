<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

trait Digi_product_import
{
    //use Digi_product_parser;
    use Digi_static_functions;

    /**
     * insert product category
     *
     * @param array $category term
     * @return array ids
     */

    public function generate_product_cats($product_category_term)
    {
        $parent_term_id = 0;
        $category_ids = array();
        foreach ($product_category_term as $category_slug => $category_value)
        {

            if($parent_term_id == 0)
            {
                $term = term_exists($category_value,'product_cat');
                if($term)
                {
                    $parent_term_id = $term['term_id'];
                    array_push($category_ids,$parent_term_id);
                }
                else
                {
                    $in_term = wp_insert_term(
                        $category_value, // the term
                        'product_cat', // the taxonomy
                        array(
                            'description'=> '',
                            'slug' => $category_slug,
                            //'parent'=> $parent_term['term_id']  // get numeric term id
                        )
                    );
                    if($in_term)
                    {
                        $parent_term_id = $in_term['term_id'];
                        array_push($category_ids,$parent_term_id);
                    }
                }
            }
            else
            {
                $term = term_exists($category_value,'product_cat');
                if($term)
                {
                    $parent_term_id = $term['term_id'];
                    array_push($category_ids,$parent_term_id);
                }
                else
                {
                    $in_term = wp_insert_term(
                        $category_value, // the term
                        'product_cat', // the taxonomy
                        array(
                            'description'=> '',
                            'slug' => $category_slug,
                            'parent'=> $parent_term_id  // get numeric term id
                        )
                    );
                    if($in_term)
                    {
                        $parent_term_id = $in_term['term_id'];
                        array_push($category_ids,$parent_term_id);
                    }
                }
            }
        }
        return $category_ids;

    }


    /**
     * insert_product
     *
     * @param array $total_product_info
     * @return message
     */
    public function insert_product($total_product_info)
    {
        /*add product properties tab information*/

        if (get_option('digi_import_options')) {
            $digi_import_options = get_option('digi_import_options', true);
            $show_gallery_image_directly = $digi_import_options['show_gallery_image_directly'];
            $show_featured_image_directly = $digi_import_options['show_featured_image_directly'];
            $selected_categories = $digi_import_options['import_product_to_categories'];
            $product_status = $digi_import_options['product_imported_status'];
            $enable_marketing_link = $digi_import_options['create_marketing_link'];
            $marketing_link_const = $digi_import_options['marketing_link_const'];
        }

        $variation_attr_color = $total_product_info['product_public_info']['variation_attr_color'];
        $variation_attr_size = $total_product_info['product_public_info']['variation_attr_size'];
        $product_name = $total_product_info['product_public_info']['product_name'];
        $product_price = $total_product_info['product_public_info']['product_price'];
        $product_sale_price = $total_product_info['product_public_info']['product_sale_price'];
        $product_brand = $total_product_info['product_public_info']['product_brand'];
        $attr_brand_translated = $total_product_info['product_public_info']['product_brand_slug'];
        $product_catgories = $total_product_info['product_public_info']['product_cats'];
        $product_stock_status = $total_product_info['product_public_info']['product_stock_status'];
        $product_produce_status = $total_product_info['product_public_info']['product_produce_status'];
        $product_english_title = $total_product_info['product_public_info']['product_english_title'];
        $product_properties_tab = $total_product_info['product_public_info']['product_properties_tab'];
        $product_description = $total_product_info['product_excerpt_description']['product_description'];
        $short_description = $total_product_info['product_excerpt_description']['product_excerpt'];
        $product_image_gallery = $total_product_info['product_gallery'];
        $product_properties = $total_product_info['product_properties'];
        $product_code = $total_product_info['product_code'];
        $share_link = $total_product_info['share_link'];

        $scrap_config = get_option('digi_scraper_options', true);

        $var_color_count = count($variation_attr_color);
        $var_size_count = count($variation_attr_size);

        if ($this->get_product_by_sku($product_code, $total_product_info)) {
            $scrap_detailes = get_option('progress_status_details', true);
            $scrap_name = $scrap_detailes['scrap-name'];
            $num_of_imported = $scrap_detailes['number_of_products_imported'];
            $num_of_imported = $num_of_imported + 1;
            $scrap_detailes['number_of_products_imported'] = $num_of_imported;
            update_option('progress_status_details', $scrap_detailes);

            /*current task update*/

            if (get_option($scrap_name)) {
                $current_scrap = get_option($scrap_name, true);
                $current_scrap['number-of-product-imported'] = $num_of_imported;
                update_option($scrap_name, $current_scrap);
            }

            return 'The product already exists';
        }

        if ($var_color_count > 0 || $var_size_count > 0) {
            $objProduct = new WC_Product_Variable();
        } else {
            $objProduct = new WC_Product();
        }

        $objProduct->set_name($product_name);
        $objProduct->set_status($product_status);  // can be publish,draft or any wordpress post status
        $objProduct->set_catalog_visibility('visible'); // add the product visibility status


        if (empty($selected_categories)) {
            /*generate dynamicly product category*/
            $category_ids = $this->generate_product_cats($product_catgories);
            if (!empty($category_ids)) {
                $objProduct->set_category_ids($category_ids); // select product category
            }
        } else {
            $objProduct->set_category_ids($selected_categories); // select product category
        }


        /*set description and short description*/
        if ($product_description) {
            if ($scrap_config['get_product_excerpt'] != 0) {
                $objProduct->set_short_description($short_description);
            }
            if ($scrap_config['get_product_description'] != 0) {
                $objProduct->set_description($product_description);
            }

        }


        //$objProduct->set_sku("product-sku-vari"); //can be blank in case you don't have sku, but You can't add duplicate sku's

        if ($scrap_config['get_product_price'] != 0)
        {

            if ($product_price == 0 && $product_sale_price == 0) {
            }
            else {
                if ($product_price != 0) {
                    $objProduct->set_price($product_price); // set product price
                    $objProduct->set_regular_price($product_price); // set product regular price
                    $objProduct->set_sale_price($product_sale_price); // set product sale price
                } else {
                    $objProduct->set_price($product_sale_price); // set product price
                    $objProduct->set_regular_price($product_sale_price); // set product regular price
                }
            }
        }

        if($product_stock_status == 'ناموجود')
        {
            $objProduct->set_manage_stock(true); // true or false
            //$objProduct->set_stock_quantity(10);
            //$objProduct->set_stock_status('instock'); // in stock or out of stock value
            $objProduct->set_stock_status('outofstock'); // in stock or out of stock value
        }


        $objProduct->set_backorders('no');
        $objProduct->set_reviews_allowed(true);
        $objProduct->set_sold_individually(false);

        if($scrap_config['get_product_code'] != 0)
        {
            $objProduct->set_sku($product_code);
        }
        /*if(!empty($product_english_title))
        {
            $objProduct->set_slug($product_english_title);
        }*/

        // above function uploadMedia, I have written which takes an image url as an argument and upload image to wordpress and returns the media id, later we will use this id to assign the image to product.
        $productImagesIDs = array(); // define an array to store the media ids.
        $images = $product_image_gallery; // images url array of product
        $img_upload_step = 0;


        if($show_gallery_image_directly == 0 || $show_featured_image_directly == 0)
        {
            foreach($images as $image_m){
                /*start watermark remover process*/
                if($scrap_config['get_product_index_image'] == 0 && $img_upload_step == 0 )
                {
                    $img_upload_step++;
                    continue;
                }
                if($show_featured_image_directly != 0 && $img_upload_step == 0 )
                {
                    $img_upload_step++;
                    continue;
                }
                if($show_gallery_image_directly != 0 && $img_upload_step > 0 )
                {
                    break;
                }
                $media_path = wp_upload_dir();
                $media_url = $media_path['url'];
                if (!file_exists($media_path['path'].'/xgen_pic')) {
                    mkdir($media_path['path'].'/xgen_pic', 0777, true);
                }

                $url_pic = $image_m;
                // Use basename() function to return the base name of file
                $file_name = basename($url_pic);

                if(extension_loaded('imagick') && $scrap_config['remove_watermark_image'] != 0 && $img_upload_step != 0 )
                {

                    // Initialize a file URL to the variable
                    $url_pic = $image_m;

                    // Use basename() function to return the base name of file
                    $file_name = basename($url_pic);

                    // Use file_get_contents() function to get the file
                    // from url and use file_put_contents() function to
                    // save the file by using base name

                    $streamContext = stream_context_create([
                        'ssl' => [
                            'verify_peer'      => false,
                            'verify_peer_name' => false
                        ]
                    ]);

                    $source = file_get_contents($url_pic, false, $streamContext);
                    if($source !== false AND !empty($source)) {

                        /*if(file_put_contents($media_path['path'].'/xgen_pic/'.$file_name,file_get_contents($url_pic))) {
                        //echo "File downloaded successfully";
                        }*/

                        if(file_put_contents($media_path['path'].'/xgen_pic/'.$file_name,$source)) {
                            //echo "File downloaded successfully";
                        }

                        $imagick = new \Imagick($media_path['path'].'/xgen_pic/'.$file_name);
                        $image1 = new Imagick($media_path['path'].'/xgen_pic/'.$file_name);
                        $image2 = new Imagick($media_path['path'].'/xgen_pic/'.$file_name);
                        $image = new Imagick($media_path['path'].'/xgen_pic/'.$file_name);

                        $x_axis = 20;
                        $y_axis = 0;
                        $x_middle_postion = 0;
                        $y_middle_postion = 0;
                        $x_first_postion = 0;
                        $y_first_postion = 0;
                        $x_last_postion = 0;
                        $y_last_postion = 0;
                        $first_position_composite = 0;
                        $last_position_composite = 0;
                        $imageprops = $imagick->getImageGeometry();
                        $width_img = $imageprops['width'];
                        if($width_img > 1500)
                        {
                            $length_of_search = 240;
                        }
                        else
                        {
                            $length_of_search = 170;
                        }

                        for($y_axis=35; $y_axis < 65; $y_axis += 10)
                        {
                            for($x_axis=40; $x_axis < $length_of_search; $x_axis++)
                            {

                                $fill_color = $imagick->getImagePixelColor($x_axis, $y_axis);
                                $colors = $fill_color->getColor();
                                $red_value = $colors['r'];
                                $green_value = $colors['g'];
                                $blue_value = $colors['b'];
                                $r_between_g = $red_value - $green_value;
                                $r_between_b = $red_value - $blue_value;
                                /*if($red_value >= 140 && $red_value <= 255 && $green_value >= 50 && $green_value <= 135 && $blue_value >= 50 && $blue_value <= 135 )*/
                                if($r_between_g >= 70 && $r_between_b >= 70 )
                                {
                                    $x_middle_postion = $x_axis;
                                    $y_middle_postion = $y_axis;


                                    break;

                                }
                            }
                            if($y_middle_postion != 0)
                            {
                                break;
                            }
                        }

                        if ($y_middle_postion == 0)
                        {
                            for($y_axis=25; $y_axis < 30; $y_axis += 10)
                            {
                                for($x_axis=40; $x_axis < $length_of_search; $x_axis++)
                                {

                                    $fill_color = $imagick->getImagePixelColor($x_axis, $y_axis);
                                    $colors = $fill_color->getColor();
                                    $red_value = $colors['r'];
                                    $green_value = $colors['g'];
                                    $blue_value = $colors['b'];
                                    $r_between_g = $red_value - $green_value;
                                    $r_between_b = $red_value - $blue_value;
                                    /*if($red_value >= 140 && $red_value <= 255 && $green_value >= 50 && $green_value <= 135 && $blue_value >= 50 && $blue_value <= 135 )*/
                                    if($r_between_g >= 70 && $r_between_b >= 70 )
                                    {
                                        $x_middle_postion = $x_axis;
                                        $y_middle_postion = $y_axis;


                                        break;

                                    }
                                }
                                if($y_middle_postion != 0)
                                {
                                    break;
                                }
                            }
                        }

                        if($y_middle_postion > 0)
                        {
                            $x_m_final = $x_middle_postion + 5;
                            $y_m_final = 0;
                            $x_m_position = $x_middle_postion;
                            $y_m_position = $y_middle_postion;
                            for($x_m_position; $x_m_position < $x_m_final; $x_m_position++ )
                            {
                                for($y_m_position; $y_m_position > $y_m_final; $y_m_position--)
                                {
                                    $fill_color = $imagick->getImagePixelColor($x_m_position, $y_m_position);
                                    $colors = $fill_color->getColor();
                                    $red_value = $colors['r'];
                                    $green_value = $colors['g'];
                                    $blue_value = $colors['b'];
                                    $r_between_g = $red_value - $green_value;
                                    $r_between_b = $red_value - $blue_value;
                                    if(!($r_between_g >= 60 && $r_between_b >= 60 ))
                                    {
                                        $x_first_postion = $x_m_position;
                                        $y_first_postion = $y_m_position;
                                        break;
                                    }
                                }
                                if($y_first_postion != 0)
                                {
                                    break;
                                }
                            }


                            $x_f_final = $x_middle_postion + 5;
                            $y_f_final = $y_middle_postion + 110;
                            $x_f_position = $x_middle_postion;
                            $y_f_position = $y_middle_postion;
                            for($x_f_position; $x_f_position < $x_f_final; $x_f_position++ )
                            {
                                for($y_f_position; $y_f_position < $y_f_final; $y_f_position++)
                                {
                                    $fill_color = $imagick->getImagePixelColor($x_f_position, $y_f_position);
                                    $colors = $fill_color->getColor();
                                    $red_value = $colors['r'];
                                    $green_value = $colors['g'];
                                    $blue_value = $colors['b'];
                                    $r_between_g = $red_value - $green_value;
                                    $r_between_b = $red_value - $blue_value;
                                    if(!($r_between_g >= 60 && $r_between_b >= 60 ))
                                    {
                                        $y_last_postion = $y_f_position;
                                        break;
                                    }
                                }
                                if($y_last_postion != 0)
                                {
                                    break;
                                }
                            }



                            $crop_height_top = 0;
                            $crop_height_bottom = 0;
                            $water_mark_height = $y_last_postion - $y_first_postion;
                            $average_crop_height = absint($water_mark_height/2)+1;
                            if($y_first_postion < $average_crop_height)
                            {
                                $crop_height_top =  $y_first_postion - 1;
                            }
                            else
                            {
                                $crop_height_top =  $average_crop_height;
                                $crop_height_bottom =  $average_crop_height;
                            }
                            if($crop_height_top < $average_crop_height)
                            {
                                $crop_height_bottom = $water_mark_height - $crop_height_top + 1 ;
                            }

                            $first_position_composite = $y_first_postion - 1;
                            $last_position_composite = $y_first_postion + $crop_height_top - 1;

                            if(15 <= $water_mark_height && $water_mark_height < 40)
                            {
                                $crop_width = 195;
                                $begin_of_bottom_crop = $y_last_postion + 12;
                                $crop_height_bottom = $crop_height_bottom + 12;
                            }
                            elseif ($water_mark_height < 15)
                            {
                                $crop_width = 160;
                                $begin_of_bottom_crop = $y_last_postion + 7;
                                $crop_height_bottom = $crop_height_bottom + 7;
                            }
                            else
                            {
                                $crop_width = 500;
                                $begin_of_bottom_crop = $y_last_postion + 22;
                                $crop_height_bottom = $crop_height_bottom + 22;
                            }

                            $image1->cropImage($crop_width, $crop_height_top, 0, 0);
                            $image1->writeImage( $media_path['path'].'/xgen_pic/'.'logo-top.jpg' );
                            $image2->cropImage($crop_width, $crop_height_bottom, 0, $begin_of_bottom_crop);
                            $image2->writeImage( $media_path['path'].'/xgen_pic/'.'logo-bottom.jpg' );

                            $logo_top = new Imagick($media_path['path'].'/xgen_pic/'.'logo-top.jpg');
                            $logo_bottom = new Imagick($media_path['path'].'/xgen_pic/'.'logo-bottom.jpg');

                            $image->compositeImage($logo_top,imagick::COMPOSITE_OVER , 0, $first_position_composite);
                            $image->compositeImage($logo_bottom, imagick::COMPOSITE_OVER , 0, $last_position_composite);
                            $image->writeImage($media_path['path'].'/xgen_pic/'.'final-step.jpg');

                            /*start code for watermark copy right*/
                            /* Create some objects*/
                            $image1 = new Imagick($media_path['path'].'/xgen_pic/'.'final-step.jpg');
                            $image2 = new Imagick($media_path['path'].'/xgen_pic/'.'final-step.jpg');
                            $image = new Imagick($media_path['path'].'/xgen_pic/'.'final-step.jpg');

                            $image1->cropImage(300, 9, 0, 641);
                            $image1->writeImage( $media_path['path'].'/xgen_pic/'.'copy-top.jpg' );
                            $image2->cropImage(300, 9, 0, 669);
                            $image2->writeImage( $media_path['path'].'/xgen_pic/'.'copy-bottom.jpg' );

                            $current_memory = memory_get_usage( true );
                            $copy_top = new Imagick($media_path['path'].'/xgen_pic/'.'copy-top.jpg');
                            $copy_bottom = new Imagick($media_path['path'].'/xgen_pic/'.'copy-bottom.jpg');

                            $image->compositeImage($copy_top, imagick::COMPOSITE_OVER , 0, 650);
                            $image->compositeImage($copy_bottom, imagick::COMPOSITE_OVER , 0, 659);
                            unlink($media_path['path'].'/xgen_pic/'.$file_name );
                            $image->writeImage($media_path['path'].'/xgen_pic/'.$file_name);

                            unlink($media_path['path'].'/xgen_pic/'.'logo-top.jpg' );
                            unlink($media_path['path'].'/xgen_pic/'.'logo-bottom.jpg' );
                            unlink($media_path['path'].'/xgen_pic/'.'copy-top.jpg' );
                            unlink($media_path['path'].'/xgen_pic/'.'copy-bottom.jpg' );
                            unlink($media_path['path'].'/xgen_pic/'.'final-step.jpg' );
                            $image_m = $media_path['path'].'/xgen_pic/'.$file_name;
                        }

                        $this->digi_add_watermark($media_path['path'].'/xgen_pic/'.$file_name,$digi_import_options);


                        $url_pic = $media_url.'/xgen_pic/'.$file_name;
                    }
                    else
                    {

                    }


                }


                $mediaID = $this->uploadMedia($url_pic);

                $product_name = ltrim($product_name);
                $product_name = rtrim($product_name);

                global $wpdb;
                $image_alt_text = $product_name;
                $image_title_text = $product_name;
                $image_excerpt_text = $product_name;
                $image_content_text = $product_name;
                $image_post_name =

                    update_post_meta($mediaID, '_wp_attachment_image_alt',$image_alt_text);

                $new_values = array(
                    'post_title' => $image_title_text,
                    'post_name' =>  $file_name,
                    'post_content' => $image_content_text,
                    'post_excerpt' => $image_excerpt_text,
                );
                $where = array( 'ID' => $mediaID );
                $wpdb->update( $wpdb->posts, $new_values, $where );
                // calling the uploadMedia function and passing image url to get the uploaded media id
                if($mediaID) $productImagesIDs[] = $mediaID; // storing media ids in a array
                $img_upload_step++;
            }
        }


        if($productImagesIDs){
            if($scrap_config['get_product_index_image'] != 0 && $show_featured_image_directly == 0)
            {
                $objProduct->set_image_id($productImagesIDs[0]); // set the first image as primary image of the product
            }

            //in case we have more than 1 image, then add them to product gallery.
            if(count($productImagesIDs) > 1){
                if($scrap_config['get_product_gallery_image'] != 0)
                {
                    $length = count($productImagesIDs);
                    $productImagesIDs = array_splice($productImagesIDs, 2, $length);
                    $objProduct->set_gallery_image_ids($productImagesIDs);
                }
            }
        }

        if( Zhaket_Guard_DGS::is_activated() === true )
        {
            $product_id = $objProduct->save(); // it will save the product and return the generated product id
        }
        else
        {
            return 1;
        }

        /*load picture directly from*/
        $img_upload_step = 0;
        $productImagesIDs = array(); // define an array to store the media ids.
        $images = $product_image_gallery; // images url array of product

        if($show_gallery_image_directly != 0 || $show_featured_image_directly != 0)
        {
            foreach($images as $image)
            {
                if($img_upload_step == 0 && $scrap_config['get_product_index_image'] != 0 && $show_featured_image_directly != 0 )
                {
                    $img_upload_step++;

                    // Use basename() function to return the base name of file
                    $file_name = basename($image);
                    $product_name = ltrim($product_name);
                    $product_name = rtrim($product_name);

                    // insert new attachment
                    $new_attachment = array(
                        'post_author'   => 8888,
                        'post_title'    => $product_name,
                        'post_name' => $file_name,
                        'post_content'  => $product_name,
                        'post_excerpt' => $product_name,
                        'post_status'   => 'inherit',
                        'post_type'   => 'attachment',
                        'post_parent'   => $product_id,
                        'post_mime_type'  => 'image/jpeg',
                        'guid'   => $image,
                    );
                    $attach_file = ';'.$image;

                    // Insert the post into the database
                    $new_attachment_id = wp_insert_post( $new_attachment );
                    update_post_meta($new_attachment_id,'_wp_attachment_image_alt',$product_name);
                    update_post_meta($new_attachment_id,'_wp_attached_file',$attach_file);
                    update_post_meta($product_id,'_thumbnail_id',$new_attachment_id);
                    if($new_attachment_id) $productImagesIDs[] =  $new_attachment_id; // storing media ids in a array



                    continue;
                }

                if ($img_upload_step > 0 && $scrap_config['get_product_gallery_image'] != 0 && $show_gallery_image_directly != 0 )
                {
                    $file_name = basename($image);
                    $product_name = ltrim($product_name);
                    $product_name = rtrim($product_name);

                    // insert new attachment
                    $new_attachment = array(
                        'post_author'   => 8888,
                        'post_title'    => $product_name,
                        'post_name' => $file_name,
                        'post_content'  => $product_name,
                        'post_excerpt' => $product_name,
                        'post_status'   => 'inherit',
                        'post_type'   => 'attachment',
                        'post_parent'   => $product_id,
                        'post_mime_type'  => 'image/jpeg',
                        'guid'   => $image,
                    );
                    $attach_file = ';'.$image;

                    // Insert the post into the database
                    $new_attachment_id = wp_insert_post( $new_attachment );
                    update_post_meta($new_attachment_id,'_wp_attachment_image_alt',$product_name);
                    update_post_meta($new_attachment_id,'_wp_attached_file',$attach_file);
                    if($new_attachment_id) $productImagesIDs[] =  $new_attachment_id; // storing media ids in a array
                }
                if($show_gallery_image_directly == 0)
                {
                    break;
                }

                $img_upload_step++;
            }
        }

        if($productImagesIDs){
            //in case we have more than 1 image, then add them to product gallery.
            if(count($productImagesIDs) > 1){
                if($scrap_config['get_product_gallery_image'] != 0)
                {
                    $length = count($productImagesIDs);
                    $productImagesIDs = array_splice($productImagesIDs, 1, $length);
                    $gallery_images = implode(",",$productImagesIDs);
                    update_post_meta($product_id,'_product_image_gallery',$gallery_images);
                }
            }
        }


        /*create marketing link*/

        if($enable_marketing_link == 1)
        {
            $base64_link = base64_encode($share_link);
            $url_link = $marketing_link_const.$base64_link;
            update_post_meta($product_id, 'digi_market_link', $url_link);
        }

        update_post_meta($product_id, 'digi_product_code', $product_code);

        /*add product properties while creating product*/

        if($product_produce_status == 'توقف تولید')
        {
            update_post_meta($product_id, 'product_stop_production', 1);
        }
        if(!empty($product_english_title))
        {
            update_post_meta($product_id, 'product_english_name', $product_english_title);
        }
        $taxonomy_brand_exist = taxonomy_exists( 'yith_product_brand' );
        if($taxonomy_brand_exist)
        {
            $brand_term_id = 0;
            $term = term_exists($product_brand,'yith_product_brand');
            if($term)
            {
                $brand_term_id = absint($term['term_id']);
            }
            else
            {
                $in_term = wp_insert_term(
                    $product_brand, // the term
                    'yith_product_brand', // the taxonomy
                    array(
                        'description'=> '',
                        'slug' => $attr_brand_translated,
                        //'parent'=> $parent_term['term_id']  // get numeric term id
                    )
                );
                if($in_term)
                {
                    $brand_term_id = $in_term['term_id'];
                }
            }
            if(!empty($brand_term_id))
            {
                wp_set_object_terms( $product_id, $brand_term_id, 'yith_product_brand' );
            }
        }

        $attributes = array();

        $att_position = 1;

        if(!empty($product_properties))
        {
            foreach($product_properties as $product_property)
            {
                $root_product_property = $product_property;
                $attribute_name = array_slice($product_property , 0,1);
                $attribute_value = array_slice($root_product_property,1);
                foreach ($attribute_value as $key => $value)
                {
                    $key_length = strlen($key)-3;
                    $value_length = strlen($value)-3;
                    $att_slug = substr($key,0,$key_length);
                    $att_value = substr($value,0,$value_length);
                    $attribute = array($att_slug => $att_value);
                }



                $new_attribute = array("name"=>$attribute_name,"options"=>$attribute,"position"=>$att_position,"visible"=>1,"variation"=>0);
                array_push($attributes,$new_attribute);
                $att_position++;
            }
        }

        $att_position++;
        /*add brand to product properties*/

        $new_attribute = array("name"=>array('brand' => 'برند'),"options"=>array($attr_brand_translated => $product_brand),"position"=>$att_position,"visible"=>1,"variation"=>0);

        array_push($attributes,$new_attribute);


        /*add attributes and create variations*/
        $att_position++;

        if($var_color_count > 0)
        {
            $attribute_color = array("name"=>array("color" => "رنگ"),"options"=>$variation_attr_color,"position"=>$att_position,"visible"=>1,"variation"=>1);
            array_push($attributes,$attribute_color);
        }

        if($var_size_count > 0)
        {
            $attribute_size = array("name"=>array("size" => "سایز"),"options"=>$variation_attr_size,"position"=>$att_position,"visible"=>1,"variation"=>1);
            array_push($attributes,$attribute_size);
        }

        if($attributes){
            $productAttributes=array();
            foreach($attributes as $attribute){

                foreach ($attribute["name"] as $attribute_slug => $attribute_name)
                {
                    $attr_slug = wc_sanitize_taxonomy_name(stripslashes($attribute_slug));
                    $attr_name = stripslashes($attribute_name);
                }
                // remove any unwanted chars and return the valid string for taxonomy name

                $this->create_product_attribute($attr_slug , $attr_name);

                $taxonomy_attribute_label = $attr_name;

                //create_product_attribute($attr);
                $attr = 'pa_'.$attr_slug; // woocommerce prepend pa_ to each attribute name

                if(!taxonomy_exists($attr))
                {
                    $this->register_taxonomy_attribute($attr, $taxonomy_attribute_label);
                }

                if($attribute["options"]){
                    foreach($attribute["options"] as $slug => $label){
                        if(!term_exists($slug,$attr))
                        {
                            wp_insert_term($label,$attr,array('slug' => $slug));
                        }
                        wp_set_object_terms($product_id,$slug,$attr,true); // save the possible option value for the attribute which will be used for variation later
                    }
                }
                $productAttributes[sanitize_title($attr)] = array(
                    'name' => sanitize_title($attr),
                    'value' => $attribute["options"],
                    'position' => $attribute["position"],
                    'is_visible' => $attribute["visible"],
                    'is_variation' => $attribute["variation"],
                    'is_taxonomy' => '1'
                );
            }
            update_post_meta($product_id,'_product_attributes',$productAttributes); // save the meta entry for product attributes
        }

        /*add product properties tab information*/

        $this->digi_add_product_meta_data($product_id, $total_product_info, $digi_import_options, $scrap_config);


        /*create variation products for variable product*/

        $variations = array();

        if($var_color_count > 0)
        {
            foreach ($variation_attr_color as $color_slug => $color_label)
            {
                $new_variation = array("regular_price"=>$product_sale_price,"price"=>$product_sale_price,"attributes"=>array(array("name"=>"color","option"=>$color_slug)));
                array_push($variations,$new_variation);
            }
        }

        if($var_size_count > 0)
        {
            foreach ($variation_attr_size as $size_slug => $size_label)
            {
                $new_variation = array("regular_price"=>$product_sale_price,"price"=>$product_sale_price,"attributes"=>array(array("name"=>"size","option"=>$size_slug)));
                array_push($variations,$new_variation);
            }
        }


        if($variations){
            try{
                foreach($variations as $variation){
                    $objVariation = new WC_Product_Variation();
                    $objVariation->set_price($variation["price"]);
                    $objVariation->set_regular_price($variation["regular_price"]);
                    $objVariation->set_parent_id($product_id);

                    $var_attributes = array();
                    foreach($variation["attributes"] as $vattribute){
                        $taxonomy = "pa_".wc_sanitize_taxonomy_name(stripslashes($vattribute["name"])); // name of variant attribute should be same as the name used for creating product attributes
                        $attr_val_slug =  wc_sanitize_taxonomy_name(stripslashes($vattribute["option"]));
                        $var_attributes[$taxonomy]=$attr_val_slug;
                    }
                    $objVariation->set_attributes($var_attributes);

                    $objVariation->save();
                }
            }
            catch(Exception $e){
                // handle exception here
            }
        }

        $scrap_detailes = get_option('progress_status_details', true);
        $scrap_name = $scrap_detailes['scrap-name'];
        $num_of_imported = $scrap_detailes['number_of_products_imported'];
        $num_of_imported = $num_of_imported + 1;
        $scrap_detailes['number_of_products_imported'] = $num_of_imported ;
        update_option('progress_status_details',$scrap_detailes);

        /*current task update*/

        if(get_option($scrap_name))
        {
            $current_scrap = get_option($scrap_name , true);
            $current_scrap['number-of-product-imported'] = $num_of_imported;
            update_option($scrap_name , $current_scrap);
        }

        return 'product-imported';

    }

}


add_filter('get_attached_file', 'digi_replace_attached_file', 10, 2);

function digi_replace_attached_file($att_url, $att_id) {

    $post = get_post($att_id);
    if(!is_object($post))
    {
        return $att_url;
    }
    if($post->post_author != "8888")
    {
        return $att_url;
    }

    if ($att_url) {
        $url = explode(";", $att_url);
        if (sizeof($url) > 1)
        {
            return digi_has_internal_image_path($url[1]) ? get_post($att_id)->guid : $url[1];
        }

    }
    return $att_url;
}

add_filter('wp_get_attachment_url', 'digi_replace_attachment_url', 10, 2);

function digi_replace_attachment_url($att_url, $att_id) {

    $post = get_post($att_id);
    if(!is_object($post))
    {
        return $att_url;
    }
    if($post->post_author != "8888")
    {
        return $att_url;
    }

    if ($att_url) {
        $url = explode(";", $att_url);
        if (sizeof($url) > 1)
            return digi_has_internal_image_path($url[1]) ? get_post($att_id)->guid : $url[1];
        else {
            if (get_post($att_id)) {
                $url = get_post($att_id)->guid;
                if ($url && strpos($url, 'http') === 0 )
                {
                    return $url;
                }
            }
        }
    }
    return $att_url;
}

add_filter('posts_where', 'digi_query_attachments');

function digi_query_attachments($where) {
    /*if (isset($_POST['action']) && ($_POST['action'] == 'query-attachments')) {
        global $wpdb;
        $where .= ' AND ' . $wpdb->prefix . 'posts.post_author <> 77777 ';
    }*/
    return $where;
}

add_filter('posts_where', function ( $where, \WP_Query $q ) {
    return $where;
}, 10, 2);

add_filter('wp_get_attachment_image_src', 'digi_replace_attachment_image_src', 10, 3);

function digi_replace_attachment_image_src($image, $att_id, $size) {

    $post = get_post($att_id);
    if(!is_object($post))
    {
        return $image;
    }
    if($post->post_author != "8888")
    {
        return $image;
    }

    if (!$image)
        return $image;

    if (strpos($image[0], ';http') !== false)
        $image[0] = 'http' . explode(";http", $image[0])[1];

    if (!$att_id)
        return $image;

    $post = get_post($att_id);

    if (digi_has_internal_image_path($image[0]) && $post->post_author != "8888")
        return $image;

    if(is_singular('product'))
    {
        $width = 1000;
        $height = 1000;
    }
    else
    {
        $width = 300;
        $height = 300;
    }

    /*if (digi_should_hide())
        return null;
    $image_size = digi_get_image_size($size);*/
    if (1 == 1) {
        return array(
            digi_has_internal_image_path($image[0]) ? get_post($att_id)->guid : $image[0],
            $width,
            $height,
            null,
        );
    }
    /*$dimension = $post ? get_post_meta($post, 'digi_image_dimension') : null;
    $arrdigi = digi_get_width_height($dimension);
    return array(
        digi_has_internal_image_path($image[0]) ? get_post($att_id)->guid : $image[0],
        $arrdigi['width'] == null || (!$dimension && isset($image_size['width']) && $image_size['width'] < $arrdigi['width']) ? $image_size['width'] : $arrdigi['width'],
        $arrdigi['height'] == null || (!$dimension && isset($image_size['height']) && $image_size['height'] < $arrdigi['height']) ? $image_size['height'] : $arrdigi['height'],
        isset($image_size['crop']) ? $image_size['crop'] : '',
    );*/
}

function digi_get_internal_image_path() {
    return explode("//", get_home_url())[1] . "/wp-content/uploads/";
}

function digi_get_internal_image_path2() {
    return get_bloginfo() . ".files.wordpress.com";
}

function digi_get_internal_image_path3() {
    return explode('.', explode("//", get_home_url())[1])[0] . ".files.wordpress.com";
}

// for WPML Multilingual CMS
function digi_get_internal_image_path4() {
    return explode("/", explode("//", get_home_url())[1])[0] . "/wp-content/uploads/";
}

function digi_has_internal_image_path($url) {
    return strpos($url, digi_get_internal_image_path()) !== false || strpos($url, digi_get_internal_image_path2()) !== false || strpos($url, digi_get_internal_image_path3()) !== false || strpos($url, digi_get_internal_image_path4()) !== false;
}

add_filter('wp_get_attachment_metadata', 'digi_filter_wp_get_attachment_metadata', 10, 2);

function digi_filter_wp_get_attachment_metadata($data, $post_id) {
    if (!$data || !is_array($data)) {
        $dimension = get_post_meta($post_id, 'digi_image_dimension');
        return digi_get_width_height($dimension);
    }
    return $data;
}

add_action('init', 'zhk_guard_init');
/**
 * Initialize function for class and hook it to wordpress init action
 */
function zhk_guard_init() {
    $settings = [
        'name'          => 'دیجی اسکراپر',
        'slug'          => 'zhk_guard_register_DGS',
        'parent_slug'   => 'woo-dgscraper', // Read this: https://developer.wordpress.org/reference/functions/add_submenu_page/#parameters
        'text_domain'   => 'woo-digi-scraper',
        'product_token' => 'ffc2f699-199b-4b8c-8599-d7428d3a3fda', // Get it from here: https://zhaket.com/dashboard/licenses/
        'option_name'   => 'register_DGS_settings_secrity'
    ];
    Zhaket_Guard_DGS::instance($settings);
}