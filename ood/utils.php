<?php

class utils
{

    static function maxNameAsset($data_array)
    {
        $max_number = 0;
        for ($i = 0; $i < count($data_array); $i++) {
            //example:'com_content.category.1157'

            $first_position = strpos($data_array[$i], '.');
            $last_position = strpos($data_array[$i], '.', $first_position + 1);

            $number_name = (int)substr($data_array[$i], $last_position + 1);

            if ($number_name > $max_number) {
                $max_number = $number_name;
            }
        }
        return $max_number;
    }

    static function addAutoIncrement($content_max_id,$asset_max_id){
        if($asset_max_id > $content_max_id){
            return $asset_max_id-$content_max_id;
        }
        else{
            return 0;
        }
    }

    // static function isValidJson($json_str)
    // {
    //     json_decode($json_str);
    //     return (json_last_error() === JSON_ERROR_NONE);
    // }


}
