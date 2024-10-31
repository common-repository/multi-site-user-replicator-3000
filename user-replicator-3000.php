<?php
/*
Plugin Name: Multi-Site User Replicator 3000
Plugin URI: 
Description: Adds the abilty to add or subtract a user from all sites.
Author: jroakes
Version: 0.1
Author URI: http://visiblecompany.com

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 
 Special Thanks: Justin Tadlock for a great article and Brent Shepherd for creating another plugin that I coould hack code from :-)
 
 
*/ 


//-------Them Hooks ------------------------------------------------//

add_action( 'edit_user_profile', 'jr_mu_user_options' ); //Show new user options
add_action( 'edit_user_profile_update', 'jr_add_users_to_sites' ); //Save new user options






//-------Action Functions ------------------------------------------------//




//---Add The Options to the Edit User Page ---------------------//

function jr_mu_user_options($user){


if ( is_multisite() && is_network_admin() && ! IS_PROFILE_PAGE && current_user_can( 'manage_network_options' ) ) {

jr_mu_user_update_msg($user->id, '');//Update status message

$result = "<br /><br/><h3>Multi-Site User Replicator 3000</h3>\n";
$result .= "<p>Please use with caution. You may want to take a Database backup before selecting here.</p>\n";
$result .= "<table class='form-table'>\n";

if (!get_the_author_meta( 'add_all_sites', $user->ID )){
//Add To All Sites checkbox
$result .= "<tr><th><label for='role'>Add User to All Sites</label></th>\n";
$result .= "<td width='80' >\n";
$result .= "<label><input type='checkbox' id='add_all_sites' name='add_all_sites' value='1' ". checked(get_the_author_meta( 'add_all_sites', $user->ID )) . "/>  Add User</label>\n";
$result .= "</td>\n";
// Add Role selection
$result .= "<td>\n";
$result .= "<label for='all_sites_role'> Select Global Role </label>";
$result .= "<select name='all_sites_role' id='all_sites_role'>\n";
$result .= "<option value='none'>-- None --</option>";
$result .= jr_dropdown_roles(esc_attr( get_the_author_meta( 'all_sites_role', $user->ID ) ));
$result .= "</select>\n";
$result .="</td>\n</tr>\n";
} else if (!get_the_author_meta( 'rem_all_sites', $user->ID )){
//Remove From All Sites checkbox
$result .= "<tr><th><label for='role'>Remove User From All Sites</label></th>\n";
$result .= "<td width='80' >\n";
$result .= "<label><input type='checkbox' id='rem_all_sites' name='rem_all_sites' value='1' ". checked(get_the_author_meta( 'rem_all_sites',$user->ID ) ). "/>  Del User</label>\n";
$result .= "</td>\n";
$result .= "<td>\n";
$result .= "<label for='all_sites_role'> Global Role </label>";
$result .= "<input type='text' readonly name='all_sites_role' id='all_sites_role' value='". esc_attr( get_the_author_meta( 'all_sites_role', $user->ID ) )."' />\n";
$result .="</td>\n</tr>\n";

} else {
$result .= "<tr><th><span style='color:#AE1B22; font-weight:bold;' >ERROR:</span></th>\n";
$result .= "<td>\n";
$result .= "<p>Something is wrong here.  Please check your Database tables.  Somehow both Add All Sites(add_all_sites) AND Delete All Sites(rem_all_sites) options are selected for this user.\n";
$result .= "</td>\n</tr>\n";
}
$result .="</table>\n";

$result .="<p><span style ='font-weight:bold;'>Status:</span> ". get_the_author_meta( 'all_sites_msg', $user->ID )."</p> <br /> <br />";
echo $result;
	
	jr_mu_user_error_flag($user->id, 0);
	
	

}else{


}
}



//---Update The Options on the User Page ---------------------//

function jr_mu_user_options_upd($user_id, $a, $b, $c) {


if ( is_multisite() && is_network_admin() && ! IS_PROFILE_PAGE && current_user_can( 'manage_network_options' ) ) {

	update_usermeta( $user_id, 'add_all_sites', $a );
	update_usermeta( $user_id , 'all_sites_role', $b );
	update_usermeta( $user_id , 'rem_all_sites', $c );
	
	} else {
	
	return false;
	
	}
	
}


//---Update The Error Flag ---------------------//

function jr_mu_user_error_flag($user_id, $status) {


if ( is_multisite() && is_network_admin() && ! IS_PROFILE_PAGE && current_user_can( 'manage_network_options' ) ) {

	if ( $status == 'on' ){
		update_usermeta( $user_id , 'all_sites_error', 1 );
		
	} elseif (get_the_author_meta( 'all_sites_error', $user_id ) && $status == 'off'){
	
	update_usermeta( $user_id , 'all_sites_error', 0 );
	update_usermeta( $user_id , 'all_sites_msg', '' );
	
	} else {
	
	update_usermeta( $user_id , 'all_sites_error', 0 );
	
	} 	
}

}


//---Show Update Message ---------------------//

