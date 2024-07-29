<?php

require_once './query.php';

class category
{
    static function add_category($j2_table_categories, $j2_table_items, $j4_table_categories, $j4_table_assets)
    {


        //level in asset table
        $asset_level = 2;
        //level in category level
        $category_level = 1;

        $table_map_name = "map_categories";

        //--#1-- select nessesary data from k2 categories table
        $j2_select_category_query = "select * from $j2_table_categories";

        $j2_select_item_query = "select * from $j2_table_items";

        $data_name = query::$joomla2->getColumnMultiData($j2_select_category_query, "name");

        $data_id = query::$joomla2->getColumnMultiData($j2_select_category_query, "id");

        $data_alias = query::$joomla2->getColumnMultiData($j2_select_category_query, "alias");

        $data_description = query::$joomla2->getColumnMultiData($j2_select_category_query, "description");

        $data_parent = query::$joomla2->getColumnMultiData($j2_select_category_query, "parent");

        $data_extra_fields_group = query::$joomla2->getColumnMultiData($j2_select_category_query, "extraFieldsGroup");

        $data_published = query::$joomla2->getColumnMultiData($j2_select_category_query, "published");

        // $data_ordering = query::$joomla2->getColumnMultiData($j2_select_category_query, "ordering");

        $data_language = query::$joomla2->getColumnMultiData($j2_select_category_query, "language");
        //this query for get hits of category
        $data_catid = query::$joomla2->getColumnMultiData($j2_select_item_query, "catid");
        $data_hits = query::$joomla2->getColumnMultiData($j2_select_item_query, "hits");
        ////////////////////////////////////////////////
        $category_count = query::$joomla2->getColumnData("select COUNT(id) as count_id from $j2_table_categories", "count_id");

        query::$joomla4->resetAutoIncrement($j4_table_categories);
        query::$joomla4->resetAutoIncrement($j4_table_assets);

        //if map category table not exist,create a table

        if (query::$joomla4->checkExistTable($table_map_name) === null) {
            query::$joomla4->Insert("CREATE TABLE $table_map_name (id int NOT NULL auto_increment,j2_id int NOT NULL,j4_id int NOT NULL ,j4_asset_id int,name varchar(225),primary key(id) );");
        } else {
            query::$joomla4->resetAutoIncrement($table_map_name);
        }

        // --------- add category in category table -------------
        for ($i = 0; $i < $category_count; $i++) {


            if ($data_parent[$i] != 0) {
                $asset_level = 3;
                $category_level = 2;
            } else {
                $asset_level = 2;
                $category_level = 1;
            }

            //--#2-- find lft in category table in joomla4
            $category_lft = query::$joomla4->getColumnData("select max(rgt) as max_rgt from $j4_table_categories where level=$category_level", "max_rgt") + 1;

            $category_rgt = $category_lft + 1;

            // insert into map table
            $asset_id = query::$joomla4->getColumnData("select max(id) as max_id from $j4_table_assets", "max_id") + 1;

            $category_id = query::$joomla4->getColumnData("select max(id) as max_id from $j4_table_categories", "max_id") + 1;

            query::$joomla4->Insert("insert into $table_map_name (j2_id,j4_id,j4_asset_id,name) values($data_id[$i],$category_id,$asset_id,'$data_name[$i]')");


            // --#3--
            //alias in joomla4 should be lower case
            $path = strtolower($data_alias[$i]);

            $json_params = '{"category_layout":"","image":"","image_alt":""}';
            $json_metadata = '{"author":"","robots":""}';

            // after this insert : update asset_id,parent_id,hits,path
            query::$joomla4->Insert("insert into $j4_table_categories (asset_id,parent_id,lft,rgt,level,path,extension,title,alias,note,description,published,access,params,metadesc,metakey,metadata,created_user_id,created_time,modified_user_id,modified_time,hits,language,version) values($asset_id,1,$category_lft,$category_rgt,$category_level,'$path','com_content','$data_name[$i]','$data_alias[$i]','','$data_description[$i]',$data_published[$i],1,'$json_params','','','$json_metadata',926,NOW(),926,NOW(),0,'$data_language[$i]',1)");

            //--#4-- check for update rgt of root in category table

            $root_rgt = query::$joomla4->getColumnData("select * from $j4_table_categories where title='ROOT';", "rgt");

            if ($category_rgt >= $root_rgt) {
                $new_root_rgt = $category_rgt + 1;
                query::$joomla4->Insert("update $j4_table_categories set rgt=$new_root_rgt where title='ROOT'");
            }

            // --#7 -- ----------add category in assets table ------------

            $asset_lft = query::$joomla4->getColumnData("select max(rgt) as max_rgt from $j4_table_assets where level=$asset_level ", "max_rgt") + 1;

            $asset_rgt = $asset_lft + 1;

            $asset_name = "com_content.category." . $category_id;

            query::$joomla4->Insert("insert into $j4_table_assets (parent_id,lft,rgt,level,name,title,rules) values(8,$asset_lft,$asset_rgt,$asset_level,'$asset_name','$data_name[$i]','{}')");
        }

        // ------------- update hits and parent_id in joomla4 category table -------------

        for ($i = 0; $i < $category_count; $i++) {


            // --#8-- update hits

            // $category_hits=query::$joomla2->getColumnData("select * from $j2_table_items where catid=$data_id[$i]","hits");

            $category_id=query::$joomla4->getColumnData("select * from $table_map_name where j2_id=$data_id[$i]","j4_id");

            // query::$joomla4->Insert("update $j4_table_categories set hits=$category_hits where id=$category_id");

            // --#5-- update parent_id in asset table and category table

            if ($data_parent[$i] != 0) {

                $parent_id = query::$joomla4->getColumnData("select * from $table_map_name where j2_id=$data_parent[$i];", "j4_id");

                $parent_asset_id = query::$joomla4->getColumnData("select * from $table_map_name where j2_id=$data_parent[$i];", "j4_asset_id");

                //update lft & rgt of parent of category
                query::$joomla4->Insert("update $j4_table_categories set rgt=rgt+2 where id = $parent_id;");

                $category_asset_id=query::$joomla4->getColumnData("select * from $table_map_name where j2_id=$data_id[$i]","j4_asset_id");

                query::$joomla4->Insert("update $j4_table_categories set parent_id=$parent_id where id=$category_id");

                query::$joomla4->Insert("update $j4_table_assets set parent_id=$parent_asset_id where id=$category_asset_id");

                //update asset_id of parent
                query::$joomla4->Insert("update $j4_table_assets set rgt=rgt+2 where id=$parent_asset_id");

            }
        }
    }
}
