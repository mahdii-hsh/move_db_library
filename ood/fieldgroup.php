<?php

require_once './query.php';

class fieldgroup
{
    static function add_fieldgroup($j2_table_fieldgroup, $j4_table_assets, $j4_table_fieldgroup)
    {

        $parent_id = 8;
        $level = 2;
        $table_map_name="map_fieldgroups";

        //get name of fieldgroup from k2
        $data_name = query::$joomla2->getColumnMultiData("SELECT * from $j2_table_fieldgroup", "name");

        $data_id = query::$joomla2->getColumnMultiData("SELECT * from $j2_table_fieldgroup", "id");

        $fieldgroup_count = query::$joomla2->getColumnData("SELECT COUNT(id) as count_id from $j2_table_fieldgroup", "count_id");

        query::$joomla4->resetAutoIncrement($j4_table_fieldgroup);
        query::$joomla4->resetAutoIncrement($j4_table_assets);

        if (query::$joomla4->checkExistTable($table_map_name) === null) {
            query::$joomla4->Insert("CREATE TABLE $table_map_name (id int NOT NULL auto_increment,j2_id int NOT NULL,j4_id int NOT NULL ,j4_asset_id int,name varchar(225),primary key(id) );");
        }
        else{
            query::$joomla4->resetAutoIncrement($table_map_name);
        }

        for ($i = 0; $i < $fieldgroup_count; $i++) {

            //--#5-- add to map table

            //get asset_id
            $asset_id = query::$joomla4->getColumnData("SELECT MAX(id) as max_id from $j4_table_assets", "max_id") + 1;

            //get id of fieldgroup in joomla4 fieldgroup table
            $fieldgroup_id = query::$joomla4->getColumnData("SELECT MAX(id) as max_id from $j4_table_fieldgroup", "max_id") + 1;

            query::$joomla4->Insert("INSERT INTO $table_map_name(j2_id,j4_id,j4_asset_id,`name`) values($data_id[$i],$fieldgroup_id,$asset_id,'$data_name[$i]')");

            //--#2-- get lft in assets table
            $lft = query::$joomla4->getColumnData("SELECT max(rgt) as max_lft from $j4_table_assets where parent_id=$parent_id && `level`=$level;", "max_lft") + 1;
            $rgt = $lft + 1;

            //--#3-- ---------INSERT INTO joomla4 assets table----------- 
            query::$joomla4->Insert("INSERT INTO $j4_table_assets (parent_id,lft,rgt,`level`,`name`,title,rules) values($parent_id,$lft,$rgt,$level,'com_content.fieldgroup.$fieldgroup_id','$data_name[$i]','{}');");

            //--#4-- ---------INSERT INTO joomla4 fieldgroup table------- 
            $json_params = '{"display_readonly":"1"}';

            query::$joomla4->Insert("INSERT INTO $j4_table_fieldgroup (asset_id,context,title,note,`description`,`state`,ordering,params,`language`,created,created_by,modified,modified_by,access) values ($asset_id,'com_content.article','$data_name[$i]','','',1,0,'$json_params','*',NOW(),926,NOW(),926,1);");
        }
    }
}
