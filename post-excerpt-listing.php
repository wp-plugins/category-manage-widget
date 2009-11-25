<?php
/*
 * Plugin Name:   Post Excerpt Listing
 * Version:       1.0
 * Plugin URI:    http://wordpress.org/extend/plugins/post-excerpt-listing/
 * Description:   This plugin displays the excerpt in the post listing along with the post title.It also generates automatic excerpt for the post whose excerpt is missing and the excerpt limitation like total words/character and cutoff character can be configured. Adjust your settings <a href="options-general.php?page=mbp_pel_excerpt_options">here</a>.
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
 * This is the improved version of "Excerpts Deluxe" plugin by Joost Baaij
 *
 */

$mbppel_path      = preg_replace('/^.*wp-content[\\\\\/]plugins[\\\\\/]/', '', __FILE__);
$mbppel_path      = str_replace('\\','/',$mbppel_path);
$mbppel_dir       = substr($mban_path,0,strrpos($mbppel_path,'/'));
$mbppel_siteurl   = get_bloginfo('wpurl');
$mbppel_siteurl   = (strpos($mbppel_siteurl,'http://') === false) ? get_bloginfo('siteurl') : $mbppel_siteurl;
$mbppel_fullpath  = $mbppel_siteurl.'/wp-content/plugins/'.$mbppel_dir.'';
$mbppel_fullpath  = $mbppel_fullpath.'post-ordering/';
$mbppel_abspath   = str_replace("\\","/",ABSPATH); 

define('MBP_PEL_ABSPATH', $mbppel_path);
define('MBP_PEL_LIBPATH', $mbppel_fullpath);
define('MBP_PEL_SITEURL', $mbppel_siteurl);
define('MBP_PEL_NAME', 'Post Excerpt Listing');
define('MBP_PEL_VERSION', '1.0');  
define('MBP_PEL_LIBPATH', $mbppel_fullpath);


// Defaults

define('MBP_PEL_CUTOFF_LENGTH_DEFAULT', 25);
define('MBP_PEL_CUTOFF_MODE_DEFAULT', 'words');
define('MBP_PEL_CUTOFF_CHARACTER_DEFAULT', '...');

// Hooks

add_action('init', 'mbp_pel_excerpts_install');
$mbp_pel_activate = get_option('mbp_pel_activate');
if ($mbp_pel_activate != '') {
	add_action('the_content', 'mbp_pel_excerpts_display');
}
add_action('admin_menu', 'mbp_pel_excerpts_add_pages');


// Functions

/**
 * Install this plugin. Sets defaults for the options.
 */
function mbp_pel_excerpts_install() {
	if (get_option('mbp_pel_excerpts_cutoff_length') == '') {
		update_option('mbp_pel_excerpts_cutoff_length', MBP_PEL_CUTOFF_LENGTH_DEFAULT);
	}
	
	if (get_option('mbp_pel_excerpts_cutoff_mode') == '') {
		update_option('mbp_pel_excerpts_cutoff_mode', MBP_PEL_CUTOFF_MODE_DEFAULT);
	}
	
	if (get_option('mbp_pel_excerpts_cutoff_character') == '') {
		update_option('mbp_pel_excerpts_cutoff_character', MBP_PEL_CUTOFF_CHARACTER_DEFAULT);
	}
}

/**
 * Main function. Displays the excerpt.
 */
function mbp_pel_excerpts_display($excerpt) {
	global $post;
	
	// Posts that have an actual excerpt should display that and return.
	if($post->post_excerpt) {
		if (is_single()){
			echo $post->post_content;
			return;		
		} else {
			echo $post->post_excerpt;
			return;			
		}
	}
	
	
	// Otherwise we'll build the excerpt ourselves.
	$content = strip_tags($post->post_content);
	$cutoff_length = (int) get_option('mbp_pel_excerpts_cutoff_length');
	
	if(get_option('mbp_pel_excerpts_cutoff_mode')=='words') {
		$words = preg_split("/\s+/", $content);
		if (is_single()){
			echo $post->post_content;
		} else {
			$words = explode(' ', $content, $cutoff_length + 1);
			if(count($words) > $cutoff_length){
				array_pop($words);
				array_push($words, get_option('mbp_pel_excerpts_cutoff_character'));
				echo implode(' ', $words);
			} else {
				echo $content;
			}		
		}
	} else if(get_option('mbp_pel_excerpts_cutoff_mode')=='characters') {
		if (is_single()){
			echo $post->post_content;
		} else {
			if (strlen($content)> $cutoff_length){
				echo substr($content, 0, $cutoff_length);
				echo get_option('mbp_pel_excerpts_cutoff_character');				
			} else {
				echo $post->post_content;
			}	
		}	
	} else {
		// Display the originally intended excerpt as a last resort.
		echo $excerpt;
	}
}

