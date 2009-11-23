<?php
/*
 * Plugin Name:   Category Manage Widget
 * Version:       1.0
 * Plugin URI:    http://wordpress.org/extend/plugins/category-manage-widget/
 * Description:   This plugin gives the flebility to effectively manage category in a sidebar with multiple instances possible. Adjust your settings <a href="options-general.php?page=category-manage-widget/category-manage-widget.php">here</a>.
 * Author:        MaxBlogPress
 * Author URI:    http://www.maxblogpress.com
 *
 * License:       GNU General Public License
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * 
 * Copyright (C) 2007 www.maxblogpress.com
 *
 * This is the improved version of "Breukie's Categories Widget" plugin by Arnold Breukhoven
 *
 */
$mbpcmw_path      = preg_replace('/^.*wp-content[\\\\\/]plugins[\\\\\/]/', '', __FILE__);
$mbpcmw_path      = str_replace('\\','/',$mbpcmw_path);
$mbpcmw_dir       = substr($mban_path,0,strrpos($mbpcmw_path,'/'));
$mbpcmw_siteurl   = get_bloginfo('wpurl');
$mbpcmw_siteurl   = (strpos($mbpcmw_siteurl,'http://') === false) ? get_bloginfo('siteurl') : $mbpcmw_siteurl;
$mbpcmw_fullpath  = $mbpcmw_siteurl.'/wp-content/plugins/'.$mbpcmw_dir.'';
$mbpcmw_fullpath  = $mbpcmw_fullpath.'category-manage-widget/';
$mbpcmw_abspath   = str_replace("\\","/",ABSPATH); 

define('MBP_CMW_ABSPATH', $mbpcmw_path);
define('MBP_CMW_LIBPATH', $mbpcmw_fullpath);
define('MBP_CMW_SITEURL', $mbpcmw_siteurl);
define('MBP_CMW_NAME', 'Category Manage Widget');
define('MBP_CMW_VERSION', '1.0');  
define('MBP_CMW_LIBPATH', $mbpcmw_fullpath);
global $wp_version;

