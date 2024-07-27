<?php

require_once './query.php';
require_once './fieldgroup.php';
require_once './field.php';
require_once './category.php';
require_once './article.php';

query::$joomla2 = new query("172.18.0.2", "root", "root", "oldLibrary");
query::$joomla4 = new query("127.0.0.1", "db-mahdii", "1883hsh", "Library");


// fieldgroup::add_fieldgroup("i0kno_k2_extra_fields_groups","hcnov_assets","hcnov_fields_groups");

field::add_field("i0kno_k2_extra_fields", "i0kno_k2_extra_fields_groups", "hcnov_assets", "hcnov_fields", "hcnov_fields_groups");

// category::add_category("i0kno_k2_categories","i0kno_k2_items","hcnov_categories","hcnov_assets");

// article::add_atricle("i0kno_k2_items","i0kno_k2_categories","i0kno_k2_extra_fields","hcnov_content","hcnov_categories","hcnov_assets","hcnov_fields","hcnov_fields_values");