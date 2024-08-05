<?php

require_once './query.php';

class fieldgroup
{
    static function add_fieldgroup($j2_table_fieldgroup, $j4_table_assets, $j4_table_fieldgroup)
    {

        $parent_id = 8;
        $level = 2;
        $table_map_name = "map_fieldgroups";

        $parametter_insert_map = [];
        $parametter_insert_assets = [];
        $parametter_insert_fieldgroup = [];

        //get name of fieldgroup from k2
        $data_name = query::$joomla2->getColumnMultiData("SELECT * from $j2_table_fieldgroup", "name");

        $data_id = query::$joomla2->getColumnMultiData("SELECT * from $j2_table_fieldgroup", "id");

        $fieldgroup_count = query::$joomla2->getColumnData("SELECT COUNT(id) as count_id from $j2_table_fieldgroup", "count_id");

        query::$joomla4->resetAutoIncrement($j4_table_fieldgroup);
        query::$joomla4->resetAutoIncrement($j4_table_assets);

        if (query::$joomla4->checkExistTable($table_map_name) === null) {
            query::$joomla4->createTable("CREATE TABLE $table_map_name (id int NOT NULL auto_increment,j2_id int NOT NULL,j4_id int NOT NULL ,j4_asset_id int,name varchar(225),primary key(id) );");
        } else {
            query::$joomla4->resetAutoIncrement($table_map_name);
        }

        for ($i = 0; $i < $fieldgroup_count; $i++) {

            //--#5-- add to map table

            //get asset_id
            $asset_id = query::$joomla4->getColumnData("SELECT MAX(id) as max_id from $j4_table_assets", "max_id") + 1;

            //get id of fieldgroup in joomla4 fieldgroup table
            $fieldgroup_id = query::$joomla4->getColumnData("SELECT MAX(id) as max_id from $j4_table_fieldgroup", "max_id") + 1;

            $fieldgroup_name = "com_content.fieldgroup.$fieldgroup_id";

            //--#2-- get lft in assets table
            $asset_lft = query::$joomla4->getColumnData("SELECT max(rgt) as max_lft from $j4_table_assets where parent_id=$parent_id && `level`=$level;", "max_lft") + 1;
            $asset_rgt = $asset_lft + 1;

            $json_params = '{"display_readonly":"1"}';

            //--#3-- ---------INSERT INTO map table----------
            $parametter_insert_map[':j2_id'] = $data_id[$i];
            $parametter_insert_map[':j4_id'] = $fieldgroup_id;
            $parametter_insert_map[':j4_asset_id'] = $asset_id;
            $parametter_insert_map['name'] = $data_name[$i];

            query::$joomla4->Insert("INSERT INTO $table_map_name(j2_id,j4_id,j4_asset_id,`name`) VALUES(:j2_id,:j4_id,:j4_asset_id,:name)", $parametter_insert_map);

            //--#3-- ---------INSERT INTO joomla4 assets table-----------

            $parametter_insert_assets[':parent_id'] = $parent_id;
            $parametter_insert_assets[':lft'] = $asset_lft;
            $parametter_insert_assets[':rgt'] = $asset_rgt;
            $parametter_insert_assets[':level'] = $level;
            $parametter_insert_assets[':name'] = $fieldgroup_name;
            $parametter_insert_assets[':title'] = $data_name[$i];
            $parametter_insert_assets[':rules'] = '{}';

            query::$joomla4->Insert("INSERT INTO $j4_table_assets (parent_id,lft,rgt,`level`,`name`,title,rules) VALUES (:parent_id,:lft,:rgt,:level,:name,:title,:rules);", $parametter_insert_assets);

            //--#4-- ---------INSERT INTO joomla4 fieldgroup table------- 

            $parametter_insert_fieldgroup[':asset_id'] = $asset_id;
            $parametter_insert_fieldgroup[':context'] = 'com_content.article';
            $parametter_insert_fieldgroup[':title'] = $data_name[$i];
            $parametter_insert_fieldgroup[':note'] = '';
            $parametter_insert_fieldgroup[':description'] = '';
            $parametter_insert_fieldgroup[':state'] = 1;
            $parametter_insert_fieldgroup[':ordering'] = 0;
            $parametter_insert_fieldgroup[':params'] = $json_params;
            $parametter_insert_fieldgroup[':language'] = '*';
            $parametter_insert_fieldgroup[':created'] = date("Y-m-d h:i:s");
            $parametter_insert_fieldgroup[':created_by'] = query::$user_id;
            $parametter_insert_fieldgroup[':modified'] = date("Y-m-d h:i:s");
            $parametter_insert_fieldgroup[':modified_by'] = query::$user_id;
            $parametter_insert_fieldgroup[':access'] = 1;


            query::$joomla4->Insert("INSERT INTO $j4_table_fieldgroup (asset_id,context,title,note,`description`,`state`,ordering,params,`language`,created,created_by,modified,modified_by,access) VALUES (:asset_id,:context,:title,:note,:description,:state,:ordering,:params,:language,:created,:created_by,:modified,:modified_by,:access);", $parametter_insert_fieldgroup);
        }
    }
}
