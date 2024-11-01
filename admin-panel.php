<?php
if ( !function_exists('add_action') ) :
  header('Status: 403 Forbidden');
  header('HTTP/1.1 403 Forbidden');
  exit();
endif;

?>

<div class="wrap">
		<div id="icon-options-general" class="icon32">
<br/>
</div>
<h2><?php echo __('Order options', 'wp-dragdrop-cpt'); ?></h2>
<?php echo $msg; ?>
<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">

<?php
    wp_nonce_field( $this->plugin_nonce, $this->plugin_nonce . '_nonce' );
?>	

  <table class="form-table">
    <tbody>
  

      <tr valign="top">
        <th scope="row">
          <label for="post_separator"><?php echo __('Post types', 'wp-dragdrop-cpt'); ?></label>
        </th>
        <td>
<?php
foreach( get_post_types() as $post_type_name ) {
  
  if (
       $post_type_name == 'reply' 
	|| $post_type_name == 'topic' 
	|| $post_type_name == 'revision' 
	|| $post_type_name == 'nav_menu_item' 
	|| $post_type_name == 'attachment'
  ) {
	continue;  
  }
	  
  if (!is_post_type_hierarchical($post_type_name)) {
	  
	$checked = '';
	if (in_array($post_type_name, $options)) {
	  $checked = ' checked="checked" ';
	}
	  	
    echo '<label><input ' . $checked . ' type="checkbox" name="cctype[]" value="' . $post_type_name . '" />' . $post_type_name . '</label><br />';
  }

}

?>
        </td>
      </tr>

    </tbody>
  </table>

  <p class="submit">
    <input id="submit" class="button button-primary" type="submit" value="<?php echo esc_attr(__('Save Changes')); ?>" name="submit">
  </p>
</form>

<h3><?php echo __('Hook sample', 'wp-dragdrop-cpt'); ?></h3>
<div class="postbox">
  <div class="inside">
<h4>You can past this code into your functions.php<br/>
This sample has been built to work with WPLM plugin and it tries to update the menu_order field of your items in all the languages not only the selected language</h4>
<pre>

function order_overwrite($k, $id) {

  global $wpdb;
 
  $wpdb->query("
	UPDATE " . $wpdb->prefix . "posts SET menu_order = " . intval($k+1) . " WHERE ID IN( 
	  SELECT element_id FROM " . $wpdb->prefix . "icl_translations WHERE trid IN (
		SELECT trid FROM " . $wpdb->prefix . "icl_translations WHERE element_id = " . intval($id) . "
	  )
	)
  "); 
	
}

if (function_exists('icl_object_id')) {
  add_action( 'wp_order_custom_overwrite', 'order_overwrite', 10, 2);
}
</pre>
  </div>
</div>
