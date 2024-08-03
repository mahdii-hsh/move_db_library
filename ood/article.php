<?php

require_once './query.php';

class article
{

    static function add_atricle($j2_table_items, $j2_table_category, $j2_table_fields, $j4_table_content, $j4_table_category, $j4_table_assets, $j4_table_fields, $j4_table_field_values,$j4_table_workflow_associations)
    {

        $table_map_name="map_articles";

        $table_map_category="map_categories";

        $table_map_field="map_fields";

        // --#1-- get data
        $j2_select_category_query = "SELECT * from $j2_table_items where id=2010";

        $data_name = query::$joomla2->getColumnMultiData($j2_select_category_query, "title");

        $data_alias = query::$joomla2->getColumnMultiData($j2_select_category_query, "alias");

        $data_catid = query::$joomla2->getColumnMultiData($j2_select_category_query, "catid");

        $data_published = query::$joomla2->getColumnMultiData($j2_select_category_query, "published");

        $data_introtext = query::$joomla2->getColumnMultiData($j2_select_category_query, "introtext");

        $data_fulltext = query::$joomla2->getColumnMultiData($j2_select_category_query, "fulltext");

        $data_extra_fields = query::$joomla2->getColumnMultiData($j2_select_category_query, "extra_fields");

        $data_extra_fields_search = query::$joomla2->getColumnMultiData($j2_select_category_query, "extra_fields_search");

        $data_ordering = query::$joomla2->getColumnMultiData($j2_select_category_query, "ordering");

        $data_metadesc = query::$joomla2->getColumnMultiData($j2_select_category_query, "metadesc");

        $data_metadata = query::$joomla2->getColumnMultiData($j2_select_category_query, "metadata");

        $data_access = query::$joomla2->getColumnMultiData($j2_select_category_query, "access");

        $data_hits = query::$joomla2->getColumnMultiData($j2_select_category_query, "hits");

        $data_language = query::$joomla2->getColumnMultiData($j2_select_category_query, "language");

        $data_featured = query::$joomla2->getColumnMultiData($j2_select_category_query, "featured");

        ////////////////////////////////////////////////
        $article_count = query::$joomla2->getColumnData("SELECT COUNT(id) as count_id from $j2_table_items", "count_id");

        query::$joomla4->resetAutoIncrement($j4_table_content);
        query::$joomla4->resetAutoIncrement($j4_table_assets);

        for ($i = 0; $i < $article_count; $i++) {

            // --#2--

            //id of category in joomla4 category table
            $category_id = query::$joomla4->getColumnData("SELECT * from $table_map_category where j2_id=$data_catid[$i]", "j4_id");

            $asset_id = query::$joomla4->getColumnData("SELECT max(id) as max_id from $j4_table_assets", "max_id") + 1;
    
            //get id of article(book) 
            $article_id=query::$joomla4->getColumnData("SELECT max(id) as max_id from $j4_table_content", "max_id") + 1;

            //------- add to workflow association table -----------
            query::$joomla4->Insert("INSERT INTO $j4_table_workflow_associations values($article_id,1,'com_content.article')");

            $json_image='{"image_intro":"","image_intro_alt":"","float_intro":"","image_intro_caption":"","image_fulltext":"","image_fulltext_alt":"","float_fulltext":"","image_fulltext_caption":""}';

            $json_urls='{"urla":"","urlatext":"","targeta":"","urlb":"","urlbtext":"","targetb":"","urlc":"","urlctext":"","targetc":""}';

            $json_attribs='{"article_layout":"","show_title":"","link_titles":"","show_tags":"","show_intro":"","info_block_position":"","info_block_show_title":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_associations":"","flags":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_hits":"","show_noauth":"","urls_position":"","alternative_readmore":"","article_page_title":"","show_publishing_options":"","show_article_options":"","show_urls_images_backend":"","show_urls_images_frontend":""}';

            $article_alias=strtolower($data_alias[$i]);

            query::$joomla4->Insert("INSERT INTO $j4_table_content (asset_id,title,alias,introtext,`fulltext`,`state`,catid,created,created_by,created_by_alias,modified,modified_by,publish_up,images,urls,attribs,`version`,ordering,metadesc,access,hits,metadata,featured,`language`,note,publish_up) values ($asset_id,'$data_name[$i]','$article_alias','$data_introtext[$i]','',1,$category_id,NOW(),926,'',NOW(),926,'$json_image','$json_urls','$json_attribs',1,$data_ordering[$i],'$data_metadesc[$i]',1,$data_hits[$i],'$data_metadata[$i]',$data_featured[$i],'$data_language[$i]','',NOW())");

            $data_fields_array = json_decode($data_extra_fields[$i]);

            // --#3--

            for ($j = 0; $j < count($data_fields_array); $j++) {

                //id of field in joomla4 fields table
                $field_id = query::$joomla4->getColumnData("SELECT * from $table_map_field where j2_id=". $data_fields_array[$j]->id, "j4_id");

                $field_value=$data_fields_array[$j]->value;

                query::$joomla4->Insert("INSERT INTO $j4_table_field_values values($field_id,$article_id,'$field_value')");
            }

            $lft=query::$joomla4->getColumnData("SELECT max(rgt) as max_rgt from $j4_table_assets where `level`=3","max_rgt")+1;

            $rgt=$lft+1;

            $parent_id = query::$joomla4->getColumnData("SELECT * from $table_map_category where j2_id=$data_catid[$i]", "j4_asset_id");

            query::$joomla4->Insert("INSERT INTO $j4_table_assets (parent_id,lft,rgt,`level`,`name`,title,rules) values($parent_id,$lft,$rgt,3,'com_content.$article_id','$data_name[$i]','{}')");

        }
    }
}
