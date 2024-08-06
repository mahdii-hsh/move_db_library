<?php

require_once './query.php';

class field
{


    static function add_field($j2_table_fields, $j4_table_assets, $j4_table_fields)
    {

        $level = 2;
        $table_map_name = "map_fields";

        $parametter_insert_map = [];
        $parametter_insert_assets = [];
        $parametter_insert_field = [];
        $parametter_update_asset = [];

        // --#1-- SELECT nessesary data from k2 fields table
        $select_table_query = "SELECT * from $j2_table_fields";

        $data_id = query::$joomla2->getColumnMultiData($select_table_query, "id");

        $data_name = query::$joomla2->getColumnMultiData($select_table_query, "name");

        $data_type = query::$joomla2->getColumnMultiData($select_table_query, "type");

        $data_group = query::$joomla2->getColumnMultiData($select_table_query, "group");

        $data_ordering = query::$joomla2->getColumnMultiData($select_table_query, "ordering");

        $data_value=query::$joomla2->getColumnMultiData($select_table_query, "value");

        ////////////////////////////////////////////////
        $field_count = query::$joomla2->getColumnData("SELECT COUNT(id) as count_id from $j2_table_fields", "count_id");

        query::$joomla4->resetAutoIncrement($j4_table_fields);
        query::$joomla4->resetAutoIncrement($j4_table_assets);

        // if map field table not exist,create a table
        if (query::$joomla4->checkExistTable($table_map_name) === null) {
            query::$joomla4->createTable("CREATE TABLE $table_map_name (id int NOT NULL auto_increment,j2_id int NOT NULL,j4_id int NOT NULL ,j4_asset_id int,name varchar(225),primary key(id) );");
        } else {
            query::$joomla4->resetAutoIncrement($table_map_name);
        }


        for ($i = 0; $i < $field_count; $i++) {


            //--#10-- add to map table

            //get asset_id
            $asset_id = query::$joomla4->getColumnData("SELECT MAX(id) as max_id from $j4_table_assets", "max_id") + 1;

            //get id of fieldgroup in joomla4 fieldgroup table
            $field_id = query::$joomla4->getColumnData("SELECT MAX(id) as max_id from $j4_table_fields", "max_id") + 1;

            $field_name = "com_content.field.$field_id";

            // --#8--
            // this line return a object
            $data_value_decode = json_decode($data_value[$i])[0];

            // --#9--

            // a string for params column in joomla4 fields table
            $json_params = '{"hint":"","class":"","label_class":"","show_on":"","showon":"","render_class":"","value_render_class":"","showlabel":"1","label_render_class":"","display":"2","prefix":"","suffix":"","layout":"","display_readonly":"2"}';

            // a string for fieldparams column in joomla4 fields table
            $fieldparams_json = '{"filter":"","maxlength":""}';

            if($data_type[$i]==='textfield'){
                $field_type='text';
            }
            else{
                $field_type=$data_type[$i];
            }

            //--#3-- ---------INSERT INTO map table----------
            $parametter_insert_map[':j2_id'] = $data_id[$i];
            $parametter_insert_map[':j4_id'] = $field_id;
            $parametter_insert_map[':j4_asset_id'] = $asset_id;
            $parametter_insert_map['name'] = $data_name[$i];

            query::$joomla4->Insert("INSERT INTO $table_map_name(j2_id,j4_id,j4_asset_id,`name`) VALUES(:j2_id,:j4_id,:j4_asset_id,:name)", $parametter_insert_map);

            // --------- add field in asset table -------------

            // --#2--
            $update_fieldgroup_parent = false;
            $parent_asset_id = 8;

            $fieldgroup_id = query::$joomla4->getColumnData("SELECT * from map_fieldgroups where j2_id=$data_group[$i]", "j4_id");

            if ($data_group[$i] !== 0) {
                $level = 3;
                $update_fieldgroup_parent = true;

                // --#3--
                //get asset_id of name of fieldgroup == parent_id in assets table
                $parent_asset_id = query::$joomla4->getColumnData("SELECT * from map_fieldgroups where j2_id=$data_group[$i]", "j4_asset_id");
            } else {
                // --#4--
                $level = 2;
                $update_fieldgroup_parent = false;
                $parent_asset_id = 8;
            }

            // --#5--
            $asset_lft = query::$joomla4->getColumnData("SELECT max(rgt) as max_rgt from $j4_table_assets where `level`=$level", "max_rgt") + 1;
            $asset_rgt = $asset_lft + 1;

            // --#6--
            //get id of new field in field table == name in assets table

            //--#4-- ---------INSERT INTO joomla4 assets table------- 
            
            $parametter_insert_assets[':parent_id'] = $parent_asset_id;
            $parametter_insert_assets[':lft'] = $asset_lft;
            $parametter_insert_assets[':rgt'] = $asset_rgt;
            $parametter_insert_assets[':level'] = $level;
            $parametter_insert_assets[':name'] = $field_name;
            $parametter_insert_assets[':title'] = $data_name[$i];
            $parametter_insert_assets[':rules'] = '{}';

            query::$joomla4->Insert("INSERT INTO $j4_table_assets (parent_id,lft,rgt,`level`,`name`,title,rules) VALUES (:parent_id,:lft,:rgt,:level,:name,:title,:rules);", $parametter_insert_assets);


            // --#7--
            //update lft & rgt of fieldgroup
            if ($update_fieldgroup_parent) {
                $parametter_update_asset[':parent_asset_id'] = $parent_asset_id;
                query::$joomla4->Insert("update $j4_table_assets set rgt=rgt+2 where id = :parent_asset_id;", $parametter_update_asset);
            }

            //--#4-- ---------INSERT INTO joomla4 field table------- 

            $parametter_insert_field[':asset_id'] = $asset_id;
            $parametter_insert_field[':context'] = 'com_content.article';
            $parametter_insert_field[':group_id'] = $fieldgroup_id;
            $parametter_insert_field[':title'] = $data_name[$i];
            $parametter_insert_field[':name'] = $data_name[$i];
            $parametter_insert_field[':label'] = $data_name[$i];
            $parametter_insert_field[':default_value'] = '';
            $parametter_insert_field[':type'] = $field_type;
            $parametter_insert_field[':note'] = '';
            $parametter_insert_field[':description'] = '';
            $parametter_insert_field[':state'] = 1;
            $parametter_insert_field[':required'] = $data_value_decode->required;
            $parametter_insert_field[':only_use_in_subform'] = 0;
            $parametter_insert_field[':ordering'] = $data_ordering[$i];
            $parametter_insert_field[':params'] = $json_params;
            $parametter_insert_field[':fieldparams'] = $fieldparams_json;
            $parametter_insert_field[':language'] = '*';
            $parametter_insert_field[':created_time'] = date("Y-m-d h:i:s");
            $parametter_insert_field[':created_user_id'] = query::$user_id;
            $parametter_insert_field[':modified_time'] = date("Y-m-d h:i:s");
            $parametter_insert_field[':modified_by'] = query::$user_id;
            $parametter_insert_field[':access'] = 1;

            query::$joomla4->Insert("INSERT INTO $j4_table_fields (asset_id,context,group_id,title,`name`,label,default_value,`type`,note,`description`,`state`,`required`,only_use_in_subform,ordering,params,fieldparams,`language`,created_time,created_user_id,modified_time,modified_by,access) VALUES (:asset_id,:context,:group_id,:title,:name,:label,:default_value,:type,:note,:description,:state,:required,:only_use_in_subform,:ordering,:params,:fieldparams,:language,:created_time,:created_user_id,:modified_time,:modified_by,:access);", $parametter_insert_field);
        }
    }
}