if ($wp_version > '2.3') {
	

	function mbp_cmw_options() {
		add_options_page('Category Manage Widget', 'Category Manage Widget', 10, __FILE__, 'mbp_cmw_activate');
	} 
	
	function mbp_cmw_activate() {
		$mbp_cmw_activate = get_option('mbp_cmw_activate');
		$reg_msg = '';
		$mbp_cmw_msg = '';
		$form_1 = 'mbp_cmw_reg_form_1';
		$form_2 = 'mbp_cmw_reg_form_2';
			// Activate the plugin if email already on list
		if ( trim($_GET['mbp_onlist']) == 1 ) {
			$mbp_cmw_activate = 2;
			update_option('mbp_cmw_activate', $mbp_cmw_activate);
			$reg_msg = 'Thank you for registering the plugin. It has been activated'; 
		} 
		// If registration form is successfully submitted
		if ( ((trim($_GET['submit']) != '' && trim($_GET['from']) != '') || trim($_GET['submit_again']) != '') && $mbp_cmw_activate != 2 ) { 
			update_option('mbp_cmw_name', $_GET['name']);
			update_option('mbp_cmw_email', $_GET['from']);
			$mbp_cmw_activate = 1;
			update_option('mbp_cmw_activate', $mbp_cmw_activate);
		}
		if ( intval($mbp_cmw_activate) == 0 ) { // First step of plugin registration
			global $userdata;
			mbp_cmwRegisterStep1($form_1,$userdata);
		} else if ( intval($mbp_cmw_activate) == 1 ) { // Second step of plugin registration
			$name  = get_option('mbp_cmw_name');
			$email = get_option('mbp_cmw_email');
			mbp_cmwRegisterStep2($form_2,$name,$email);
		} else if ( intval($mbp_cmw_activate) == 2 ) { // Options page
				if ( trim($reg_msg) != '' ) {
					echo '<div id="message" class="updated fade"><p><strong>'.$reg_msg.'</strong></p></div>';
				}			
			}
		
		if($mbp_cmw_activate != '' && !$_GET['submit']) {
		?>
			
		<div class="wrap">
			<h2><?php echo MBP_CMW_NAME.' '.MBP_CMW_VERSION; ?></h2>
		<strong><img src="<?php echo MBP_AIT_LIBPATH;?>image/how.gif" border="0" align="absmiddle" /> <a href="http://wordpress.org/extend/plugins/category-manage-widget/other_notes/" target="_blank">How to use it</a>&nbsp;&nbsp;&nbsp;
				<img src="<?php echo MBP_AIT_LIBPATH;?>image/comment.gif" border="0" align="absmiddle" /> <a href="http://www.maxblogpress.com/forum/forumdisplay.php?f=32" target="_blank">Community</a></strong>
		<br/><br/>				
				
				<div id="message" class="updated fade">
					<p>
						<strong>You have already registered. Please go to the <a href="<?php echo MBP_CMW_SITEURL;?>/wp-admin/widgets.php">Widgets</a> section to enable and configure the widget.</strong>
					</p>
				</div>
		</div>	
		<?php
		}	
	}
	function widget_cmw( $args, $widget_args = 1 ) {
		extract( $args, EXTR_SKIP );
		if ( is_numeric($widget_args) )
			$widget_args = array( 'number' => $widget_args );
		$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
		extract( $widget_args, EXTR_SKIP );
	
		$options 			= get_option('widget_cmw');
		
		if ( !isset($options[$number]) )
			return;			
			
		//check if registered or not
		$mbp_cmw_activate 	= get_option('mbp_cmw_activate');				
		if ($mbp_cmw_activate == '') {
			echo "Please register in the admin panel to activate the `Category Manage Widget` widget";
		} else {			
			
	?>
		<?php echo $before_widget; ?>
		<div class="category_manage">
			
			<?php
			//for category output
				$title 				= empty($options[$number]['title']) ? __('Categories') : $options[$number]['title'];
				$orderby 			= empty($options[$number]['orderby']) ? 'ID' : $options[$number]['orderby'];
				$order 				= empty($options[$number]['order']) ? 'asc' : $options[$number]['order'];
				$style 				= empty($options[$number]['style']) ? 'list' : $options[$number]['style'];
				$child_of 			= empty($options[$number]['child_of']) ? '' : $options[$number]['child_of'];
				$feed 				= empty($options[$number]['feed']) ? '' : $options[$number]['feed'];
				$feed_image 		= empty($options[$number]['feed_image']) ? '' : $options[$number]['feed_image'];
				$exclude 			= empty($options[$number]['exclude']) ? '' : $options[$number]['exclude'];
				$include 			= empty($options[$number]['include']) ? '' : $options[$number]['include'];
				$title_li 			= empty($options[$number]['title_li']) ? '' : $options[$number]['title_li'];
				$show_option_all 	= empty($options[$number]['show_option_all'])  ? '0' : '1';
				$show_count 		= empty($options[$number]['show_count']) ? '0' : '1';
				$hide_empty 		= empty($options[$number]['hide_empty']) ? '0' : '1';
				$use_desc_for_title = empty($options[$number]['use_desc_for_title']) ? '0' : '1';
				$hierarchical 		= empty($options[$number]['hierarchical']) ? '0' : '1';
			
				if ( $hierarchical == '1' )
				{
					$child_of = '';
				}
				else
				{
					$child_of = '&child_of=' . $child_of;
				}	
				
				echo "<div class='" . $title . "'>" .  $title . "</div>";
				echo '<ul>';
						wp_list_categories('show_option_all=' . $show_option_all . 
											'&orderby=' . $orderby . 
											'&order=' . $order . 
											'&style=' . $style . 
											'&show_count=' . $show_count . 
											'&hide_empty=' . $hide_empty . 
											'&use_desc_for_title=' . $use_desc_for_title . $child_of . 		'&feed=' . $feed . 
											'&feed_image=' . $feed_image . 
											'&exclude=' . $exclude . 
											'&include=' . $include . 
											'&hierarchical=' . $hierarchical . 
											'&title_li=' . $title_li);
				echo '</ul>';	
			?>
		</div>
		<?php echo $after_widget; ?>
	<?php
		}//user registered or not
	}
	
	function widget_cmw_control( $widget_args = 1 ) {
		global $wp_registered_widgets;
		static $updated = false; // Whether or not we have already updated the data after a POST submit
	
		if ( is_numeric($widget_args) )
			$widget_args = array( 'number' => $widget_args );
		$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
		extract( $widget_args, EXTR_SKIP );
	
		// Data is stored as array:	 array( number => data for that instance of the widget, ... )
		$options = get_option('widget_cmw');
		if ( !is_array($options) )
			$options = array();
	
		// We need to update the data
		if ( !$updated && !empty($_POST['sidebar']) ) {
			// Tells us what sidebar to put the data in
			$sidebar = (string) $_POST['sidebar'];
	
			$sidebars_widgets = wp_get_sidebars_widgets();
			if ( isset($sidebars_widgets[$sidebar]) )
				$this_sidebar =& $sidebars_widgets[$sidebar];
			else
				$this_sidebar = array();
	
			foreach ( $this_sidebar as $_widget_id ) {
				if ( 'widget_cmw' == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number']) ) {
					$widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
					if ( !in_array( "cmw-$widget_number", $_POST['widget-id'] ) ) // the widget has been removed
						unset($options[$widget_number]);
				}
			}
	
			foreach ( (array) $_POST['widget-cmw'] as $widget_number => $widget_cmw ) {
				if ( !isset($widget_cmw['title']) && isset($options[$widget_number]) ) // user clicked cancel
					continue;
				
				$title 						= wp_specialchars( $widget_cmw['title'] );
				$orderby 					= $widget_cmw['orderby'] ;
				$order 						= $widget_cmw['order'] ;
				$child_of 					= $widget_cmw['child_of'];
				$feed 						= $widget_cmw['feed'];
				$feed_image 				= $widget_cmw['feed_image'];
				$exclude 					= implode(",", $_POST['exclude' . $widget_number]);
				$include 					= implode(",", $_POST['include' . $widget_number]);
				$title_li 					= $widget_cmw['title_li'];
				$style 						= $widget_cmw['style'];
				$hierarchical 				= $widget_cmw['hierarchical'];
				$hide_empty 				= $widget_cmw['hide_empty'];
				$show_count 				= $widget_cmw['show_count'];
				$use_desc_for_title 		= $widget_cmw['use_desc_for_title'];
				$show_option_all 			= $widget_cmw['show_option_all'];			
					
				$image 		= wp_specialchars( $widget_cmw['image'] );
				$alt 		= wp_specialchars( $widget_cmw['alt'] );
				$link 		= wp_specialchars( $widget_cmw['link'] );
				$new_window = isset( $widget_cmw['new_window'] );
				$options[$widget_number] 	= compact('image', 
														'alt', 
														'link', 
														'new_window',
														'title', 
														'orderby',
														'order', 
														'child_of',
														'feed',
														'feed_image',
														'exclude',
														'include',
														'title_li',
														'style',
														'hierarchical',
														'hide_empty',
														'show_count',
														'use_desc_for_title');			
			}
	
			update_option('widget_cmw', $options);
			$updated = true; // So that we don't go through this more than once
		}
		
		//print_r($options);
		if ( -1 == $number ) { 
			$title 						= '';
			$orderby					= '';
			$order 						= '';
			$child_of 					= '';
			$feed 						= '';
			$feed_image 				= '';
			$exclude 					= '';
			$include 					= '';
			$title_li 					= '';
			$style 						= '';
			$hierarchical 				= '';
			$hide_empty 				= '';
			$show_count 				= '';
			$use_desc_for_title 		= '';
			$show_option_all 			= '';		
	
			$image = '';
			$alt = '';
			$link = '';
			$new_window = '';
			$number = '%i%';
		} else {
			$title 						= attribute_escape($options[$number]['title']);
			$orderby 					= attribute_escape($options[$number]['orderby']);
			$order 						= attribute_escape($options[$number]['order']);
			$child_of 					= attribute_escape($options[$number]['child_of']);
			$feed 						= attribute_escape($options[$number]['feed']);
			$feed_image 				= attribute_escape($options[$number]['feed_image']);
			$exclude 					= attribute_escape($options[$number]['exclude']);
			$include 					= attribute_escape($options[$number]['include']);
			$title_li 					= attribute_escape($options[$number]['title_li']);
			$style 						= attribute_escape($options[$number]['style']);
			$hierarchical 				= attribute_escape($options[$number]['hierarchical']);
			$hide_empty 				= attribute_escape($options[$number]['hide_empty']);
			$show_count 				= attribute_escape($options[$number]['show_count']);
			$use_desc_for_title 		= attribute_escape($options[$number]['use_desc_for_title']);
			$show_option_all 			= attribute_escape($options[$number]['show_option_all']);		
	
			$image 		= attribute_escape($options[$number]['image']);
			$alt 		= attribute_escape($options[$number]['alt']);
			$link 		= attribute_escape($options[$number]['link']);
			$new_window = attribute_escape($options[$number]['new_window']);
		}
	?>
			<p>
				<label for="cmw-title-<?php echo $number; ?>">
					<?php _e('Title:'); ?>
					<input class="widefat" id="cmw-title-<?php echo $number; ?>" name="widget-cmw[<?php echo $number; ?>][title]" type="text" value="<?php echo $title; ?>" />
				</label>
			</p>		
			
			<p>
				<label for="cmw-orderby-<?php echo $number; ?>">
					<?php _e('Sort Options:'); ?>
					<select id="widget-cmw-orderby-<?php echo $number; ?>" name="widget-cmw[<?php echo $number; ?>][orderby]">
					<?php echo "<option value=\"\">Select</option>"; ?>
					<?php echo "<option value=\"id\"" . ($orderby=='id' ? " selected='selected'" : '') .">ID</option>"; ?>
					<?php echo "<option value=\"name\"" . ($orderby=='name' ? " selected='selected'" : '') .">Name</option>"; ?>
					</select>&nbsp; <select id="widget-cmw-order-<?php echo $number; ?>" name="widget-cmw[<?php echo $number; ?>][order]" value="<?php echo $order; ?>">
					<?php echo "<option value=\"\">Select</option>"; ?>
					<?php echo "<option value=\"asc\"" . ($order=='asc' ? " selected='selected'" : '') .">ASC</option>"; ?>
					<?php echo "<option value=\"desc\"" . ($order=='desc' ? " selected='selected'" : '') .">DESC</option>"; ?>
					</select>				
				</label>
			</p>			
			
<style type="text/css">
<!--
#wpcontent select {
	height:auto;
}
-->
</style>		
			<p>
				<label for="cmw-child_of-<?php echo $number; ?>">
					<?php _e('Child Of:'); ?>
					<select id="widget-cmw-child_of-<?php echo $number; ?>" name="widget-cmw[<?php echo $number; ?>][child_of]">
					<option value="">Select</option>
					<?php 
						global $wpdb;
						$query_cat = "SELECT
											a.term_id,
											a.name
									FROM
										". $wpdb->terms ." a
										INNER JOIN " . $wpdb->term_taxonomy ." b ON(a.term_id=b.term_id)
									WHERE 
										b.taxonomy='category'";
						$sql_cat   = mysql_query($query_cat);
						while($rs_cat	    = mysql_fetch_array($sql_cat)) {	 
					?>
					
					<option <?php if ($rs_cat['term_id'] == $child_of) { echo 'selected';}?> value="<?php echo $rs_cat['term_id'];?>">
						<?php echo $rs_cat['name'];?>
					</option>
					<?php } ?>
					</select>
				</label>
			</p>	
			
			<p>
				<label for="cmw-feed-<?php echo $number; ?>">
					<?php _e('Feed:'); ?>
					<input class="widefat" id="cmw-feed-<?php echo $number; ?>" name="widget-cmw[<?php echo $number; ?>][feed]" type="text" value="<?php echo $feed; ?>" />
				</label>
			</p>			
			
			<p>
				<label for="cmw-feed_image-<?php echo $number; ?>">
					<?php _e('Feed Image:'); ?>
					<input class="widefat" id="cmw-feed_image-<?php echo $number; ?>" name="widget-cmw[<?php echo $number; ?>][feed_image]" type="text" value="<?php echo $feed_image; ?>" />
				</label>
			</p>		
			
			<p>
				<label style="height:20px;" for="cmw-exclude-<?php echo $number; ?>">
					<?php _e('Exclude Categories:'); ?>		
					<?php
						//tweak for breaking cat id
						$exclude_vals = explode(",",$exclude);
						foreach($exclude_vals as $key=>$val) {
							$arr_exclude[] = $val;
						}
					?>
					
					<select id="cmw-exclude-<?php echo $number; ?>" name="exclude<?php echo $number;?>[]" multiple="multiple">
					<?php
						$query_cat = "SELECT
											a.term_id,
											a.name
									FROM
										". $wpdb->terms ." a
										INNER JOIN " . $wpdb->term_taxonomy ." b ON(a.term_id=b.term_id)
									WHERE 
										b.taxonomy='category'";
						$sql_cat   = mysql_query($query_cat);
						while($rs_cat	    = mysql_fetch_array($sql_cat)) {
							$sel = (in_array($rs_cat['term_id'], $arr_exclude)) ? ' selected="selected"':'';				
					?>						
						<option <?php echo $sel;?> value="<?php echo $rs_cat['term_id'];?>">
							<?php echo $rs_cat['name'];?>
						</option>
					<?php } ?>						
					</select>
				</label>
			</p>		
			
			<p>
				<label for="cmw-include-<?php echo $number; ?>">
					<?php _e('Include Categories:'); ?>
					<?php
						//tweak for breaking cat id
						$include_vals = explode(",",$include);
						foreach($include_vals as $key=>$val) {
							$arr_include[] = $val;
						}
					?>
					
					<select id="cmw-include-<?php echo $number; ?>" name="include<?php echo $number;?>[]" multiple="multiple">
					<?php
						$query_cat = "SELECT
											a.term_id,
											a.name
									FROM
										". $wpdb->terms ." a
										INNER JOIN " . $wpdb->term_taxonomy ." b ON(a.term_id=b.term_id)
									WHERE 
										b.taxonomy='category'";
						$sql_cat  = mysql_query($query_cat);
						while($rs_cat = mysql_fetch_array($sql_cat)) {
							$sel = (in_array($rs_cat['term_id'], $arr_include)) ? ' selected="selected"':'';				
					?>						
						<option <?php echo $sel;?> value="<?php echo $rs_cat['term_id'];?>">
							<?php echo $rs_cat['name'];?>
						</option>
					<?php } ?>						
					</select>
				</label>
			</p>		
			
			<p>
				<label for="cmw-title_li-<?php echo $number; ?>">
					<?php _e('Title li:'); ?>
					<input class="widefat" id="cmw-title_li-<?php echo $number; ?>" name="widget-cmw[<?php echo $number; ?>][title_li]" type="text" value="<?php echo $title_li; ?>" />
				</label>
			</p>			
			
			<p>
				<label for="cmw-style-<?php echo $number; ?>">
					<?php _e('Sort Options:'); ?>
					<select id="widget-cmw-style-<?php echo $number; ?>" name="widget-cmw[<?php echo $number; ?>][style]" value="<?php echo $style; ?>">
					<?php echo "<option value=\"\">Select</option>"; ?>
					<?php echo "<option value=\"list\"" . ($style=='list' ? " selected='selected'" : '') .">List</option>"; ?>
					<?php echo "<option value=\"none\"" . ($style=='none' ? " selected='selected'" : '') .">None</option>"; ?>
					</select>							
				</label>
			</p>		
			
			<p>
				<label for="cmw-hierarchical-<?php echo $number; ?>">
					<?php _e('Hierarchical:'); ?>
					<input id="widget-cmw-hierarchical-<?php echo $number; ?>" name="widget-cmw[<?php echo $number; ?>][hierarchical]" type="checkbox" <?php if ($hierarchical) echo 'checked="checked"'; ?> />
				</label> 
			</p>		
			
			<p>
				<label for="cmw-show_count-<?php echo $number; ?>">
					<?php _e('Show Post Counts:'); ?>
					<input id="widget-cmw-show_count-<?php echo $number; ?>" name="widget-cmw[<?php echo $number; ?>][show_count]" type="checkbox" <?php if ($show_count) echo 'checked="checked"'; ?> />
				</label> 
			</p>		
			
			<p>
				<label for="cmw-use_desc_for_title-<?php echo $number; ?>">
					<?php _e('Use Desc for Title:'); ?>
					<input id="widget-cmw-use_desc_for_title-<?php echo $number; ?>" name="widget-cmw[<?php echo $number; ?>][use_desc_for_title]" type="checkbox" <?php if ($use_desc_for_title) echo 'checked="checked"'; ?> />
				</label> 
			</p>		
	
			<p>
				<label for="cmw-hide_empty-<?php echo $number; ?>">
					<?php _e('Hide Empty Categories:'); ?>
					<input id="widget-cmw-hide_empty-<?php echo $number; ?>" name="widget-cmw[<?php echo $number; ?>][hide_empty]" type="checkbox" <?php if ($hide_empty) echo 'checked="checked"'; ?> />
				</label> 
			</p>
					
			<input type="hidden" id="widget-cmw-submit-<?php echo $number; ?>" name="widget-cmw[<?php echo $number; ?>][submit]" value="1" />
	<?php
	}
	
	// Registers each instance of widget on startup
	function widget_cmw_register() {
		if ( !$options = get_option('widget_cmw') )
			$options = array();
	
		$widget_ops = array('classname' => 'widget_cmw', 'description' => __('Category Management'));
		$control_ops = array( 'id_base' => 'cmw');
		$name = __(MBP_CMW_NAME);
	
		$registered = false;
		foreach ( array_keys($options) as $o ) {
			// Old widgets can have null values for some reason
			if ( !isset($options[$o]['image']) )
				continue;
	
			$id = "cmw-$o"; // Never never never translate an id
			$registered = true;
			wp_register_sidebar_widget( $id, $name, 'widget_cmw', $widget_ops, array( 'number' => $o ) );
			wp_register_widget_control( $id, $name, 'widget_cmw_control', $control_ops, array( 'number' => $o ) );
		}
	
		// If there are none, we register the widget's existance with a generic template
		if ( !$registered ) {
			wp_register_sidebar_widget( 'cmw-1', $name, 'widget_cmw', $widget_ops, array( 'number' => -1 ) );
			wp_register_widget_control( 'cmw-1', $name, 'widget_cmw_control', $control_ops, array( 'number' => -1 ) );
		}
	}
	
	
