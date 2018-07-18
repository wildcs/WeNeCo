<?php
/*                                                                       
*                                     ,--.                               
*             .---.                 ,--.'|            ,----..            
*            /. ./|             ,--,:  : |           /   /   \           
*        .--'.  ' ;          ,`--.'`|  ' :          |   :     :  ,---.   
*       /__./ \ : |          |   :  :  | |          .   |  ;. / '   ,'\  
*   .--'.  '   \' .   ,---.  :   |   \ | :   ,---.  .   ; /--` /   /   | 
*  /___/ \ |    ' '  /     \ |   : '  '; |  /     \ ;   | ;   .   ; ,. : 
*  ;   \  \;      : /    /  |'   ' ;.    ; /    /  ||   : |   '   | |: : 
*   \   ;  `      |.    ' / ||   | | \   |.    ' / |.   | '___'   | .; : 
*    .   \    .\  ;'   ;   /|'   : |  ; .''   ;   /|'   ; : .'|   :    | 
*     \   \   ' \ |'   |  / ||   | '`--'  '   |  / |'   | '/  :\   \  /  
*      :   '  |--" |   :    |'   : |      |   :    ||   :    /  `----'   
*       \   \ ;     \   \  / ;   |.'       \   \  /  \   \ .'            
*        '---"       `----'  '---'          `----'    `---`              
* 
*                          Web Network Configuration 
*     
*                         AUTHENTICATION VERIFICATION
*/

require_once( 'includes/config.php' );
require_once( 'includes/secure.php' );
require_once( 'includes/functions.php' );

$result = "";
$div_class = "result_fail";
$authData = getAuthData();
$username = AUTH_USER;
/* USERNAME FIXED TO CONFIG USERNAME
if ( isset ( $_POST["username"] ) ){
  $username = getVal( $_POST, "username" );
} else {
  $username = $authData[0];
}
*/

// SAVE AUTHENTICATION DATA
/*
*   saves authentication data into 'weneco.auth' file in /etc/
*/
function saveAuth(){
  global $result, $authData;
  $rw_err = false;
  $username = AUTH_USER; //$_POST[ "username" ];
  $old_pass = $authData[1];
  $newpass = $_POST["newpass1"];
  $newhash = password_hash( $_POST['newpass1'], PASSWORD_BCRYPT );
  
  // CHECK DATA
  //if ( ! CSRFValidate() ) { $result = lang( "MSG_CSFR_VIOLATION" ); }
  if ( strlen( $username ) < 4 ) { return lang( "MSG_USERNAME_LEN" ); }
  if ( is_file ( AUTH_FILE ) and ! password_verify( $_POST['oldpass'], $old_pass ) ) { return lang( "MSG_PWD_MISMATCH" ); }
  if ( ! $_POST["newpass1"] == $_POST["newpass2"] ){ return lang( "MSG_PWD_CONFIMATION" ); }
  
  $fauth = fopen( TMP_DIR. "/weneco.auth", "w" );
  if ( $fauth ){
    if ( ! fwrite ( $fauth, $username . PHP_EOL ) ) { $rw_err = true ; }
    if ( ! fwrite ( $fauth, $newhash ) ) { $rw_err = true ; }
    fclose ($fauth);
    
    $ret =  exec ( "sudo cp " .TMP_DIR. "/weneco.auth ". AUTH_FILE );

  } else {
    $rw_err = true;
  }
  
  if ( $rw_err ){
    return lang( "MSG_F_W_ERROR" );
  }  
  return lang( "MSG_SUCCESS" );
}


// REMOVE AUTHENTICATION
/*
*   removes 'weneco.auth' file in /etc/
*/
function removeAuth(){
  global $result, $authData;
  $rw_err = false;
  $old_pass = $authData[1];
  
  if ( is_file ( AUTH_FILE ) and ! password_verify( $_POST['oldpass'], $old_pass ) ) { return lang( "MSG_PWD_MISMATCH" ); }
  $ret =  exec ( "sudo rm " . AUTH_FILE );
  return lang( "MSG_SUCCESS" );
}

// SHOW AUTHENTICATION Configuration
function showAuthConf(){
    global $username, $result, $div_class;
    ?>

      <!-- Content Header -->
      <div id="content_header">
        <?php echo lang("HDR_AUTHCONF"); ?>
      </div>
      <!--./content_header -->

      <!-- Content Body -->
      <div id="content_body">
        <div id="result" class="<?php echo $div_class; ?>">
          <?php echo $result; ?>
        </div>
        <form name="authconf" action="index.php?page=authconf" method="post" id="authconf" class="ui-body ui-body-a ui-corner-all">
          <?php echo printCSRFToken(); // ADD CSFR TOKEN ?>
          <label for="username"><?php echo lang("LBL_TXT_USERNAME"); ?></label>
            <input type="text" name="username" id="username" value="<?php echo $username;?>" disabled="disabled"/>
          <label for="oldpass"><?php echo lang("LBL_TXT_OLDPASS"); ?></label>
            <input type="password" name="oldpass" id="oldpass"/>
          <label for="newpass1"><?php echo lang("LBL_TXT_NEWPASS1"); ?></label>
            <input type="password" name="newpass1" id="newpass1"/>
          <label for="newpass2"><?php echo lang("LBL_TXT_NEWPASS2"); ?></label>
            <input type="password" name="newpass2" id="newpass2"/>  
          <input type="submit" data-theme="b" name="UpdateAuth" value="<?php echo lang("BTN_SAVE"); ?>" />
          <input type="submit" data-theme="c" name="RemoveAuth" value="<?php echo lang("BTN_REMOVE"); ?>" />           
        </form>
      </div>
      <!--./content_body -->

    <?php
}

if ( isset ( $_POST["UpdateAuth"] ) ) {
   $result = saveAuth();
} elseif ( isset ( $_POST["RemoveAuth"] ) ) {
    $result = removeAuth();
}
if ( $result == lang( "MSG_SUCCESS" ) ){
  $div_class = "result_ok";
}
?>