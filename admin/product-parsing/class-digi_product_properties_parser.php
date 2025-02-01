<?php

/**
 * The file that defines the core plugin class
 *
 * A class define for get product properties from html object
 *
 * @link       http://www.beyranplugins.ir/
 * @since      1.0.0
 *
 * @package    digi-scraper/admin/
 * @subpackage product-parsing/
 */
class Digi_product_properties_parser
{
    use Digi_static_functions;

    /**
     * Function to parse object html for get product properties.
     *
     * @param $html_object
     * @param $gt google translate object
     * @return array
     */

    public function get_product_properties($html_object , $gt)
    {
        /*total product properties*/
        $step_tr = 0;
        $attribute_title = array();
        $attribute_slug = array();
        $attribute_value = array();
        $attribute_value_slug = array();
        $product_properties = array();

        if($html_object->data->product->specifications)
        {
            $specifications = $html_object->data->product->specifications;
            if(!empty($specifications))
            {
                foreach($specifications as $specification)
                {
                    $specification_key_value = $specification->attributes;
                    if(!empty($specification_key_value))
                    {
                        foreach ($specification_key_value as $attribute)
                        {
                            if(property_exists($attribute, "values"))
                            {
                                $title = $attribute->title;
                                $values = $attribute->values;
                                $attr_title = ltrim($title );
                                $attr_title = rtrim($title );
                                $attr_value = ltrim($values[0]);
                                $attr_value = rtrim($attr_value);

                                array_push($attribute_title,$attr_title);
                                $attr_title = str_replace('‌','', $attr_title);
                                $attr_title = str_replace('×','', $attr_title);
                                $attr_title = str_replace('(','',$attr_title);
                                $attr_title = str_replace(')','',$attr_title);
                                $attr_title = str_replace('/','-',$attr_title);
                                $attr_title = str_replace(',','-',$attr_title);
                                $attr_title = str_replace('&','',$attr_title);
                                $attr_title = str_replace('@','',$attr_title);
                                $attr_title = str_replace(';','',$attr_title);
                                $attr_title = str_replace('.','',$attr_title);
                                $attr_title = str_replace(' ','-',$attr_title);



                                //$attr_title_translated = $gt->setSource('fa')->setTarget('en')->translate($attr_title);
                                $attr_title_translated = $this->converttoenglish($attr_title);
                                $attr_title_translated = strtolower($attr_title_translated);
                                $attr_title_translated = 'at_'.$attr_title_translated;
                                $attr_title_translated = str_replace(' ','-',$attr_title_translated);
                                $str_length = strlen($attr_title_translated);
                                if($str_length > 26)
                                {
                                    $attr_title_translated = substr($attr_title_translated , 0,26);
                                    if($str_length > 26)
                                    {
                                        $attr_title_translated = substr($attr_title_translated , 0,26);
                                    }
                                }

                                if(in_array($attr_title_translated, $attribute_slug))
                                {
                                    $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
                                    $random_character = substr(str_shuffle($permitted_chars), 0, 1);
                                    $attr_title_translated = substr($attr_title_translated,0, strlen($attr_title_translated) - 1).$random_character;
                                }

                                array_push($attribute_slug,$attr_title_translated);

                                $attr_value = str_replace('  ','',$attr_value);
								
								 if(mb_strlen($attr_value) < 2)
                                {
                                    $attr_value = $attr_value.' '.'تا';
                                }

                                //$attr_value_translated = $gt->setSource('fa')->setTarget('en')->translate($attr_value);
                                $attr_value_for_translate = $attr_value;
                                $attr_value_for_translate = str_replace('‌','', $attr_value_for_translate);
                                $attr_value_for_translate = str_replace('×','', $attr_value_for_translate);
                                $attr_value_for_translate = str_replace('(','',$attr_value_for_translate);
                                $attr_value_for_translate = str_replace(')','',$attr_value_for_translate);
                                $attr_value_for_translate = str_replace('/','-',$attr_value_for_translate);
                                $attr_value_for_translate = str_replace(',','-',$attr_value_for_translate);
                                $attr_value_for_translate = str_replace('&','',$attr_value_for_translate);
                                $attr_value_for_translate = str_replace('@','',$attr_value_for_translate);
                                $attr_value_for_translate = str_replace(';','',$attr_value_for_translate);
                                $attr_value_for_translate = str_replace('.','',$attr_value_for_translate);
                                $attr_value_for_translate = str_replace(' ','-',$attr_value_for_translate);
                                $attr_value_translated = $this->converttoenglish($attr_value_for_translate);
                                $attr_value_translated = strtolower($attr_value_translated);
                                $attr_value_translated = 'at_'.$attr_value_translated;


                                $str_length = strlen($attr_value_translated);
                                if($str_length > 26)
                                {
                                    $attr_value_translated = substr($attr_value_translated , 0,26);
                                    if($str_length > 26)
                                    {
                                        $attr_value_translated = substr($attr_value_translated , 0,26);
                                    }
                                }


                                if(!array_key_exists($attr_value_translated,$attribute_value_slug))
                                {
                                    $key_generate = rand(10,99);
                                    $attr_value_translated = $attr_value_translated.'_'.$key_generate;
                                    array_push($attribute_value_slug,$attr_value_translated);
                                }
                                else
                                {
                                    array_push($attribute_value_slug,$attr_value_translated);
                                }
                                if(!array_key_exists($attr_value,$attribute_value))
                                {
                                    $key_generate = rand(10,99);
                                    $attr_value = $attr_value.'_'.$key_generate;
                                    array_push($attribute_value,$attr_value);
                                }
                                else
                                {
                                    array_push($attribute_value,$attr_value);
                                }
                                $step_tr++;
                            }
                        }

                    }
                }
            }
        }

        $product_attribute = array_combine($attribute_slug,$attribute_title);

        $step_loop = 0;
        foreach ($product_attribute as $attr_slug => $attr_value) {

            $new_attr_properties = array(
                $attr_slug => $attr_value,
                $attribute_value_slug[$step_loop] => $attribute_value[$step_loop]
            );
            array_push($product_properties,$new_attr_properties);
            $step_loop++;
        }

        return $product_properties;
    }

}