function jr_mu_user_update_msg($user_id, $err ) {

	if (!$err){
	$site_count = jr_user_sites_count($user_id ); //[total] and [count]	
	$num = $site_count['count'];
	$tot = $site_count['total'];
	}
	
	
	if ($err == 'role_miss'){
	
	$text = "<span style='color:#AE1B22;' >ERROR: Please specify a role before adding user to sites.</span>";
	
	} elseif ($num > 0){
		
		$text = 'This user is currently a member of <strong>' . $num . '</strong> out of <strong>' . $tot . '</strong> sites';
		
	} elseif ($num == 0) {
	
		$text = 'This user is not a member of any sites';
		
	} 
	
	update_usermeta( $user_id , 'all_sites_msg', $text );
}




//---Add the User to the Blogs on User Edit Save ---------------------//

function jr_add_users_to_sites($user_id ){

if ( is_multisite() && is_network_admin() && ! IS_PROFILE_PAGE && current_user_can( 'manage_network_options' ) ) {


 if ( $_POST['add_all_sites']){
 
 	if ($_POST['all_sites_role'] =='none'){
 	
		jr_mu_user_update_msg($user_id,'role_miss');   		
		jr_mu_user_options_upd($user_id, '', 'none', '');
		jr_mu_user_error_flag($user_id, 1);
		return; 
 		}
 	
		
	foreach( jr_get_blog_list( 0, 'all' ) as $key => $blog ) { 
		
		if( is_user_member_of_blog( $user_id, $blog[ 'blog_id' ] ) )
			continue;

		switch_to_blog( $blog[ 'blog_id' ] );

		$role = $_POST['all_sites_role'];

		if( $role )
			add_user_to_blog( $blog[ 'blog_id' ], $user_id, $role );

		restore_current_blog();
	}
	
	jr_mu_user_options_upd($user_id, $_POST['add_all_sites'], $_POST['all_sites_role'], '');
	jr_mu_user_update_msg($user_id, '');//Update status message
	
 } else if ( $_POST['rem_all_sites']) {
 
 
 	foreach( jr_get_blog_list( 0, 'all' ) as $key => $blog ) { 

		if( !is_user_member_of_blog( $user_id, $blog[ 'blog_id' ] ) )
			continue;

		switch_to_blog( $blog[ 'blog_id' ] );

		$role = $_POST['all_sites_role'];

		if( $role )
			remove_user_from_blog( $user_id, $blog[ 'blog_id' ], '' );

		restore_current_blog();
	}
	
	jr_mu_user_options_upd($user_id, '', 'none', $_POST['rem_all_sites']);
	jr_mu_user_update_msg($user_id, '');//Update status message
} else {

	
	return false;
}

	

}//End MS IF

}//End Function








//-------Reference Functions ------------------------------------------------//

//---Count Sites Currently a Member Of ---------------------//

function jr_user_sites_count($user_id ){

if ( is_multisite() && is_network_admin() && ! IS_PROFILE_PAGE && current_user_can( 'manage_network_options' ) ) {

		foreach( jr_get_blog_list( 0, 'all' ) as $key => $blog ) { 
		$count_s++;
		if( is_user_member_of_blog( $user_id, $blog[ 'blog_id' ] ) )
			$count_us++;
		}
		restore_current_blog();
		$arr = array("total" => $count_s, "count" => $count_us);
		
		return $arr;
}

}


//---Get the Roles in a Dropdown List ---------------------//

function jr_dropdown_roles( $selected = false ) {
      $p = '';
      $r = '';
  	 global $wp_roles; 
  	 
  	 $editable_roles = $wp_roles->roles;
  
     foreach ( $editable_roles as $role => $details ) {
          $name = translate_user_role($details['name'] );
         if ( $selected == $role ) // preselect specified role
             $p = "\n\t<option selected='selected' value='" . esc_attr($role) . "'>$name</option>";
          else
              $r .= "\n\t<option value='" . esc_attr($role) . "'>$name</option>";
    }
    return $p . $r;
}


//---Get a List of All the Blogs ---------------------//

// A Copy of the WPMU deprecated get_blog_list function. Except this function gets all blogs, even if they are marked as mature and private
function jr_get_blog_list( $start = 0, $num = 10 ) {
	global $wpdb;

	$blogs = $wpdb->get_results( $wpdb->prepare( "SELECT blog_id, domain, path FROM $wpdb->blogs WHERE site_id = %d AND archived = '0' AND spam = '0' AND deleted = '0' ORDER BY registered DESC", $wpdb->siteid ), ARRAY_A );

	foreach ( (array) $blogs as $details ) {
		$blog_list[ $details[ 'blog_id' ] ] = $details;
		$blog_list[ $details[ 'blog_id' ] ]['postcount'] = $wpdb->get_var( "SELECT COUNT(ID) FROM " . $wpdb->get_blog_prefix( $details['blog_id'] ). "posts WHERE post_status='publish' AND post_type='post'" );
	}
	unset( $blogs );
	$blogs = $blog_list;

	if ( false == is_array( $blogs ) )
		return array();

	if ( $num == 'all' )
		return array_slice( $blogs, $start, count( $blogs ) );
	else
		return array_slice( $blogs, $start, $num );
}
?>