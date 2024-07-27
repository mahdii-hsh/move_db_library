<?php

require_once './query.php';

class fieldgroup
{
    static function add_fieldgroup($j2_table_fieldgroup,$j4_table_assets,$j4_table_fieldgroup)
    {

        $parent_id = 8;
        $level = 2;
        

        //get name of fieldgroup from k2
        $data_name = query::$joomla2->getColumnMultiData("select * from $j2_table_fieldgroup", "name");
        $fieldgroup_count = query::$joomla2->getColumnData("select COUNT(id) as count_id from $j2_table_fieldgroup", "count_id");
        query::$joomla4->resetAutoIncrement($j4_table_fieldgroup);
        query::$joomla4->resetAutoIncrement($j4_table_assets);
        for ($i = 0; $i < $fieldgroup_count; $i++) {

            //--#2-- get lft in assets table
            $lft = query::$joomla4->getColumnData("select max(rgt) as max_lft from $j4_table_assets where parent_id=$parent_id && level=$level;", "max_lft") + 1;
            $rgt = $lft + 1;
            /////////////////////////////////////
            //--#3-- ---------insert into joomla4 assets table----------- 
            query::$joomla4->Insert("insert into $j4_table_assets (parent_id,lft,rgt,level,name,title,rules) values($parent_id,$lft,$rgt,$level,'com_content.fieldgroup','$data_name[$i]','{}');");
            //get asset_id
            $asset_id = query::$joomla4->getColumnData("select MAX(id) as max_id from $j4_table_assets", "max_id");
            //--#4-- ---------insert into joomla4 fieldgroup table------- 
            $json_params = '{"display_readonly":"1"}';
            query::$joomla4->Insert("insert into $j4_table_fieldgroup (asset_id,context,title,note,description,state,ordering,params,language,created,created_by,modified,modified_by,access) values ($asset_id,'com_content.article','$data_name[$i]','','',1,0,'$json_params','*',NOW(),926,NOW(),926,1);");
            //--#5-- update name of field group in assets table
            $id = query::$joomla4->getColumnData("select MAX(id) as max_id from $j4_table_fieldgroup", "max_id");
            $asset_name = "com_content.fieldgroup." . "$id";
            query::$joomla4->Insert("update $j4_table_assets set `name` ='$asset_name' where id=$asset_id");
        }
    }
}