/**
 * Add admin pages.
 */
function mbp_pel_excerpts_add_pages() {
	add_options_page('Post Excerpt Listing', 'Post Excerpt Listing', 8, 'mbp_pel_excerpt_options', 'mbp_pel_excerpts_options');
}

/**
 * Add submenu to Options admin page.
 */
function mbp_pel_excerpts_options() {
	    
	$mbp_pel_activate = get_option('mbp_pel_activate');
	$reg_msg = '';
	$mbp_pel_msg = '';
	$form_1 = 'mbp_pel_reg_form_1';
	$form_2 = 'mbp_pel_reg_form_2';
		// Activate the plugin if email already on list
	if ( trim($_GET['mbp_onlist']) == 1 ) {
		$mbp_pel_activate = 2;
		update_option('mbp_pel_activate', $mbp_pel_activate);
		$reg_msg = 'Thank you for registering the plugin. It has been activated'; 
	} 
	// If registration form is successfully submitted
	if ( ((trim($_GET['submit']) != '' && trim($_GET['from']) != '') || trim($_GET['submit_again']) != '') && $mbp_pel_activate != 2 ) { 
		update_option('mbp_pel_name', $_GET['name']);
		update_option('mbp_pel_email', $_GET['from']);
		$mbp_pel_activate = 1;
		update_option('mbp_pel_activate', $mbp_pel_activate);
	}
	if ( intval($mbp_pel_activate) == 0 ) { // First step of plugin registration
		global $userdata;
		mbp_pelRegisterStep1($form_1,$userdata);
	} else if ( intval($mbp_pel_activate) == 1 ) { // Second step of plugin registration
		$name  = get_option('mbp_pel_name');
		$email = get_option('mbp_pel_email');
		mbp_pelRegisterStep2($form_2,$name,$email);
	} else if ( intval($mbp_pel_activate) == 2 ) { // Options page
		if ( trim($reg_msg) != '' ) {
			echo '<div id="message" class="updated fade"><p><strong>'.$reg_msg.'</strong></p></div>';
		}		
		
		if($_SERVER['REQUEST_METHOD'] == 'POST' ) {
	        update_option("mbp_pel_excerpts_cutoff_length", $_POST['mbp_pel_excerpts_cutoff_length']);
	        update_option("mbp_pel_excerpts_cutoff_mode", $_POST['mbp_pel_excerpts_cutoff_mode']);
	        update_option("mbp_pel_excerpts_cutoff_character", $_POST['mbp_pel_excerpts_cutoff_character']);

	        // Put a message on the screen
			?><div class="updated"><p><strong>Options saved</strong></p></div><?php
	    }

	    // Now display the options editing screen
	    echo '<div class="wrap">';
	?>	    
		
		<h2><?php echo MBP_PEL_NAME.' '.MBP_PEL_VERSION; ?></h2>
	<strong><img src="<?php echo MBP_AIT_LIBPATH;?>image/how.gif" border="0" align="absmiddle" /> <a href="http://wordpress.org/extend/plugins/post-excerpt-listing/other_notes/" target="_blank">How to use it</a>&nbsp;&nbsp;&nbsp;
			<img src="<?php echo MBP_AIT_LIBPATH;?>image/comment.gif" border="0" align="absmiddle" /> <a href="javascript:void(0);">Community</a></strong>
	<br/><br/>			
		
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
		<ol>
			<li>
				<label for="mbp_pel_excerpts_cutoff_length">Cutoff after</label>
				<input id="mbp_pel_excerpts_cutoff_length" name="mbp_pel_excerpts_cutoff_length" type="text" size="5" maxlength="8" value="<?php echo get_option('mbp_pel_excerpts_cutoff_length') ?>" />
				<input name="mbp_pel_excerpts_cutoff_mode" <?php if(get_option('mbp_pel_excerpts_cutoff_mode')=='words') echo "checked=\"checked\""?> type="radio" value="words" /> words
				<input name="mbp_pel_excerpts_cutoff_mode" <?php if(get_option('mbp_pel_excerpts_cutoff_mode')=='characters') echo "checked=\"checked\""?> type="radio" value="characters" /> characters
			</li>
		
			<li>
				<label for="mbp_pel_excerpts_cutoff_character">Cutoff character</label>
				<input id="mbp_pel_excerpts_cutoff_character" name="mbp_pel_excerpts_cutoff_character" type="text" size="5" maxlength="255" value="<?php echo get_option('mbp_pel_excerpts_cutoff_character') ?>" />
				<em>(default: <?php echo MBP_PEL_CUTOFF_CHARACTER_DEFAULT ?>)</em>
			</li>
		</ol>

		<p class="submit">
			<input type="submit" name="Submit" value="Update Options" />
		</p>
	</form>
	
<div align="center" style="background-color:#f1f1f1; padding:5px 0px 5px 0px" >
<p align="center"><strong><?php echo MBP_PEL_NAME.' '.MBP_PEL_VERSION; ?> by <a href="http://www.maxblogpress.com" target="_blank">MaxBlogPress</a></strong></p>
<p align="center">This plugin is the result of <a href="http://www.maxblogpress.com/blog/219/maxblogpress-revived/" target="_blank">MaxBlogPress Revived</a> project.</p>
</div>		
	
	</div>
	
	<?php
	}
}

