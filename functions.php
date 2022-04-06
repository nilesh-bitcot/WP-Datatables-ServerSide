<?php 

add_action('wp_enqueue_scripts', 'wp_enqueue_datatables_scripts');
function wp_enqueue_datatables_scripts(){
	wp_register_style(
		'wp-datatables',
		'https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css',
		null,
		wp_get_theme()->get('Version'),
		'all'
	);

	wp_register_script(
		'wp-datatables',
		'https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js',
		array('jquery'),
		wp_get_theme()->get( 'Version' ),
		false
	);
	
	wp_register_script(
		'wp-datatables-exe',
		get_stylesheet_directory_uri() . '/assets/js/datatables-script.js',
		array('jquery', 'wp-datatables'),
		time(), // on production I will replace it with 
		true
	);
	wp_enqueue_style('wp-datatables');
	wp_enqueue_script('wp-datatables');
	wp_enqueue_script('wp-datatables-exe');
	wp_localize_script(
		'wp-datatables-exe', 
		'datatables_obj',
		array(
			'ajaxurl' => admin_url( 'admin-ajax.php' )
		)
	);
}


add_action('wp_ajax_nopriv_get_products_for_table', 'get_products_for_table');
function get_products_for_table(){
	global $wpdb;
	$columns = array(
		0 => 'post_title',
		1 => 'post_status',
		2 => 'post_date'
	);
	
	$startfrom = sanitize_text_field( $_GET['start'] );
	$queryLength = sanitize_text_field( $_GET['length'] );
	$searchValue = '';
	if( $_GET['search'] ){
		if($_GET['search']['value']){
			$searchValue = trim( sanitize_text_field( $_GET['search']['value'] ) );
		}
	}

	$orderby = 'post_title';
	$order = 'asc';

	if($_GET['order'][0]['column']){
		$orderByColKey = intval( $_GET['order'][0]['column'] );
		$orderby = $columns[$orderByColKey];
	}
	if($_GET['order'][0]['dir']){
		$order = sanitize_text_field( $_GET['order'][0]['dir'] );
	}

	$query = "SELECT * FROM $wpdb->posts WHERE post_type = 'post' ORDER BY $orderby $order LIMIT $startfrom, $queryLength";
	$total_query = "SELECT * FROM $wpdb->posts WHERE post_type = 'post'";
	
	if( $searchValue ){
		$query = "SELECT * FROM $wpdb->posts WHERE post_type = 'post' AND post_title LIKE '%$searchValue%' ORDER BY $orderby $order LIMIT $startfrom, $queryLength";
		$total_query = "SELECT * FROM $wpdb->posts WHERE post_type = 'post' AND post_title LIKE '%$searchValue%'";
	}
	
	$total_posts = $wpdb->get_results( $total_query );
	$posts = $wpdb->get_results($query);

	$return_json = array();

	if($posts){
		foreach ($posts as $key => $post) {
			$row = array();
			$row['title'] = $post->post_title;
			$row['status'] = $post->post_status;
			$row['created_at'] = $post->post_date;
			$return_json[] = $row;		
		}
	}

	$resultArray = array(
						'data' => $return_json, 
						'total' => count($total_posts)
					);

	echo json_encode($resultArray);
	wp_die();
}