<?php
/*
Plugin Name: WP Manage Order
Plugin URI: 
Description: Manage orders
Version: 1.1
Author: Sevy29
Author URI: 
*/


if ( !function_exists('add_action') ) :
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
endif;


class wp_dragdrop_cpt {

  var $dir;
  var $path;
  var $plugin_nonce;
  var $optionlabel;

  function __construct() {
  
    $this->dir = dirname( plugin_basename(__FILE__) );
	$this->path = plugins_url();
	$this->plugin_nonce = 'wpdragdrop';
	$this->optionlabel = 'wpdragdrop';

	add_action( 'wp_ajax_order_hook', array( $this, 'dragdrop_order_action') );

    //create submenu for setings
    add_action( 'admin_menu', array(&$this, 'dragdrop_custom_option') ); 
	   
	load_plugin_textdomain( 'wp-dragdrop-cpt', false, $this->dir . '/languages/'  );

		//load plugin css
		add_action( 'admin_head', array( $this, 'dragdrop_admin_custom_css') );
		
		//load plugin js
		add_action( 'admin_enqueue_scripts', array( $this, 'dragdrop_admin_custom_js') );


    if (in_array($this->admin_current_post_type(), $this->getOptions())) {
  
	  if (is_admin()) {

		//disable search filter
		add_action( 'parse_query', array( $this, 'dragdrop_filter_query') );
  
		//disable pagination
		add_action( 'edit_liste_per_page', create_function('', '-1;') );
  
		//table columns
		add_action( 'manage_' . $this->admin_current_post_type() . '_posts_columns', array( $this, 'set_table_columns_dragdrop') , 2); 
		add_action( 'manage_' . $this->admin_current_post_type() . '_posts_custom_column' , array( $this, 'manage_order_columns_dragdrop'), 10, 2 ); 
		
		//set order in query
		add_action('pre_get_posts', array( $this, 'set_menu_order_in_admin_dragdrop') );
	  
	  }
	  
    }

  }


  function dragdrop_custom_option() {
  	add_options_page( __( 'Order options', 'wp-dragdrop-cpt' ), __( 'Order options', 'wp-dragdrop-cpt' ), 'manage_options', 'order_options_setting', array($this, 'options_setting'));
  }


  function options_setting() {
	 
	$msg = '';
	 
	if (isset($_POST[$this->plugin_nonce . '_nonce'])) {

	  if ( !wp_verify_nonce( $_POST[$this->plugin_nonce . '_nonce'], $this->plugin_nonce ) ) {
		return;
	  }
	
	  update_option($this->optionlabel, serialize($_POST['cctype']));
	  
	  $msg = '<div id="message" class="updated below-h2"><p>' . __('Options successffully updated','wp-dragdrop-cpt') . '</p></div>';
	  	
	}
	
	$options = $this->getOptions();

	include_once( 'admin-panel.php' );
  }


  function getOptions() {

    $opt = '';

	if ( $options = get_option($this->optionlabel) ) {
	  if ($options != '') {
	    $opt = maybe_unserialize($options);
	  }
	}

    if ($opt == '') {
      $opt = array();
	}

	return $opt;

  }


  function admin_current_post_type() {
	global $post, $typenow, $current_screen;

	//we have a post so we can just get the post type from that
	if ( $post && $post->post_type )
	  return $post->post_type;
	  
	//check the global $typenow - set in admin.php
	elseif( $typenow )
	  return $typenow;
	  
	//check the global $current_screen object - set in sceen.php
	elseif( $current_screen && $current_screen->post_type )
	  return $current_screen->post_type;
	
	//lastly check the post_type querystring
	elseif( isset( $_REQUEST['post_type'] ) )
	  return sanitize_key( $_REQUEST['post_type'] );
  
    elseif (basename($_SERVER['PHP_SELF']) == 'edit.php')
	  return 'post';

	return '';
  }


  function dragdrop_filter_query( $query, $error = true ) {

    if ( is_search() ) {
      $query->is_search = false;
      $query->query_vars[s] = false;
      $query->query[s] = false;
    }

  }


  function dragdrop_admin_custom_js() {
    
	wp_register_script('dragdrop_custom-admin', $this->path . '/' . $this->dir . '/js/admin.js', array(), '1.0');
	wp_enqueue_script('dragdrop_custom-admin'); 

	//wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-sortable');
	//wp_enqueue_script('jquery-ui-widget');
	//wp_enqueue_script('jquery-ui-mouse');
    
	//for mobile device
    wp_enqueue_script('jquery-touch-punch');

  }


  function dragdrop_admin_custom_css() {
	echo '<link rel="stylesheet" type="text/css" media="all" href="' . $this->path . '/' . $this->dir . '/css/custom-admin.css" />';
  }


  function dragdrop_order_action(){
	
	global $wpdb;
	
	$data = $_POST['data'];
	
	if ($data != '') {
  
	  $data = str_replace('post-','',$data);
	  $ids = explode(',', $data);
	  

	  foreach($ids as $k=>$id) {
	
	    //default order
		if (!has_action('wp_order_custom_overwrite')) {
		  $wpdb->update($wpdb->prefix . 'posts', array('menu_order' => ($k+1)), array('ID' => $id));
		}				

	    do_action('wp_order_custom_overwrite', $k, $id);
		do_action('wp_order_custom_extra', $k, $id);

	  }
  
	}
  
	echo('ok');
  
	die();
  
  }


  function set_table_columns_dragdrop($columns) {

	$columns['menu_order_custom'] = __('Order');

	return $columns;
  }

  
  function manage_order_columns_dragdrop($column_name, $id) {
	  
	global $post;
	  
	switch ($column_name) {
  
	  case 'menu_order_custom':
		echo '<div class="btn-order">&nbsp;</div>';
		//echo '<div class="btn-order">' . $post->menu_order . '</div>';
	  break;
  
  
	}
  
  }


  function set_menu_order_in_admin_dragdrop( $wp_query ) {
    $wp_query->set( 'orderby', 'menu_order' );
    $wp_query->set( 'order', 'ASC' );
  }

	
}

$wp_dragdrop_cpt = new wp_dragdrop_cpt();


?>