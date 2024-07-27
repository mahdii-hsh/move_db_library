<?php

require_once './query.php';

class article
{

    static function add_atricle($j2_table_items, $j2_table_category, $j2_table_fields, $j4_table_content, $j4_table_category, $j4_table_assets, $j4_table_fields, $j4_table_field_values)
    {
        

        // --#1-- get data
        $j2_select_category_query = "select * from $j2_table_items where id=2010";

        $data_name = query::$joomla2->getColumnData($j2_select_category_query, "title");

        $data_alias = query::$joomla2->getColumnData($j2_select_category_query, "alias");

        $data_catid = query::$joomla2->getColumnData($j2_select_category_query, "catid");

        $data_published = query::$joomla2->getColumnData($j2_select_category_query, "published");

        $data_introtext = query::$joomla2->getColumnData($j2_select_category_query, "introtext");

        $data_fulltext = query::$joomla2->getColumnData($j2_select_category_query, "fulltext");

        $data_extra_fields = query::$joomla2->getColumnData($j2_select_category_query, "extra_fields");

        $data_extra_fields_search = query::$joomla2->getColumnData($j2_select_category_query, "extra_fields_search");

        $data_ordering = query::$joomla2->getColumnData($j2_select_category_query, "ordering");

        $data_metadesc = query::$joomla2->getColumnData($j2_select_category_query, "metadesc");

        $data_metadata = query::$joomla2->getColumnData($j2_select_category_query, "metadata");

        $data_access = query::$joomla2->getColumnData($j2_select_category_query, "access");

        $data_hits = query::$joomla2->getColumnData($j2_select_category_query, "hits");

        $data_language = query::$joomla2->getColumnData($j2_select_category_query, "language");

        $data_featured = query::$joomla2->getColumnData($j2_select_category_query, "featured");

        query::$joomla4->resetAutoIncrement($j4_table_content);
        query::$joomla4->resetAutoIncrement($j4_table_assets);

        for ($i = 0; $i < 1; $i++) {

            // --#2--

            $category_name = query::$joomla2->getColumnData("select * from $j2_table_category where id=$data_catid", "name");

            //id of category in joomla4 category table
            $category_id = query::$joomla4->getColumnData("select * from $j4_table_category where title='$category_name'", "id");

            $asset_id = query::$joomla4->getColumnData("select max(id) as max_id from $j4_table_assets", "max_id") + 1;

            $json_image='{"image_intro":"","image_intro_alt":"","float_intro":"","image_intro_caption":"","image_fulltext":"","image_fulltext_alt":"","float_fulltext":"","image_fulltext_caption":""}';

            $json_urls='{"urla":"","urlatext":"","targeta":"","urlb":"","urlbtext":"","targetb":"","urlc":"","urlctext":"","targetc":""}';

            $json_attribs='{"article_layout":"","show_title":"","link_titles":"","show_tags":"","show_intro":"","info_block_position":"","info_block_show_title":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_associations":"","flags":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_hits":"","show_noauth":"","urls_position":"","alternative_readmore":"","article_page_title":"","show_publishing_options":"","show_article_options":"","show_urls_images_backend":"","show_urls_images_frontend":""}';

            query::$joomla4->Insert("INSERT INTO $j4_table_content (asset_id,title,alias,introtext,`fulltext`,`state`,catid,created,created_by,created_by_alias,modified,modified_by,images,urls,attribs,`version`,ordering,metadesc,access,hits,metadata,featured,`language`,note,publish_up) values ($asset_id,'$data_name','$data_alias','$data_introtext','',1,$category_id,NOW(),926,'',NOW(),926,'$json_image','$json_urls','$json_attribs',1,$data_ordering,'$data_metadesc',1,$data_hits,'$data_metadata',$data_featured,'$data_language','',NOW())");

            $data_fields_array = json_decode($data_extra_fields);

            // --#3--

            //get id of article(book) 
            $article_id = query::$joomla4->getColumnData("select max(id) as max_id from $j4_table_content", "max_id");

            for ($i = 0; $i < count($data_fields_array); $i++) {

                // name of field
                $field_name = query::$joomla2->getColumnData("select * from $j2_table_fields where id=" . $data_fields_array[$i]->id, "name");

                //id of field in joomla4 fields table
                $field_id=query::$joomla4->getColumnData("select * from $j4_table_fields where name='$field_name'","id");

                echo $field_name;

                $field_value=$data_fields_array[$i]->value;

                query::$joomla4->Insert("INSERT INTO $j4_table_field_values values($field_id,$article_id,'$field_value')");
            }

            $lft=query::$joomla4->getColumnData("select max(rgt) as max_rgt from $j4_table_assets where level=3","max_rgt")+1;

            $rgt=$lft+1;

            $category_asset_id = query::$joomla4->getColumnData("select * from $j4_table_assets where title='$category_name'", "id");

            query::$joomla4->Insert("insert into $j4_table_assets (parent_id,lft,rgt,level,name,title,rules) values($category_asset_id,$lft,$rgt,3,'com_content.$article_id','$data_name','{}')");


        }
    }
}
