<?php

require_once './query.php';

class field
{


    static function add_field($j2_table_fields, $j2_table_fieldgroups, $j4_table_assets, $j4_table_fields, $j4_table_fieldgroups)
    {

        $level = 2;

        // --#1-- select nessesary data from k2 fields table
        $select_table_query = "select * from $j2_table_fields";
        

        $data_name = query::$joomla2->getColumnMultiData($select_table_query, "name");

        $data_type = query::$joomla2->getColumnMultiData($select_table_query, "type");

        $data_group = query::$joomla2->getColumnMultiData($select_table_query, "group");

        $data_ordering = query::$joomla2->getColumnMultiData($select_table_query, "ordering");
        ////////////////////////////////////////////////
        $field_count = query::$joomla2->getColumnData("select COUNT(id) as count_id from $j2_table_fields", "count_id");

        query::$joomla4->resetAutoIncrement($j4_table_fields);
        query::$joomla4->resetAutoIncrement($j4_table_assets);

        for ($i = 0; $i < $field_count; $i++) {

            // --------- add field in asset table -------------

            // --#2--
            $update_fieldgroup_parent = false;

            if ($data_group[$i] != 0) {
                $level = 3;
                $update_fieldgroup_parent = true;

                // --#3--
                //get name of fieldgroup of field
                $fieldgroup_name = query::$joomla2->getColumnData("select * from $j2_table_fieldgroups where id=$data_group[$i]", "name");

                //get asset_id of name of fieldgroup == parent_id in assets table
                $parent_id = query::$joomla4->getColumnData("select * from $j4_table_assets where name like '%com_content.fieldgroup%' && title='$fieldgroup_name'", "id");
            } else {
                // --#4--
                $level = 2;
                $update_fieldgroup_parent = false;
                $parent_id = 8;
            }

            // --#5--
            $asset_lft = query::$joomla4->getColumnData("select max(rgt) as max_rgt from $j4_table_assets where level=$level", "max_rgt") + 1;
            $asset_rgt = $asset_lft + 1;

            // --#6--
            //get id of new field in field table == name in assets table

            $field_id = query::$joomla4->getColumnData("select max(id) as max_id from $j4_table_fields", "max_id")+1;

            query::$joomla4->Insert("insert into hcnov_assets (parent_id,lft,rgt,level,name,title,rules) values($parent_id,$asset_lft,$asset_rgt,$level,'com_content.field.$field_id','$data_name[$i]','{}');");

            // --#7--
            //update lft & rgt of fieldgroup
            if ($update_fieldgroup_parent) {
                query::$joomla4->Insert("update hcnov_assets set rgt=rgt+2 where id = $parent_id;");
            }

            // --------- add field in field table joomla4 ---------------


            // --#8--
            // this line return a object
            $data_value = json_decode(query::$joomla2->getColumnData($select_table_query, "value"))[0];

            //get maximum id in asset table
            $asset_id = query::$joomla4->getColumnData("select max(id) as max_id from hcnov_assets", "max_id");

            // --#9--
            $fieldgroup_id = query::$joomla4->getColumnData("select * from $j4_table_fieldgroups where title='$fieldgroup_name'", "id");

            // a string for params column in joomla4 fields table
            $params_json = '{"hint":"","class":"","label_class":"","show_on":"","showon":"","render_class":"","value_render_class":"","showlabel":"1","label_render_class":"","display":"2","prefix":"","suffix":"","layout":"","display_readonly":"2"}';

            // a string for fieldparams column in joomla4 fields table
            $fieldparams_json = '{"filter":"","maxlength":""}';

            query::$joomla4->Insert("insert into $j4_table_fields(asset_id,context,group_id,title,name,label,default_value,type,note,description,state,required,only_use_in_subform,ordering,params,fieldparams,language,created_time,created_user_id,modified_time,modified_by,access) values($asset_id,'com_content.article',$fieldgroup_id,'$data_name[$i]','$data_name[$i]','$data_name[$i]','','$data_type[$i]','','',1,$data_value->required,0,$data_ordering[$i],'$params_json','$fieldparams_json','*',NOW(),926,NOW(),926,1);");
        }
    }
}
