<?php

require_once './query.php';

class field
{


    static function add_field($j2_table_fields, $j2_table_fieldgroups, $j4_table_assets, $j4_table_fields, $j4_table_fieldgroups)
    {

        $level = 2;
        $table_map_name = "map_fields";


        // --#1-- select nessesary data from k2 fields table
        $select_table_query = "select * from $j2_table_fields";

        $data_id = query::$joomla2->getColumnMultiData($select_table_query, "id");

        $data_name = query::$joomla2->getColumnMultiData($select_table_query, "name");

        $data_type = query::$joomla2->getColumnMultiData($select_table_query, "type");

        $data_group = query::$joomla2->getColumnMultiData($select_table_query, "group");

        $data_ordering = query::$joomla2->getColumnMultiData($select_table_query, "ordering");

        ////////////////////////////////////////////////
        $field_count = query::$joomla2->getColumnData("select COUNT(id) as count_id from $j2_table_fields", "count_id");

        query::$joomla4->resetAutoIncrement($j4_table_fields);
        query::$joomla4->resetAutoIncrement($j4_table_assets);

        //if map field table not exist,create a table
        if (query::$joomla4->checkExistTable($table_map_name) === null) {
            query::$joomla4->Insert("CREATE TABLE $table_map_name (id int NOT NULL auto_increment,j2_id int NOT NULL,j4_id int NOT NULL ,j4_asset_id int,name varchar(225),primary key(id) );");
        } else {
            query::$joomla4->resetAutoIncrement($table_map_name);
        }


        for ($i = 0; $i < $field_count; $i++) {


            //--#10-- add to map table

            //get asset_id
            $asset_id = query::$joomla4->getColumnData("select MAX(id) as max_id from $j4_table_assets", "max_id") + 1;

            //get id of fieldgroup in joomla4 fieldgroup table
            $field_id = query::$joomla4->getColumnData("select MAX(id) as max_id from $j4_table_fields", "max_id") + 1;

            query::$joomla4->Insert("insert into $table_map_name(j2_id,j4_id,j4_asset_id,name) values($data_id[$i],$field_id,$asset_id,'$data_name[$i]')");

            // --------- add field in asset table -------------


            // --#2--
            $update_fieldgroup_parent = false;
            $parent_asset_id=8;

            $fieldgroup_id = query::$joomla4->getColumnData("select * from map_fieldgroups where j2_id=$data_group[$i]", "j4_id");

            if ($data_group[$i] != 0) {
                $level = 3;
                $update_fieldgroup_parent = true;

                // --#3--

                //get asset_id of name of fieldgroup == parent_id in assets table
                $parent_asset_id = query::$joomla4->getColumnData("select * from map_fieldgroups where j2_id=$data_group[$i]", "j4_asset_id");

            } else {
                // --#4--
                $level = 2;
                $update_fieldgroup_parent = false;
                $parent_asset_id = 8;
            }

            // --#5--
            $asset_lft = query::$joomla4->getColumnData("select max(rgt) as max_rgt from $j4_table_assets where level=$level", "max_rgt") + 1;
            $asset_rgt = $asset_lft + 1;

            // --#6--
            //get id of new field in field table == name in assets table


            query::$joomla4->Insert("insert into hcnov_assets (parent_id,lft,rgt,level,name,title,rules) values($parent_asset_id,$asset_lft,$asset_rgt,$level,'com_content.field.$field_id','$data_name[$i]','{}');");

            // --#7--
            //update lft & rgt of fieldgroup
            if ($update_fieldgroup_parent) {
                query::$joomla4->Insert("update hcnov_assets set rgt=rgt+2 where id = $parent_asset_id;");
            }

            // --------- add field in field table joomla4 ---------------


            // --#8--
            // this line return a object
            $data_value = json_decode(query::$joomla2->getColumnData($select_table_query, "value"))[0];

            // --#9--

            // a string for params column in joomla4 fields table
            $params_json = '{"hint":"","class":"","label_class":"","show_on":"","showon":"","render_class":"","value_render_class":"","showlabel":"1","label_render_class":"","display":"2","prefix":"","suffix":"","layout":"","display_readonly":"2"}';

            // a string for fieldparams column in joomla4 fields table
            $fieldparams_json = '{"filter":"","maxlength":""}';

            query::$joomla4->Insert("insert into $j4_table_fields(asset_id,context,group_id,title,name,label,default_value,type,note,description,state,required,only_use_in_subform,ordering,params,fieldparams,language,created_time,created_user_id,modified_time,modified_by,access) values($asset_id,'com_content.article',$fieldgroup_id,'$data_name[$i]','$data_name[$i]','$data_name[$i]','','$data_type[$i]','','',1,$data_value->required,0,$data_ordering[$i],'$params_json','$fieldparams_json','*',NOW(),926,NOW(),926,1);");
        }
    }
}
