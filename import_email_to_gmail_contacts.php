<?php session_start();?>
<?php
/*
Plugin Name: Import Emails to gmail Contacts
Plugin URI: http://venugopalphp.wordpress.com/
Description: Insert user email address and import bulk of emails into your gmail contacts
Author: Venugopal
Version: 1.0
Author URI: http://venugopalphp.wordpress.com/
*/

/* 
 * Include styles and JS
 */
    
add_action('admin_enqueue_scripts', 'emtg_load_scripts_styles');
 function emtg_load_scripts_styles() { 

wp_enqueue_style( 'e_styles', plugins_url( 'css/e_styles.css',__FILE__ ) );
if($_REQUEST['page'] == 'contacts-gmail'){
wp_enqueue_script( 'jQuery', plugins_url( 'js/jquery-1.8.0.min.js',__FILE__ ), array(), '1.8.0', true );
}
wp_enqueue_script( 'radio_select', plugins_url( 'js/radio_select.js',__FILE__ ), '', '', TRUE );

 }

function emtg_contacts_option_page()
{      

	include "db_insert_detals.php";
       $all_details->emtg_update_keys(); // After Click Save button Update API Key form 
     
       // If API key form fields not empty show radio list
	    
        $cleint_id = get_option('gmail_clientid');
        $secert_id = get_option('gmail_secretid');
		$redirect_url = get_option('gmail_redirect_url');
		
		$_SESSION['cleint_id'] =  $cleint_id;
		$_SESSION['secert_id'] =  $secert_id;
		$_SESSION['redirect_url'] =  $redirect_url;
		$_SESSION['sucess_url'] = admin_url( 'admin.php?page=contacts-gmail', 'http' ).'&res=sucesss';

        if(!empty($cleint_id) && !empty($secert_id))
        {

            echo '<div class="main_select">';
            emtg_radio_list(); //Radi button List
            emtg_user_radio(); // Display User Email List
            emtg_csv_single_radio();// Display CSV form and Single email form
			
			$all_details->emtg_confirm_user_email_list(); //After clicked on Save button Selected User Emails stores in gmail contacts
			$all_details->emtg_csv_email_upload(); //After clicked on Save button CSV Emails stores in gmail contacts
			$all_details->emtg_single_email(); // Single email Submission

              if(isset($_REQUEST['res'])) {
				 //echo $_REQUEST['email_id'] ;
				 if($_REQUEST['email_id']){
					 echo "<h3>Sucessfuly Imported to &nbsp; <span>".$_REQUEST['email_id']."</span></h3>";
				 } else {
echo "<h4>Failed to Import your mails. &nbsp; <span>Please Enable '<b>Contacts API</b>' in <a target='_blank' href ='https://console.developers.google.com'>https://console.developers.google.com</a></span></h4>";

				 }
                   } 
            echo '</div>';
	
           
        }

        emtg_api_key_form(); // Display API Key Form
       

}



function import_emails_gmail_contacts()
{
	
	add_menu_page('Emails To Gmail Contacts','Emails To Gmail Contacts','manage_options','contacts-gmail','emtg_contacts_option_page' ,plugins_url( 'css/logo.png',__FILE__ ));	
}
add_action('admin_menu','import_emails_gmail_contacts');



//Create table while activation

function emtg_create_email_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'import_mail_gmail_contacts';
    $sql = "CREATE TABLE $table_name (
        id int(9) unsigned NOT NULL AUTO_INCREMENT,
           email varchar(250),
               PRIMARY KEY  (id)
        );";
 
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}
 
register_activation_hook( __FILE__, 'emtg_create_email_table' );
?>