// Srart Registration.

/**
 * Plugin registration form
 */
function mbp_cmwRegistrationForm($form_name, $submit_btn_txt='Register', $name, $email, $hide=0, $submit_again='') {
	$wp_url = get_bloginfo('wpurl');
	$wp_url = (strpos($wp_url,'http://') === false) ? get_bloginfo('siteurl') : $wp_url;
	$plugin_pg    = 'options-general.php';
	$thankyou_url = $wp_url.'/wp-admin/'.$plugin_pg.'?page='.$_GET['page'];
	$onlist_url   = $wp_url.'/wp-admin/'.$plugin_pg.'?page='.$_GET['page'].'&amp;mbp_onlist=1';
	if ( $hide == 1 ) $align_tbl = 'left';
	else $align_tbl = 'center';
	?>
	
	<?php if ( $submit_again != 1 ) { ?>
	<script><!--
	function trim(str){
		var n = str;
		while ( n.length>0 && n.charAt(0)==' ' ) 
			n = n.substring(1,n.length);
		while( n.length>0 && n.charAt(n.length-1)==' ' )	
			n = n.substring(0,n.length-1);
		return n;
	}
	function mbp_cmwValidateForm_0() {
		var name = document.<?php echo $form_name;?>.name;
		var email = document.<?php echo $form_name;?>.from;
		var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
		var err = ''
		if ( trim(name.value) == '' )
			err += '- Name Required\n';
		if ( reg.test(email.value) == false )
			err += '- Valid Email Required\n';
		if ( err != '' ) {
			alert(err);
			return false;
		}
		return true;
	}
	//-->
	</script>
	<?php } ?>
	<table align="<?php echo $align_tbl;?>">
	<form name="<?php echo $form_name;?>" method="post" action="http://www.aweber.com/scripts/addlead.pl" <?php if($submit_again!=1){;?>onsubmit="return mbp_cmwValidateForm_0()"<?php }?>>
	 <input type="hidden" name="unit" value="maxbp-activate">
	 <input type="hidden" name="redirect" value="<?php echo $thankyou_url;?>">
	 <input type="hidden" name="meta_redirect_onlist" value="<?php echo $onlist_url;?>">
	 <input type="hidden" name="meta_adtracking" value="mr-posr-ordering">
	 <input type="hidden" name="meta_message" value="1">
	 <input type="hidden" name="meta_required" value="from,name">
	 <input type="hidden" name="meta_forward_vars" value="1">	
	 <?php if ( $submit_again == 1 ) { ?> 	
	 <input type="hidden" name="submit_again" value="1">
	 <?php } ?>		 
	 <?php if ( $hide == 1 ) { ?> 
	 <input type="hidden" name="name" value="<?php echo $name;?>">
	 <input type="hidden" name="from" value="<?php echo $email;?>">
	 <?php } else { ?>
	 <tr><td>Name: </td><td><input type="text" name="name" value="<?php echo $name;?>" size="25" maxlength="150" /></td></tr>
	 <tr><td>Email: </td><td><input type="text" name="from" value="<?php echo $email;?>" size="25" maxlength="150" /></td></tr>
	 <?php } ?>
	 <tr><td>&nbsp;</td><td><input type="submit" name="submit" value="<?php echo $submit_btn_txt;?>" class="button" /></td></tr>
	 </form>
	</table>
	<?php
}

