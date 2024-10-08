<?php

require_once './query.php';
require_once './utils.php';

class article
{

    static function add_atricle($j2_table_items, $j4_table_content, $j4_table_assets, $j4_table_field_values, $j4_table_workflow_associations, $j4_table_featured)
    {

        $table_map_name = "map_articles";
        $table_map_category = "map_categories";
        $table_map_field = "map_fields";

        $parametter_insert_assets = [];
        $parametter_insert_article = [];
        $parametter_insert_workflow_associate = [];
        $parametter_insert_featured = [];
        $parametter_insert_field_value = [];

        // --#1-- get data
        $j2_select_item_query = "SELECT * from $j2_table_items";

        $data_name = query::$joomla2->getColumnMultiData($j2_select_item_query, "title");

        $data_id = query::$joomla2->getColumnMultiData($j2_select_item_query, "id");

        $data_alias = query::$joomla2->getColumnMultiData($j2_select_item_query, "alias");

        $data_catid = query::$joomla2->getColumnMultiData($j2_select_item_query, "catid");

        $data_published = query::$joomla2->getColumnMultiData($j2_select_item_query, "published");

        $data_introtext = query::$joomla2->getColumnMultiData($j2_select_item_query, "introtext");

        $data_fulltext = query::$joomla2->getColumnMultiData($j2_select_item_query, "fulltext");

        $data_extra_fields = query::$joomla2->getColumnMultiData($j2_select_item_query, "extra_fields");

        $data_extra_fields_search = query::$joomla2->getColumnMultiData($j2_select_item_query, "extra_fields_search");

        $data_ordering = query::$joomla2->getColumnMultiData($j2_select_item_query, "ordering");

        $data_metadesc = query::$joomla2->getColumnMultiData($j2_select_item_query, "metadesc");

        $data_metadata = query::$joomla2->getColumnMultiData($j2_select_item_query, "metadata");

        $data_trash = query::$joomla2->getColumnMultiData($j2_select_item_query, "trash");

        $data_access = query::$joomla2->getColumnMultiData($j2_select_item_query, "access");

        $data_hits = query::$joomla2->getColumnMultiData($j2_select_item_query, "hits");

        $data_language = query::$joomla2->getColumnMultiData($j2_select_item_query, "language");

        $data_featured = query::$joomla2->getColumnMultiData($j2_select_item_query, "featured");

        ////////////////////////////////////////////////
        $article_s = query::$joomla4->getColumnMultiData("select * from $j4_table_assets where name like '%com_content.article.%';", "name");

        $is_empty_table = (query::$joomla4->getColumnData("SELECT COUNT(*) AS total_rows FROM $j4_table_content", "total_rows")) === 0;

        if (!$is_empty_table) {
            query::$joomla4->resetAutoIncrement($j4_table_content, utils::maxNameAsset($article_s) + 1);
        } else {
            query::$joomla4->resetAutoIncrement($j4_table_content, utils::maxNameAsset($article_s));
        }
        query::$joomla4->resetAutoIncrement($j4_table_assets, 0);

        if (query::$joomla4->checkExistTable($table_map_name) === null) {
            query::$joomla4->defaultQuery("CREATE TABLE $table_map_name (id int NOT NULL auto_increment,j2_id int NOT NULL,j4_id int NOT NULL ,j4_asset_id int,name varchar(225),primary key(id) );");
        } else {
            query::$joomla4->resetAutoIncrement($table_map_name, 0);
        }


        $article_count = query::$joomla2->getColumnData("SELECT COUNT(id) as count_id from $j2_table_items", "count_id");
        for ($i = 0; $i < $article_count; $i++) {

            // --#2--

            //id of category in joomla4 category table
            $category_id = query::$joomla4->getColumnData("SELECT * from $table_map_category where j2_id=$data_catid[$i]", "j4_id");

            $asset_id = query::$joomla4->getColumnData("SELECT max(id) as max_id from $j4_table_assets", "max_id") + 1;

            //get id of article(book) 
            $article_id = query::$joomla4->getAutoIncrement($j4_table_content);

            $article_name = "com_content.$article_id";

            $json_image = '{"image_intro":"","image_intro_alt":"","float_intro":"","image_intro_caption":"","image_fulltext":"","image_fulltext_alt":"","float_fulltext":"","image_fulltext_caption":""}';

            $json_urls = '{"urla":"","urlatext":"","targeta":"","urlb":"","urlbtext":"","targetb":"","urlc":"","urlctext":"","targetc":""}';

            $json_attribs = '{"article_layout":"","show_title":"","link_titles":"","show_tags":"","show_intro":"","info_block_position":"","info_block_show_title":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_associations":"","flags":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_hits":"","show_noauth":"","urls_position":"","alternative_readmore":"","article_page_title":"","show_publishing_options":"","show_article_options":"","show_urls_images_backend":"","show_urls_images_frontend":""}';

            $article_alias = strtolower($data_alias[$i]);

            $asset_lft = query::$joomla4->getColumnData("SELECT max(rgt) as max_rgt from $j4_table_assets where `level`=3", "max_rgt") + 1;

            $asset_rgt = $asset_lft + 1;

            $parent_asset_id = query::$joomla4->getColumnData("SELECT * from $table_map_category where j2_id=$data_catid[$i]", "j4_asset_id");

            if ($data_trash === 1) {
                $data_state = -2;
            } else {
                $data_state = 1;
            }

            //--#3-- ---------INSERT INTO map table----------
            $parametter_insert_map[':j2_id'] = $data_id[$i];
            $parametter_insert_map[':j4_id'] = $article_id;
            $parametter_insert_map[':j4_asset_id'] = $asset_id;
            $parametter_insert_map[':name'] = $data_name[$i];

            query::$joomla4->Insert("INSERT INTO $table_map_name (j2_id,j4_id,j4_asset_id,`name`) VALUES(:j2_id,:j4_id,:j4_asset_id,:name)", $parametter_insert_map);

            //--#4-- ---------INSERT INTO joomla4 assets table------- 

            $parametter_insert_assets[':parent_id'] = $parent_asset_id;
            $parametter_insert_assets[':lft'] = $asset_lft;
            $parametter_insert_assets[':rgt'] = $asset_rgt;
            $parametter_insert_assets[':level'] = 3;
            $parametter_insert_assets[':name'] = $article_name;
            $parametter_insert_assets[':title'] = $data_name[$i];
            $parametter_insert_assets[':rules'] = '{}';


            query::$joomla4->Insert("INSERT INTO $j4_table_assets (parent_id,lft,rgt,`level`,`name`,title,rules) VALUES (:parent_id,:lft,:rgt,:level,:name,:title,:rules);", $parametter_insert_assets);


            //--#4-- ---------INSERT INTO joomla4 contect table------- 

            $parametter_insert_article[':asset_id'] = $asset_id;
            $parametter_insert_article[':title'] = $data_name[$i];
            $parametter_insert_article[':alias'] = $article_alias;
            $parametter_insert_article[':introtext'] = $data_introtext[$i];
            $parametter_insert_article[':fulltext'] = '';
            $parametter_insert_article[':state'] = $data_state;
            $parametter_insert_article[':catid'] = $category_id;
            $parametter_insert_article[':created'] = date("Y-m-d h:i:s");
            $parametter_insert_article[':created_by'] = query::$user_id;
            $parametter_insert_article[':created_by_alias'] = '';
            $parametter_insert_article[':modified'] = date("Y-m-d h:i:s");
            $parametter_insert_article[':modified_by'] = query::$user_id;
            $parametter_insert_article[':publish_up'] = date("Y-m-d h:i:s");
            $parametter_insert_article[':images'] = $json_image;
            $parametter_insert_article[':urls'] = $json_urls;
            $parametter_insert_article[':attribs'] = $json_attribs;
            $parametter_insert_article[':version'] = 1;
            $parametter_insert_article[':ordering'] = $data_ordering[$i];
            $parametter_insert_article[':metadesc'] = $data_metadesc[$i];
            $parametter_insert_article[':access'] = 1;
            $parametter_insert_article[':hits'] = $data_hits[$i];
            $parametter_insert_article[':metadata'] = $data_metadata[$i];
            $parametter_insert_article[':featured'] = $data_featured[$i];
            $parametter_insert_article[':language'] = $data_language[$i];
            $parametter_insert_article[':note'] = '';


            query::$joomla4->Insert("INSERT INTO $j4_table_content (asset_id,title,alias,introtext,`fulltext`,`state`,catid,created,created_by,created_by_alias,modified,modified_by,publish_up,images,urls,attribs,`version`,ordering,metadesc,access,hits,metadata,featured,`language`,note) VALUES (:asset_id,:title,:alias,:introtext,:fulltext,:state,:catid,:created,:created_by,:created_by_alias,:modified,:modified_by,:publish_up,:images,:urls,:attribs,:version,:ordering,:metadesc,:access,:hits,:metadata,:featured,:language,:note)", $parametter_insert_article);

            //------- INSERT INTO workflow association table -----------
            $parametter_insert_workflow_associate[':item_id'] = $article_id;
            $parametter_insert_workflow_associate[':stage_id'] = 1;
            $parametter_insert_workflow_associate[':extension'] = 'com_content.article';


            query::$joomla4->Insert("INSERT INTO $j4_table_workflow_associations(item_id,stage_id,extension) VALUES (:item_id,:stage_id,:extension)", $parametter_insert_workflow_associate);

            //------- INSERT INTO featured -----------

            if ($data_featured[$i] === 1) {
                $parametter_insert_featured[':content_id'] = $article_id;
                $parametter_insert_featured[':ordering'] = $data_ordering[$i];


                query::$joomla4->Insert("INSERT INTO $j4_table_featured (content_id,ordering) VALUES (:content_id,:ordering)", $parametter_insert_featured);
            }

            //------- INSERT values of fields -----------


            if ($data_extra_fields[$i] !== null && $data_trash[$i] !== 1) {
                $data_fields_array = json_decode($data_extra_fields[$i]);

                // --#3--
                echo "<hr />";
                echo $i;
                echo "<hr />";

                for ($j = 0; $j < count($data_fields_array); $j++) {


                    //id of field in joomla4 fields table
                    $field_id = query::$joomla4->getColumnData("SELECT * from $table_map_field where j2_id=" . $data_fields_array[$j]->id, "j4_id");

                    //------- INSERT INTO workflow association table -----------
                    $parametter_insert_field_value[':field_id'] = $field_id;
                    $parametter_insert_field_value[':item_id'] = $article_id;
                    $parametter_insert_field_value[':value'] = $data_fields_array[$j]->value;

                    query::$joomla4->Insert("INSERT INTO $j4_table_field_values(field_id,item_id,value) values(:field_id,:item_id,:value)", $parametter_insert_field_value);
                }
            }
        }
    }
}
