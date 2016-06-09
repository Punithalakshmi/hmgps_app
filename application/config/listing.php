<?php
/*
 * view - the path to the listing view that you want to display the data in
 * 
 * base_url - the url on which that pagination occurs. This may have to be modified in the 
 * 			controller if the url is like /product/edit/12
 * 
 * per_page - results per page
 * 
 * order_fields - These are the fields by which you want to allow sorting on. They must match
 * 				the field names in the table exactly. Can prefix with table name if needed
 * 				(EX: products.id)
 * 
 * OPTIONAL
 * 
 * default_order - One of the order fields above
 * 
 * uri_segment - this will have to be increased if you are paginating on a page like 
 * 				/product/edit/12
 * 				otherwise the pagingation will start on page 12 in this case 
 * 
 * 
 */
 



$config['admin_user_index'] = array(
	"view"		=> 	'admin/listing/listing',
	"init_scripts" => 'admin/listing/init_scripts',
	"advance_search_view" => 'admin/user/filter',
	"base_url"	=> 	'/admin/user/index/',
	"per_page"	=>	"20",
	"fields"	=> array(
							'default_id'=>array('name'=>'Default ID', 'data_type' => 'username', 'sortable' => TRUE, 'default_view'=>1),
							'phonenumber'=>array('name'=>'Phone', 'data_type' => 'string', 'sortable' => TRUE, 'default_view'=>1),
							'display_name'=>array('name'=>'Display Name', 'data_type' => 'string', 'sortable' => TRUE, 'default_view'=>1),
                            'email'=>array('name'=>'Email', 'data_type' => 'string', 'sortable' => TRUE, 'default_view'=>1),
                            'plan_id'=>array('name'=>'User Type', 'data_type' => 'user_type', 'sortable' => TRUE, 'default_view'=>1),
                            'last_updated'=>array('name'=>'Last Updated time', 'data_type' => 'last_updated', 'sortable' => TRUE, 'default_view'=>1),
                            'user_status'=>array('name'=>'Status', 'data_type' => 'userstatus', 'sortable' => TRUE, 'default_view'=>1)
						),
	"default_order"	=> "id",
	"default_direction" => "DESC"
);


$config['admin_plan_index'] = array(
	"view"		=> 	'admin/listing/listing',
	"init_scripts" => 'admin/listing/init_scripts',
	"advance_search_view" => 'admin/plan/filter',
	"base_url"	=> 	'/admin/plan/index/',
	"per_page"	=>	"20",
	"fields"	=> array(
							'planname'=>array('name'=>'Plan Name', 'data_type' => 'string', 'sortable' => TRUE, 'default_view'=>1),
							'validity'=>array('name'=>'Validity', 'data_type' => 'string', 'sortable' => TRUE, 'default_view'=>1)
						),
	"default_order"	=> "id",
	"default_direction" => "DESC"
);

$config['admin_plantype_index'] = array(
	"view"		=> 	'admin/listing/listing',
	"init_scripts" => 'admin/listing/init_scripts',
	"advance_search_view" => 'admin/plantype/filter',
	"base_url"	=> 	'/admin/plantype/index/',
	"per_page"	=>	"20",
	"fields"	=> array(
							'name'=>array('name'=>'Plan Type', 'data_type' => 'string', 'sortable' => TRUE, 'default_view'=>1),
							'plan_id'=>array('name'=>'Plan Name', 'data_type' => 'planname', 'sortable' => TRUE, 'default_view'=>1),
                            'cost'=>array('name'=>'Cost', 'data_type' => 'string', 'sortable' => TRUE, 'default_view'=>1),
                            'type'=>array('name'=>'Type', 'data_type' => 'string', 'sortable' => TRUE, 'default_view'=>1)
						),
	"default_order"	=> "id",
	"default_direction" => "DESC"
);

$config['admin_promo_index'] = array(
	"view"		=> 	'admin/listing/listing',
	"init_scripts" => 'admin/listing/init_scripts',
	"advance_search_view" => 'admin/promo/filter',
	"base_url"	=> 	'/admin/promo/index/',
	"per_page"	=>	"20",
	"fields"	=> array(
							'code'=>array('name'=>'Promo Code', 'data_type' => 'string', 'sortable' => TRUE, 'default_view'=>1),
							'expire'=>array('name'=>'Expire', 'data_type' => 'string', 'sortable' => TRUE, 'default_view'=>1),
                            'purchased_from'=>array('name'=>'Purchased From', 'data_type' => 'string', 'sortable' => TRUE, 'default_view'=>1)
						),
	"default_order"	=> "id",
	"default_direction" => "DESC"
);