// Srart Registration.

/**
 * Plugin registration form
 */
function mbp_pelRegistrationForm($form_name, $submit_btn_txt='Register', $name, $email, $hide=0, $submit_again='') {
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
	function mbp_pelValidateForm_0() {
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
	<form name="<?php echo $form_name;?>" method="post" action="http://www.aweber.com/scripts/addlead.pl" <?php if($submit_again!=1){;?>onsubmit="return mbp_pelValidateForm_0()"<?php }?>>
	 <input type="hidden" name="unit" value="maxbp-activate">
	 <input type="hidden" name="redirect" value="<?php echo $thankyou_url;?>">
	 <input type="hidden" name="meta_redirect_onlist" value="<?php echo $onlist_url;?>">
	 <input type="hidden" name="meta_adtracking" value="mr-image-organizer">
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
function mbp_pelRegisterStep2($form_name='frm2',$name,$email) {
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
	<div class="wrap"><h2> <?php echo MBP_PEL_NAME.' '.MBP_PEL_VERSION; ?></h2>
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
	  <tr><td><?php mbp_pelRegistrationForm($form_name.'_0','Verify and Activate',$name,$email,$hide=1,$submit_again=1);?></td></tr>
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
	   <tr><td><?php mbp_pelRegistrationForm($form_name,'Register Again',$name,$email,$hide=0,$submit_again=2);?></td></tr>
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
	<p style="text-align:center;margin-top:3em;"><strong><?php echo MBP_PEL_NAME.' '.MBP_PEL_VERSION; ?> by <a href="http://www.maxblogpress.com/" target="_blank" >MaxBlogPress</a></strong></p>
	</div>
	<?php
}

/**
 * Register Plugin - Step 1
 */
function mbp_pelRegisterStep1($form_name='frm1',$userdata) {
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
	<div class="wrap"><h2> <?php echo MBP_PEL_NAME.' '.MBP_PEL_VERSION; ?></h2>
	 <center>
	 <table width="100%" cellpadding="3" cellspacing="1" style="border:2px solid #e3e3e3; padding: 8px; background-color:#f1f1f1;">
	  <tr><td align="center">
		<table width="548" align="center" cellpadding="3" cellspacing="1" style="border:1px solid #e9e9e9; padding: 8px; background-color:#ffffff;">
		  <tr><td align="center"><h3>Please register the plugin to activate it. (Registration is free)</h3></td></tr>
		  <tr><td align="left">In addition you'll receive complimentary subscription to MaxBlogPress Newsletter which will give you many tips and tricks to attract lots of visitors to your blog.</td></tr>
		  <tr><td align="center"><strong>Fill the form below to register the plugin:</strong></td></tr>
		  <tr><td align="center"><?php mbp_pelRegistrationForm($form_name,'Register',$name,$email);?></td></tr>
		  <tr><td align="center"><font size="1">[ Your contact information will be handled with the strictest confidence <br />and will never be sold or shared with third parties ]</font></td></tr>
		</table>
	  </td></tr></table>
	 </center>
	<p style="text-align:center;margin-top:3em;"><strong><?php echo MBP_PEL_NAME.' '.MBP_PEL_VERSION; ?> by <a href="http://www.maxblogpress.com/" target="_blank" >MaxBlogPress</a></strong></p>
	</div>
	<?php
}
?>