/**
 * Register Plugin - Step 2
 */
function mbp_cmwRegisterStep2($form_name='frm2',$name,$email) {
	$msg = 'You have not clicked on the confirmation link yet. A confirmation email has been sent to you again. Please check your email and click on the confirmation link to activate the plugin.';
	if ( trim($_GET['submit_again']) != '' && $msg != '' ) {
		echo '<div id="message" class="updated fade"><p><strong>'.$msg.'</strong></p></div>';
	}
	?>
	<style type="text/css">
	table, tbody, tfoot, thead {
		padding: 8px;
	}
	tr, th, td {
		padding: 0 8px 0 8px;
	}
	</style>
	<div class="wrap"><h2> <?php echo MBP_CMW_NAME.' '.MBP_CMW_VERSION; ?></h2>
	 <center>
	 <table width="100%" cellpadding="3" cellspacing="1" style="border:1px solid #e3e3e3; padding: 8px; background-color:#f1f1f1;">
	 <tr><td align="center">
	 <table width="650" cellpadding="5" cellspacing="1" style="border:1px solid #e9e9e9; padding: 8px; background-color:#ffffff; text-align:left;">
	  <tr><td align="center"><h3>Almost Done....</h3></td></tr>
	  <tr><td><h3>Step 1:</h3></td></tr>
	  <tr><td>A confirmation email has been sent to your email "<?php echo $email;?>". You must click on the link inside the email to activate the plugin.</td></tr>
	  <tr><td><strong>The confirmation email will look like:</strong><br /><img src="http://www.maxblogpress.com/images/activate-plugin-email.jpg" vspace="4" border="0" /></td></tr>
	  <tr><td>&nbsp;</td></tr>
	  <tr><td><h3>Step 2:</h3></td></tr>
	  <tr><td>Click on the button below to Verify and Activate the plugin.</td></tr>
	  <tr><td><?php mbp_cmwRegistrationForm($form_name.'_0','Verify and Activate',$name,$email,$hide=1,$submit_again=1);?></td></tr>
	 </table>
	 </td></tr></table><br />
	 <table width="100%" cellpadding="3" cellspacing="1" style="border:1px solid #e3e3e3; padding:8px; background-color:#f1f1f1;">
	 <tr><td align="center">
	 <table width="650" cellpadding="5" cellspacing="1" style="border:1px solid #e9e9e9; padding:8px; background-color:#ffffff; text-align:left;">
	   <tr><td><h3>Troubleshooting</h3></td></tr>
	   <tr><td><strong>The confirmation email is not there in my inbox!</strong></td></tr>
	   <tr><td>Dont panic! CHECK THE JUNK, spam or bulk folder of your email.</td></tr>
	   <tr><td>&nbsp;</td></tr>
	   <tr><td><strong>It's not there in the junk folder either.</strong></td></tr>
	   <tr><td>Sometimes the confirmation email takes time to arrive. Please be patient. WAIT FOR 6 HOURS AT MOST. The confirmation email should be there by then.</td></tr>
	   <tr><td>&nbsp;</td></tr>
	   <tr><td><strong>6 hours and yet no sign of a confirmation email!</strong></td></tr>
	   <tr><td>Please register again from below:</td></tr>
	   <tr><td><?php mbp_cmwRegistrationForm($form_name,'Register Again',$name,$email,$hide=0,$submit_again=2);?></td></tr>
	   <tr><td><strong>Help! Still no confirmation email and I have already registered twice</strong></td></tr>
	   <tr><td>Okay, please register again from the form above using a DIFFERENT EMAIL ADDRESS this time.</td></tr>
	   <tr><td>&nbsp;</td></tr>
	   <tr>
		 <td><strong>Why am I receiving an error similar to the one shown below?</strong><br />
			 <img src="http://www.maxblogpress.com/images/no-verification-error.jpg" border="0" vspace="8" /><br />
		   You get that kind of error when you click on &quot;Verify and Activate&quot; button or try to register again.<br />
		   <br />
		   This error means that you have already subscribed but have not yet clicked on the link inside confirmation email. In order to  avoid any spam complain we don't send repeated confirmation emails. If you have not recieved the confirmation email then you need to wait for 12 hours at least before requesting another confirmation email. </td>
	   </tr>
	   <tr><td>&nbsp;</td></tr>
	   <tr><td><strong>But I've still got problems.</strong></td></tr>
	   <tr><td>Stay calm. <strong><a href="http://www.maxblogpress.com/contact-us/" target="_blank">Contact us</a></strong> about it and we will get to you ASAP.</td></tr>
	 </table>
	 </td></tr></table>
	 </center>		
	<p style="text-align:center;margin-top:3em;"><strong><?php echo MBP_CMW_NAME.' '.MBP_CMW_VERSION; ?> by <a href="http://www.maxblogpress.com/" target="_blank" >MaxBlogPress</a></strong></p>
	</div>
	<?php
}

