<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
 class Db_insert_details
 {
   
     public function emtg_redirect_url(){
         
         $cleint_id = get_option('gmail_clientid');
		 
          echo '<img src='.plugins_url('css/ajax-loader.gif',__FILE__).'>';
			echo '<script>window.location="https://accounts.google.com/o/oauth2/auth?client_id='.$cleint_id.'&redirect_uri='.plugins_url('apiconnect.php',__FILE__).'&scope=https://www.google.com/m8/feeds/&response_type=code"</script>';
			
     }


	 
	 public function create_csv_file(){
		 global $wpdb;
		$emails = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."import_mail_gmail_contacts");
		 
		 foreach($emails as $emailsrr) { 
		//echo $emailsrr->email;		 
		$data .= $emailsrr->email."\n";
		}
		$file = plugin_dir_path( __FILE__ ).'\emtg_file.csv';
		//echo getcwd();
		//chmod($file, 777);
		file_put_contents($file, $data) or die("Permission Denied");
		 $deleterecords = "TRUNCATE TABLE ".$wpdb->prefix."import_mail_gmail_contacts"; //empty the table of its current records
         $wpdb->query($deleterecords);
	 }
     
     public function emtg_update_keys()
     {
           if(isset($_REQUEST['client_key_imp']))
            {   
         echo '<div class="updated notice is-dismissible" id="message"><p>Your Gmail API details updated</p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';		  
		  
			update_option('gmail_clientid', sanitize_text_field($_REQUEST['gmail_clientid']));
			update_option('gmail_secretid', sanitize_text_field($_REQUEST['gmail_secretid']));
			update_option('gmail_redirect_url', sanitize_text_field($_REQUEST['redirect_url']));
			

             }
         }
     
     public function emtg_confirm_user_email_list()
     {
         
        $usersmail = $_REQUEST['users'];
		//print_r( $usersmail );
		
        if(is_array($usersmail))
        {
			   //Remove all the data from table
			    global $wpdb;
             $deleterecords = "TRUNCATE TABLE ".$wpdb->prefix."import_mail_gmail_contacts"; //empty the table of its current records
            $wpdb->query($deleterecords);
			
            foreach($usersmail as $usersmail_list){
            //echo $usersmail_list.'<br>';
           
          //Insert data into table
            $insert_email =  "INSERT INTO ".$wpdb->prefix."import_mail_gmail_contacts (email) VALUES('$usersmail_list')";
            $wpdb->query($insert_email);
			  }
				 				 
				//Creating csv file
				$this->create_csv_file();
			  
               // Calling Google API URL
              $this->emtg_redirect_url();
                  
        }

     }
     
     public function emtg_csv_email_upload(){
         
         if(isset($_REQUEST['csv_upload'])){
             global $wpdb;
            $deleterecords = "TRUNCATE TABLE ".$wpdb->prefix."import_mail_gmail_contacts"; //empty the table of its current records
            $wpdb->query($deleterecords);
           // echo $_FILES['filename']['name'];
            
            //Upload File
			if (is_uploaded_file($_FILES['filename']['tmp_name'])) {
				readfile($_FILES['filename']['tmp_name']);
                
	       //Import uploaded file to Database
                $handle = fopen($_FILES['filename']['tmp_name'], "r");
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        $import="INSERT into ".$wpdb->prefix."import_mail_gmail_contacts(email) values('$data[0]')";
                            $wpdb->query($import);
                }
                fclose($handle);
				
				//Creating csv file
				$this->create_csv_file();
			
                // Calling Google API URL
                $this->emtg_redirect_url();
            }
	

         }
         
     }
          
     public function emtg_single_email(){
		
		
         if(isset($_REQUEST['single_email_s']))
         {  global $wpdb;
             //$emails = sanitize_text_field($_REQUEST['single_email']);
			 //echo $emails;
			 $deleterecords = "TRUNCATE TABLE ".$wpdb->prefix."import_mail_gmail_contacts"; //empty the table of its current records
             $wpdb->query($deleterecords);
	
			 for($i=1; $i<=$_REQUEST['count']; $i++){
				 
				 $emails = sanitize_text_field($_REQUEST['email_name'.$i]);
                //echo $emails.'<br>';
				$import="INSERT into ".$wpdb->prefix."import_mail_gmail_contacts(email) values('$emails')";
				$wpdb->query($import);
				
				}
			   
			   
			//Creating csv file
			$this->create_csv_file();

			// Calling Google API URL
			$this->emtg_redirect_url();
         }
             
         
     }
    
 }
 
  $all_details = new Db_insert_details();
  
 /*   
 * API fields form display function 
 */
  
   function emtg_api_key_form()
  {
      echo '<form name="import_email" action="" method="post" class="import_email">
        <h3>Enter Google Application Details <a href="'.plugins_url( 'google_api_console.pdf', __FILE__ ).'" target="_blank">User Guide</a></h3>
        <label>Client ID :</label><input type="text" name="gmail_clientid" value="'.get_option('gmail_clientid').'">
        <label>Client Secret:</label><input type="text" name="gmail_secretid" value="'.get_option('gmail_secretid').'">
        <label>Authorized redirect URIs <br><i><b class="blink_me">(Copy below URL and paste in Google Authorized redirect URIs) :</b></i></label>
        <input type="text" class="large_text_code"  name="redirect_url" value="'.plugins_url('apiconnect.php',__FILE__).'" readonly="readonly" onfocus="this.select();">
        <input type="submit" value="Save" class="imports" name="client_key_imp">
        </form>';
  }
 
  
  
  /*
  * List Of Radio buttons
  */
  function emtg_radio_list()
  {
      echo '<div class="radio_list">
        <label><input type="radio" name="colorRadio" value="Users_list"> Users Email List</label>
        <label><input type="radio" name="colorRadio" value="csv_upload"> CSV Upload</label>
        <label><input type="radio" name="colorRadio" value="single_email"> Add Email</label>
        </div>';
  }
  
  
  function emtg_user_radio()
  {?>
       <div class="Users_list raido_default">
        
    <form name="users" method="post" action="">
     
        <table>
               
     <tbody>
         <tr style="position: fixed; top: 93px;"><td> <input id="chkAllFiles" type="checkbox" title="All Files" onchange="selectAllFiles(this.checked);" /></td><td><b>Check this for ALL</b></td></tr>
         <?php global $wpdb;
             $usr_email = $wpdb->get_results("select * from ".$wpdb->prefix."users");
                  $i=1;
             foreach($usr_email as $usr_emails){?>
        <tr><td><input id="chkFile<?php echo $i;?>" type="checkbox"  name="users[]" value="<?php echo $usr_emails->user_email ?>"/></td><td>
             <?php echo $usr_emails->user_email ?></td></tr>
            <?php  $i++; } ?>
       </tbody>
       </table>
        <input type="hidden" class="email_count" value="<?php echo $wpdb->num_rows;?>">
        <input type="submit" value="submit" class="imports" name="userlist">
          
 
    
  </form>
     
    </div>
<?php  }
 
     function emtg_csv_single_radio()
     {
         //CSv from
         echo '<div class="csv_upload raido_default">
                <form name="csv_upload" action="" method="post" enctype="multipart/form-data">
                <input type="file" name="filename" accept="text/csv">
                 <input type="submit" value="Upload" class="imports" name="csv_upload">
                 </form>
                </div>';
         
         //Single email form
                echo  '<div class="single_email raido_default">
				<ul>

                     <form name="single_emails" action="" method="post">
					 <input type="hidden" count="count" name="count" value="0" readonly >
					 
					<INPUT type="button" class="imports" value="ADD Email" name="add" onClick="incrementCount()">
					<INPUT type="button" class="del_text" value="Remove Email" name="remove" onClick="decCount()" style="display:none;">
					<br><br>
					<li id="listtypes"></li>
					<div class="show_sub"></div>
                  <input type="submit" value="Submit"  name="single_email_s" class="sub_disable imports" style="display:none;">
				  <br><br>
                </form>
				
				</ul>
                </div>';
     }      