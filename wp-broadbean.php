<?php
/*
Plugin Name: WP-Broadbean
Plugin URI: https://github.com/visualspark/wp-broadbean
Description: Provides API integration with Broadbean Job Adder
Version: 0.8.0.0
Author: Stephen Macchia
Author URI: http://visualspark.com.au
License: MIT
License URI: http://opensource.org/licenses/MIT
*/

class wp_broadbean {
    public function __construct(){
        if(is_admin()){
        	add_action('admin_init', array($this, 'page_init'));
	    	add_action('admin_menu', array($this, 'add_plugin_page'));	    
		}
    }
    

    public function add_plugin_page(){
		/******************************************
		 This page will be under "Settings"
		*******************************************/
		add_options_page('Settings Broadbean', 'WP-Broadbean', 'manage_options', 'wp_broadbean_slug', array($this, 'create_admin_page'));
    }
	
	
	
    public function create_admin_page(){
    ?>
		<div class="wrap">
	    <?php screen_icon(); ?>
	    <h2>WP-Broadbean</h2>
		    <form method="post" action="options.php">
		        <?php
					/******************************************
					 This prints out all hidden setting fields
					*******************************************/
			    	settings_fields('test_option_group');	
			    	do_settings_sections('wp_broadbean_slug');
				?>
		        <?php submit_button(); ?>
		    </form>
		</div>
	<?php
    }
	
    public function page_init(){		
		register_setting('test_option_group', 'array_key', array($this, 'check_ID'));
        add_settings_section('setting_section_id', 'Setting', array($this, 'print_section_info'), 'wp_broadbean_slug');
		
		add_settings_field('wp_broadbean_api_availibility_id', 'Turn API', array($this, 'create_wp_broadbean_api_availibility_field'), 'wp_broadbean_slug', 'setting_section_id');
		add_settings_field('wp_broadbean_iprange_id', 'Restrict to IP range', array($this, 'create_wp_broadbean_iprange_field'), 'wp_broadbean_slug', 'setting_section_id');
		add_settings_field('wp_broadbean_users_id', 'Users', array($this, 'create_wp_broadbean_users_field'), 'wp_broadbean_slug', 'setting_section_id');
		add_settings_field('wp_broadbean_categories_id', 'Categories', array($this, 'create_wp_broadbean_categories_field'), 'wp_broadbean_slug', 'setting_section_id');
    }
	
    public function check_ID($input){
		$mid = $input['wp_broadbean_iprange_id'];
		if(get_option('wp_broadbean_iprange') === FALSE){
			add_option('wp_broadbean_iprange', $mid);
		}else{
			update_option('wp_broadbean_iprange', $mid);
		}

	    $availibility = $input['wp_broadbean_api_availibility_id'];
	    if(get_option('wp_broadbean_ipavailibility') === FALSE){
			add_option('wp_broadbean_ipavailibility', $availibility);
	    }else{
			update_option('wp_broadbean_ipavailibility', $availibility);
	    }

	    $availibility = $input['wp_broadbean_users_id'];
	    if(get_option('wp_broadbean_users') === FALSE){
			add_option('wp_broadbean_users', $availibility);
	    }else{
			update_option('wp_broadbean_users', $availibility);
	    }

	    $availibility = $input['wp_broadbean_categories_id'];
	    if(get_option('wp_broadbean_categories') === FALSE){
			add_option('wp_broadbean_categories', $availibility);
	    }else{
			update_option('wp_broadbean_categories', $availibility);
	    }
    }
	
    public function print_section_info(){
		print 'Enter your setting below:';
    }

    public function create_wp_broadbean_api_availibility_field() {
    	?>
    		<div>
    			<input type="radio" name="array_key[wp_broadbean_api_availibility_id]" <?=get_option('wp_broadbean_ipavailibility') == 0 ? 'checked=checked' : '';?> value="0"/> Off<br />
				<input type="radio" name="array_key[wp_broadbean_api_availibility_id]" <?=get_option('wp_broadbean_ipavailibility') == 1 ? 'checked=checked' : '';?> value="1"/> On
			</div>
		<?php
    }
	
    public function create_wp_broadbean_iprange_field() {
	?>
		<textarea cols="60" rows="5" name="array_key[wp_broadbean_iprange_id]" id="wp_broadbean_iprange"><?=get_option('wp_broadbean_iprange');?></textarea>
	<?php
    }

    public function create_wp_broadbean_users_field() {
    	$stack = array();

    	$roles = array('editor', 'author');

    	foreach ( $roles as $role ) {
			$users = get_users('role=' . $role);

			foreach ($users as $user) {
				array_push($stack, $user);
			}
		}
		if (count($stack) >0){
			echo "<select name='array_key[wp_broadbean_users_id]'>";
			foreach ( $stack as $s ) {
				$selected = get_option('wp_broadbean_users') == $s->user_login ? 'selected=selected' : '';
				echo "<option value='$s->user_login' $selected>$s->user_login</option>";
			}
			echo "</select>";
		}
		else echo "Please create a new user with a role of  Author or Editor";
		
    }

    public function create_wp_broadbean_categories_field() {
    	$args = array(
	        'orderby'   => 'name', 
	        'order'     => 'ASC',
	        'hierarchical' => 1,
	        'hide_empty'    => '0'
  		);

  		$categories = get_categories($args);

    	echo "<select name='array_key[wp_broadbean_categories_id]'>";
		foreach ( $categories as $category ) {
			$selected = get_option('wp_broadbean_categories') == $category->cat_ID ? 'selected=selected' : '';
			echo "<option value='$category->cat_ID' $selected>$category->name</option>";
		}
		echo "</select>";
    }
}

$wp_broadbean_setting = new wp_broadbean();

?>