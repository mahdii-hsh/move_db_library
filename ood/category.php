<?php

require_once './query.php';

class category
{
    static function add_category($j2_table_categories, $j2_table_fields, $j2_table_items, $j4_table_categories, $j4_table_assets, $j4_table_fields, $j4_table_field_category)
    {


        //level in asset table
        $asset_level = 2;
        //level in category level
        $category_level = 1;

        $parametter_insert_map = [];
        $parametter_insert_assets = [];
        $parametter_insert_category = [];
        $parametter_insert_field_category = [];
        $parametter_update_asset = [];
        $parametter_update_category = [];

        $table_map_name = "map_categories";

        $table_map_fieldgroup = "map_fieldgroups";

        $table_map_field = "map_fields";

        //--#1-- SELECT nessesary data from k2 categories table
        $j2_select_category_query = "SELECT * from $j2_table_categories";

        $j2_select_item_query = "SELECT * from $j2_table_items";

        $data_name = query::$joomla2->getColumnMultiData($j2_select_category_query, "name");

        $data_id = query::$joomla2->getColumnMultiData($j2_select_category_query, "id");

        $data_alias = query::$joomla2->getColumnMultiData($j2_select_category_query, "alias");

        $data_description = query::$joomla2->getColumnMultiData($j2_select_category_query, "description");

        $data_parent = query::$joomla2->getColumnMultiData($j2_select_category_query, "parent");

        $data_extra_fields_group = query::$joomla2->getColumnMultiData($j2_select_category_query, "extraFieldsGroup");

        $data_published = query::$joomla2->getColumnMultiData($j2_select_category_query, "published");

        $data_language = query::$joomla2->getColumnMultiData($j2_select_category_query, "language");
        //this query for get hits of category
        $data_catid = query::$joomla2->getColumnMultiData($j2_select_item_query, "catid");
        $data_hits = query::$joomla2->getColumnMultiData($j2_select_item_query, "hits");

        $data_field_j2_id = query::$joomla2->getColumnMultiData("SELECT * from $j2_table_fields", "id");

        $data_field_j2_group = query::$joomla2->getColumnMultiData("SELECT * from $j2_table_fields", "group");

        ////////////////////////////////////////////////
        $category_count = query::$joomla2->getColumnData("SELECT COUNT(id) as count_id from $j2_table_categories", "count_id");


        query::$joomla4->resetAutoIncrement($j4_table_categories);
        query::$joomla4->resetAutoIncrement($j4_table_assets);

        //if map category table not exist,create a table

        if (query::$joomla4->checkExistTable($table_map_name) === null) {
            query::$joomla4->createTable("CREATE TABLE $table_map_name (id int NOT NULL auto_increment,j2_id int NOT NULL,j4_id int NOT NULL ,j4_asset_id int,name varchar(225),primary key(id) );");
        } else {
            query::$joomla4->resetAutoIncrement($table_map_name);
        }

        // --------- add category in category table -------------
        for ($i = 0; $i < $category_count; $i++) {


            if ($data_parent[$i] !== 0) {
                $asset_level = 3;
                $category_level = 2;
            } else {
                $asset_level = 2;
                $category_level = 1;
            }

            //--#2-- find lft in category table in joomla4
            $category_lft = query::$joomla4->getColumnData("SELECT max(rgt) as max_rgt from $j4_table_categories where `level`=$category_level", "max_rgt") + 1;

            $category_rgt = $category_lft + 1;

            // insert into map table
            $asset_id = query::$joomla4->getColumnData("SELECT max(id) as max_id from $j4_table_assets", "max_id") + 1;

            $category_id = query::$joomla4->getColumnData("SELECT max(id) as max_id from $j4_table_categories", "max_id") + 1;

            // --#3--
            //alias in joomla4 should be lower case
            $path = strtolower($data_alias[$i]);

            $json_params = '{"category_layout":"","image":"","image_alt":""}';
            $json_metadata = '{"author":"","robots":""}';

            // --#7 -- ----------variables for asset table ------------

            $asset_lft = query::$joomla4->getColumnData("SELECT max(rgt) as max_rgt from $j4_table_assets where `level`=$asset_level ", "max_rgt") + 1;

            $asset_rgt = $asset_lft + 1;

            $asset_name = "com_content.category." . $category_id;

            //--#3-- ---------INSERT INTO map table----------
            $parametter_insert_map[':j2_id'] = $data_id[$i];
            $parametter_insert_map[':j4_id'] = $category_id;
            $parametter_insert_map[':j4_asset_id'] = $asset_id;
            $parametter_insert_map[':name'] = $data_name[$i];

            query::$joomla4->Insert("INSERT INTO $table_map_name (j2_id,j4_id,j4_asset_id,`name`) VALUES(:j2_id,:j4_id,:j4_asset_id,:name)", $parametter_insert_map);

            //--#4-- ---------INSERT INTO joomla4 field table------- 

            $parametter_insert_category[':asset_id'] = $asset_id;
            $parametter_insert_category[':parent_id'] = 1;
            $parametter_insert_category[':lft'] = $category_lft;
            $parametter_insert_category[':rgt'] = $category_rgt;
            $parametter_insert_category[':level'] = $category_level;
            $parametter_insert_category[':path'] = $path;
            $parametter_insert_category[':extension'] = 'com_content';
            $parametter_insert_category[':title'] = $data_name[$i];
            $parametter_insert_category[':alias'] = $data_alias[$i];
            $parametter_insert_category[':note'] = '';
            $parametter_insert_category[':description'] = $data_description[$i];
            $parametter_insert_category[':published'] = $data_published[$i];
            $parametter_insert_category[':access'] = 1;
            $parametter_insert_category[':params'] = $json_params;
            $parametter_insert_category[':metadesc'] = '';
            $parametter_insert_category[':metakey'] = '';
            $parametter_insert_category[':metadata'] = $json_metadata;
            $parametter_insert_category[':created_user_id'] = query::$user_id;
            $parametter_insert_category[':created_time'] = date("Y-m-d h:i:s");
            $parametter_insert_category[':modified_user_id'] = query::$user_id;
            $parametter_insert_category[':modified_time'] = date("Y-m-d h:i:s");
            $parametter_insert_category[':hits'] = 0;
            $parametter_insert_category[':language'] = $data_language[$i];
            $parametter_insert_category[':version'] = 1;

            // after this insert : update asset_id,parent_id,hits,path
            query::$joomla4->Insert("INSERT INTO $j4_table_categories (asset_id,parent_id,lft,rgt,`level`,`path`,extension,title,alias,note,`description`,published,access,params,metadesc,metakey,metadata,created_user_id,created_time,modified_user_id,modified_time,hits,`language`,`version`) VALUES (:asset_id,:parent_id,:lft,:rgt,:level,:path,:extension,:title,:alias,:note,:description,:published,:access,:params,:metadesc,:metakey,:metadata,:created_user_id,:created_time,:modified_user_id,:modified_time,:hits,:language,:version)",$parametter_insert_category);

            //--#4-- check for update rgt of root in category table

            $root_rgt = query::$joomla4->getColumnData("SELECT * from $j4_table_categories where title='ROOT';", "rgt");

            if ($category_rgt >= $root_rgt) {
                $new_root_rgt = $category_rgt + 1;
                $parametter_update_category['root_rgt'] = $new_root_rgt;

                query::$joomla4->Insert("UPDATE $j4_table_categories set rgt=:root_rgt where title='ROOT'",$parametter_update_category);
            }

            //--#4-- ---------INSERT INTO joomla4 assets table------- 

            $parametter_insert_assets[':parent_id'] = 8;
            $parametter_insert_assets[':lft'] = $asset_lft;
            $parametter_insert_assets[':rgt'] = $asset_rgt;
            $parametter_insert_assets[':level'] = $asset_level;
            $parametter_insert_assets[':name'] = $asset_name;
            $parametter_insert_assets[':title'] = $data_name[$i];
            $parametter_insert_assets[':rules'] = '{}';

            query::$joomla4->Insert("INSERT INTO $j4_table_assets (parent_id,lft,rgt,`level`,`name`,title,rules) VALUES (:parent_id,:lft,:rgt,:level,:name,:title,:rules);", $parametter_insert_assets);

            // if ($data_extra_fields_group[$i] !== 0) {

            //     $category_id = query::$joomla4->getColumnData("SELECT * from $table_map_name where j2_id=$data_id[$i]", "j4_id");

            //     for ($j = 0; $j < $field_count; $j++) {

            //         if ($data_extra_fields_group[$i] === $data_field_j2_group[$j]) {
            //             $data_field_j4_id = query::$joomla4->getColumnData("SELECT * from $table_map_field where j2_id=$data_field_j2_id[$j]", "j4_id");

            //             query::$joomla4->Insert("insert into $j4_table_field_category values($data_field_j4_id,$category_id)");
            //         }
            //     }
            // }

            if ($data_extra_fields_group[$i] !== 0) {

                $j2_field_id = query::$joomla2->getColumnMultiData("SELECT * from $j2_table_fields where `group`=$data_extra_fields_group[$i]", "id");

                // echo "<hr/>";
                // echo var_dump($j2_field_id);
                // echo "category_id" . $category_id;
                // echo "count" . count($j2_field_id);
                // echo "< hr/>";

                for ($j = 0; $j < count($j2_field_id); $j++) {

                    $j4_field_id = query::$joomla4->getColumnData("SELECT * from $table_map_field where j2_id=$j2_field_id[$j]", "j4_id");

                    //--#4-- ---------INSERT INTO joomla4 field_category table------- 
                    $parametter_insert_field_category['field_id']=$j4_field_id;
                    $parametter_insert_field_category['category_id']=$category_id;

                    query::$joomla4->Insert("INSERT INTO $j4_table_field_category (field_id,category_id) VALUES (:field_id,:category_id)",$parametter_insert_field_category);
                }
            }
        }

        // ------------- update hits and parent_id in joomla4 category table -------------

        for ($i = 0; $i < $category_count; $i++) {


            // --#8-- update hits

            $category_id = query::$joomla4->getColumnData("SELECT * from $table_map_name where j2_id=$data_id[$i]", "j4_id");


            // --#5-- update parent_id in asset table and category table

            if ($data_parent[$i] !== 0) {

                $parent_id = query::$joomla4->getColumnData("SELECT * from $table_map_name where j2_id=$data_parent[$i];", "j4_id");

                $parent_asset_id = query::$joomla4->getColumnData("SELECT * from $table_map_name where j2_id=$data_parent[$i];", "j4_asset_id");

                // ------UPDATE joomla4 category table -------------------------
                //update lft & rgt of parent of category $ parent of category
                //delete root_rgt element
                array_pop($parametter_update_category);
                $parametter_update_category[':parent_id']=$parent_id;
                $parametter_update_category[':category_id']=$category_id;

                query::$joomla4->Insert("UPDATE $j4_table_categories set parent_id=:parent_id where id=:category_id",$parametter_update_category);

                array_pop($parametter_update_category);
                
                query::$joomla4->Insert("update $j4_table_categories set rgt=rgt+2 where id = :parent_id;",$parametter_update_category);
                // ------UPDATE joomla4 asset table ------------------------

                $category_asset_id = query::$joomla4->getColumnData("SELECT * from $table_map_name where j2_id=$data_id[$i]", "j4_asset_id");

                $parametter_update_asset[':parent_asset_id']=$parent_asset_id;
                $parametter_update_asset[':category_asset_id']=$category_asset_id;
                                
                query::$joomla4->Insert("update $j4_table_assets set parent_id=:parent_asset_id where id=:category_asset_id",$parametter_update_asset);

                array_pop($parametter_update_asset);

                //update asset_id of parent
                query::$joomla4->Insert("update $j4_table_assets set rgt=rgt+2 where id=:parent_asset_id",$parametter_update_asset);
            }
        }
    }
}