/**
 * Register Plugin - Step 1
 */
function mbp_cmwRegisterStep1($form_name='frm1',$userdata) {
	$name  = trim($userdata->first_name.' '.$userdata->last_name);
	$email = trim($userdata->user_email);
	?>
	<style type="text/css">
	tabled , tbody, tfoot, thead {
		padding: 8px;
	}
	tr, th, td {
		padding: 0 8px 0 8px;
	}
	</style>
	<div class="wrap"><h2> <?php echo MBP_CMW_NAME.' '.MBP_CMW_VERSION; ?></h2>
	 <center>
	 <table width="100%" cellpadding="3" cellspacing="1" style="border:2px solid #e3e3e3; padding: 8px; background-color:#f1f1f1;">
	  <tr><td align="center">
		<table width="548" align="center" cellpadding="3" cellspacing="1" style="border:1px solid #e9e9e9; padding: 8px; background-color:#ffffff;">
		  <tr><td align="center"><h3>Please register the plugin to activate it. (Registration is free)</h3></td></tr>
		  <tr><td align="left">In addition you'll receive complimentary subscription to MaxBlogPress Newsletter which will give you many tips and tricks to attract lots of visitors to your blog.</td></tr>
		  <tr><td align="center"><strong>Fill the form below to register the plugin:</strong></td></tr>
		  <tr><td align="center"><?php mbp_cmwRegistrationForm($form_name,'Register',$name,$email);?></td></tr>
		  <tr><td align="center"><font size="1">[ Your contact information will be handled with the strictest confidence <br />and will never be sold or shared with third parties ]</font></td></tr>
		</table>
	  </td></tr></table>
	 </center>
	<p style="text-align:center;margin-top:3em;"><strong><?php echo MBP_CMW_NAME.' '.MBP_CMW_VERSION; ?> by <a href="http://www.maxblogpress.com/" target="_blank" >MaxBlogPress</a></strong></p>
	</div>
	<?php
}	
	
	// add a option page
	add_action('admin_menu', 'mbp_cmw_options');
	// Hook for the registration
	add_action( 'widgets_init', 'widget_cmw_register' );
} else if ($wp_version < '2.5') {
	function widget_cmwold_init()
	{
		// Check for the required API functions
		if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
			return;
	
	function widget_cmwold($args, $number = 1) {
		extract($args);
		$options 			= get_option('widget_cmwold');	
		$title 	 			= empty($options[$number]['title']) ? __('Categories') : $options[$number]['title'];
	
		$orderby 			= empty($options[$number]['orderby']) ? 'ID' : $options[$number]['orderby'];
		$order 	 			= empty($options[$number]['order']) ? 'asc' : $options[$number]['order'];
		$style 	 			= empty($options[$number]['style']) ? 'list' : $options[$number]['style'];
		
		$child_of 			= empty($options[$number]['child_of']) ? '' : $options[$number]['child_of'];
		$feed 				= empty($options[$number]['feed']) ? '' : $options[$number]['feed'];
		$feed_image 		= empty($options[$number]['feed_image']) ? '' : $options[$number]['feed_image'];
		$exclude 			= empty($options[$number]['exclude']) ? '' : $options[$number]['exclude'];
		$include 			= empty($options[$number]['include']) ? '' : $options[$number]['include'];
		$title_li 			= empty($options[$number]['title_li']) ? '' : $options[$number]['title_li'];
	
		$show_option_all 	= $options[$number]['show_option_all']  ? '1' : '0';
		$show_count 		= $options[$number]['show_count'] ? '1' : '0';
		$hide_empty 		= $options[$number]['hide_empty'] ? '1' : '0';
		$use_desc_for_title = $options[$number]['use_desc_for_title'] ? '1' : '0';
		$hierarchical 		= $options[$number]['hierarchical'] ? '1' : '0';
	
		if ( $hierarchical == '1' )
		{
			$child_of = '';
		}
		else
		{
			$child_of = '&child_of=' . $child_of;
		}
		echo $before_widget;
		echo $before_title . $title . $after_title;
		echo '<ul>';
		wp_list_categories('show_option_all=' . 
							$show_option_all . '&orderby=' . 
							$orderby . '&order=' . 
							$order . '&style=' . 
							$style . '&show_count=' . 
							$show_count . '&hide_empty=' . 
							$hide_empty . '&use_desc_for_title=' . 
							$use_desc_for_title . $child_of . '&feed=' . 
							$feed . '&feed_image=' . 
							$feed_image . '&exclude=' . 
							$exclude . '&include=' . 
							$include . '&hierarchical=' . 
							$hierarchical . '&title_li=' . 
							$title_li);
		echo '</ul>';
		echo $after_widget;
	}
	
	function widget_cmwold_control($number) {
		$options = $newoptions = get_option('widget_cmwold');
		
		if ( $_POST["cmwold-submit-$number"] ) {
			$newoptions[$number]['title'] 				= stripslashes($_POST["cmwold-title-$number"]);
	// Extraatjes
			$newoptions[$number]['show_option_all'] 	= isset($_POST["cmwold-show_option_all-$number"]);
			$newoptions[$number]['orderby'] 			= strip_tags(stripslashes($_POST["cmwold-orderby-$number"]));
			$newoptions[$number]['order'] 				= stripslashes($_POST["cmwold-order-$number"]);
			$newoptions[$number]['style'] 				= strip_tags(stripslashes($_POST["cmwold-style-$number"]));
			$newoptions[$number]['show_count'] 			= isset($_POST["cmwold-show_count-$number"]);
			$newoptions[$number]['hide_empty'] 			= isset($_POST["cmwold-hide_empty-$number"]);
			$newoptions[$number]['use_desc_for_title'] 	= isset($_POST["cmwold-use_desc_for_title-$number"]);
			$newoptions[$number]['child_of'] 			= strip_tags(stripslashes($_POST["cmwold-child_of-$number"]));
			$newoptions[$number]['feed'] 				= stripslashes($_POST["cmwold-feed-$number"]);
			$newoptions[$number]['feed_image'] 			= strip_tags(stripslashes($_POST["cmwold-feed_image-$number"]));
			$newoptions[$number]['exclude'] 			= strip_tags(stripslashes($_POST["cmwold-exclude-$number"]));
			$newoptions[$number]['include'] 			= strip_tags(stripslashes($_POST["cmwold-include-$number"]));
			$newoptions[$number]['hierarchical'] 		= isset($_POST["cmwold-hierarchical-$number"]);
			$newoptions[$number]['title_li'] 			= strip_tags(stripslashes($_POST["cmwold-title_li-$number"]));
		}
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('widget_cmwold', $options);
		}
		$title 				= wp_specialchars($options[$number]['title']);
		$show_option_all 	= $options[$number]['show_option_all'] ? 'checked="checked"' : '';
		$show_count 		= $options[$number]['show_count'] ? 'checked="checked"' : '';
		$hide_empty 		= $options[$number]['hide_empty'] ? 'checked="checked"' : '';
		$use_desc_for_title = $options[$number]['use_desc_for_title'] ? 'checked="checked"' : '';
		$hierarchical 		= $options[$number]['hierarchical'] ? 'checked="checked"' : '';
	?>
	<center>Check <a href="http://codex.wordpress.org/Template_Tags/wp_list_categories" target="_blank">wp_list_categories</a> for help with these parameters.</center>
	<br />
	<table align="center" cellpadding="1" cellspacing="1" width="400">
	<tr>
	<td align="left" valign="middle" width="90" nowrap="nowrap">
	Title Widget:
	</td>
	<td align="left" valign="middle" colspan="2">
	<input style="width: 300px;" id="cmwold-title-<?php echo "$number"; ?>" name="cmwold-title-<?php echo "$number"; ?>" type="text" value="<?php echo $title; ?>" />
	</td>
	</tr>
	<tr>
	<td align="left" valign="middle" width="90" nowrap="nowrap">
	Sort Options:
	</td>
	<td align="left" valign="middle" colspan="2">
	<select id="cmwold-orderby-<?php echo "$number"; ?>" name="cmwold-orderby-<?php echo "$number"; ?>" value="<?php echo $options[$number]['orderby']; ?>">
	<?php echo "<option value=\"\">Select</option>"; ?>
	<?php echo "<option value=\"ID\"" . ($options[$number]['orderby']=='id' ? " selected='selected'" : '') .">ID</option>"; ?>
	<?php echo "<option value=\"name\"" . ($options[$number]['orderby']=='name' ? " selected='selected'" : '') .">Name</option>"; ?>
	</select>&nbsp; <select id="cmwold-order-<?php echo "$number"; ?>" name="cmwold-order-<?php echo "$number"; ?>" value="<?php echo $options[$number]['order']; ?>">
	<?php echo "<option value=\"\">Select</option>"; ?>
	<?php echo "<option value=\"asc\"" . ($options[$number]['order']=='asc' ? " selected='selected'" : '') .">ASC</option>"; ?>
	<?php echo "<option value=\"desc\"" . ($options[$number]['order']=='desc' ? " selected='selected'" : '') .">DESC</option>"; ?>
	</select>
	</td>
	</tr>
	<tr>
	<td align="left" valign="middle" width="90" nowrap="nowrap">
	Child of:
	</td>
	<td align="left" valign="middle" colspan="2">
	<input style="width: 300px;" id="cmwold-child_of-<?php echo "$number"; ?>" name="cmwold-child_of-<?php echo "$number"; ?>" type="text" value="<?php echo $child_of; ?>" />
	</td>
	</tr>
	<tr>
	<td align="left" valign="middle" width="90" nowrap="nowrap">
	Feed:
	</td>
	<td align="left" valign="middle" colspan="2">
	<input style="width: 300px;" id="cmwold-feed-<?php echo "$number"; ?>" name="cmwold-feed-<?php echo "$number"; ?>" type="text" value="<?php echo $feed; ?>" />
	</td>
	</tr>
	<tr>
	<td align="left" valign="middle" width="90" nowrap="nowrap">
	Feed Image:
	</td>
	<td align="left" valign="middle" colspan="2">
	<input style="width: 300px;" id="cmwold-feed_image-<?php echo "$number"; ?>" name="cmwold-feed_image-<?php echo "$number"; ?>" type="text" value="<?php echo $feed_image; ?>" />
	</td>
	</tr>
	<tr>
	<td align="left" valign="middle" width="90" nowrap="nowrap">
	Exclude Categories:
	</td>
	<td align="left" valign="middle" colspan="2">
	<input style="width: 300px;" id="cmwold-exclude-<?php echo "$number"; ?>" name="cmwold-exclude-<?php echo "$number"; ?>" type="text" value="<?php echo $exclude; ?>" />
	</td>
	</tr>
	<tr>
	<td align="left" valign="middle" width="90" nowrap="nowrap">
	Include Categories:
	</td>
	<td align="left" valign="middle" colspan="2">
	<input style="width: 300px;" id="cmwold-include-<?php echo "$number"; ?>" name="cmwold-include-<?php echo "$number"; ?>" type="text" value="<?php echo $include; ?>" />
	</td>
	</tr>
	<tr>
	<td align="left" valign="middle" width="90" nowrap="nowrap">
	Title li:
	</td>
	<td align="left" valign="middle" colspan="2">
	<input style="width: 300px;" id="cmwold-title_li-<?php echo "$number"; ?>" name="cmwold-title_li-<?php echo "$number"; ?>" type="text" value="<?php echo $title_li; ?>" />
	</td>
	</tr>
	<tr>
	<td align="left" valign="middle" width="90" nowrap="nowrap">
	Style:
	</td>
	<td align="left" valign="middle">
	<select id="cmwold-style-<?php echo "$number"; ?>" name="cmwold-style-<?php echo "$number"; ?>" value="<?php echo $options[$number]['style']; ?>">
	<?php echo "<option value=\"\">Select</option>"; ?>
	<?php echo "<option value=\"list\"" . ($options[$number]['style']=='list' ? " selected='selected'" : '') .">List</option>"; ?>
	<?php echo "<option value=\"none\"" . ($options[$number]['style']=='none' ? " selected='selected'" : '') .">None</option>"; ?>
	</select>
	</td>
	<td align="right" valign="middle">
	Hierarchical:
	&nbsp;<input id="cmwold-hierarchical-<?php echo "$number"; ?>" name="cmwold-hierarchical-<?php echo "$number"; ?>" type="checkbox" <?php echo $hierarchical; ?> />
	</td>
	</tr>
	<tr>
	<td align="left" valign="middle" width="90" nowrap="nowrap">
	<?php _e('Show Post Counts', 'widgets'); ?>:
	&nbsp;<input id="cmwold-show_count-<?php echo "$number"; ?>" name="cmwold-show_count-<?php echo "$number"; ?>" type="checkbox" <?php echo $show_count; ?> />
	</td>
	<td align="right" valign="middle" width="90" nowrap="nowrap" colspan="2">
	Hide Empty Categories:
	&nbsp;<input id="cmwold-hide_empty-<?php echo "$number"; ?>" name="cmwold-hide_empty-<?php echo "$number"; ?>" type="checkbox" <?php echo $hide_empty; ?> />
	</td>
	</tr>
	<tr>
	<td align="left" valign="middle" width="90" nowrap="nowrap">
	Use Desc for Title:
	&nbsp;<input id="cmwold-use_desc_for_title-<?php echo "$number"; ?>" name="cmwold-use_desc_for_title-<?php echo "$number"; ?>" type="checkbox" <?php echo $use_desc_for_title; ?> />
	</td>
	<td align="right" valign="middle" width="90" nowrap="nowrap" colspan="2">
	Show Option All:
	&nbsp;<input id="cmwold-show_option_all-<?php echo "$number"; ?>" name="cmwold-show_option_all-<?php echo "$number"; ?>" type="checkbox" <?php echo $show_option_all; ?> />
	<input type="hidden" id="cmwold-submit-<?php echo "$number"; ?>" name="cmwold-submit-<?php echo "$number"; ?>" value="1" />
	</td>
	</tr>
	</table>
	<br />

	<?php
	}
	
	function widget_cmwold_setup() {
		$options = $newoptions = get_option('widget_cmwold');
		if ( isset($_POST['cmwold-number-submit']) ) {
			$number = (int) $_POST['cmwold-number'];
			if ( $number > 9 ) $number = 9;
			if ( $number < 1 ) $number = 1;
			$newoptions['number'] = $number;
		}
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('widget_cmwold', $options);
			widget_cmwold_register($options['number']);
		}
	}
	
	function widget_cmwold_page() {
		$options = $newoptions = get_option('widget_cmwold');
	?>
		<div class="wrap">
			<form method="POST">
				<h2>Manage Categories Widgets</h2>
				<p style="line-height: 30px;"><?php _e('How many Categories widgets would you like?'); ?>
				<select id="cmwold-number" name="cmwold-number" value="<?php echo $options['number']; ?>">
	<?php for ( $i = 1; $i < 10; ++$i ) echo "<option value='$i' ".($options['number']==$i ? "selected='selected'" : '').">$i</option>"; ?>
				</select>
				<span class="submit"><input type="submit" name="cmwold-number-submit" id="cmwold-number-submit" value="<?php _e('Save'); ?>" /></span></p>
			</form>
		</div>
	<?php
	}
	
	function widget_cmwold_register() {
		global $wp_version;
		$options = get_option('widget_cmwold');
		$number = $options['number'];
		if ( $number < 1 ) $number = 1;
		if ( $number > 9 ) $number = 9;
		for ($i = 1; $i <= 9; $i++) {
			$name = array('Manage Categories %s', null, $i);
			
			if ($wp_version == '2.2') {
				register_sidebar_widget($name, $i <= $number ? 'widget_cmwold' : /* unregister */ '','', $i);
			} else if ($wp_version == '2.3') {
				register_sidebar_widget($name, $i <= $number ? 'widget_cmwold' : /* unregister */ '', $i);
			} else {
				register_sidebar_widget($name, $i <= $number ? 'widget_cmwold' : /* unregister */ '','', $i);				
			}	
			
			register_widget_control($name, $i <= $number ? 'widget_cmwold_control' : /* unregister */ '', 460, 400, $i);
		}
		add_action('sidebar_admin_setup', 'widget_cmwold_setup');
		add_action('sidebar_admin_page', 'widget_cmwold_page');
	}
	// Delay plugin execution to ensure Dynamic Sidebar has a chance to load first
	widget_cmwold_register();
}

// Tell Dynamic Sidebar about our new widget and its control
add_action('plugins_loaded', 'widget_cmwold_init');
}
?>