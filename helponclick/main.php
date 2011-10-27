<?php
/*
Plugin Name: Live Chat by HelpOnClick
Plugin URI: http://www.helponclick.com?utm_campaign=wordpress&utm_medium=web&utm_source=plugin_page
Description: Chat with your website visitors - live chat plugin for HelpOnClick software. <a href='options-general.php?page=helponclick'>Configure</a>
Version: 1.1
Author: OnClick Solutions ltd
Author URI: http://www.helponclick.com?utm_campaign=wordpress&utm_medium=web&utm_source=plugin_page
License: GPL2
*/
?>
<?php 
// add the admin settings and such
add_action('admin_init', 'helponclick_admin_init');
add_action('wp_footer', 'helponclick_footer');
// add the admin options page
add_action('admin_menu', 'helponclick_admin_add_page');

function get_remote($url, $port=80)
{
    if(function_exists("curl_init"))
    {
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $url);
        curl_setopt($curl_handle, CURLOPT_PORT , $port);
        curl_setopt($curl_handle, CURLOPT_HEADER, false);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl_handle, CURLOPT_POST, false);
        $data = curl_exec($curl_handle);
        curl_close($curl_handle);
    }
    else
    {
        $data = file_get_contents($url);
    }

    return $data;
}

function helponclick_admin_init(){
    $response_errors = array ("Invalid email address<br />",
                                "error:Can't find account, please contact support"); 
    if (isset($_GET['reset_settings'])) {
        update_option('helponclick_account_email', '');
        update_option('helponclick_account_password', '');
        update_option('helponclick_code_script', '');
        wp_redirect(admin_url().'options-general.php?page=helponclick&reset_success=1');
        
    }
    if (isset($_GET['reset_success'])) {
        echo '    <div id="message" class="updated fade">
                    <p>'.__('Reset success', 'helponclick').'</p>
                </div>';

    }
    if (isset($_GET['connect_success'])) {
        echo '    <div id="message" class="updated fade">
                    <p>'.__('Connected successfully, chat is installed', 'helponclick').'</p>
                </div>';

    }
    if (isset($_GET['connect_account']) && count($_POST)) 
    {
        if($_POST['helponclick_account_email']!="" && $_POST['helponclick_account_password']!="")
        {
            //$response = file_get_contents('http://www.helponclick.com/code.php?email='.$_POST['helponclick_account_email'].'&password='.md5($_POST['helponclick_account_password']));
            $response = get_remote('http://www.helponclick.com/code.php?email='.$_POST['helponclick_account_email'].'&password='.md5($_POST['helponclick_account_password']));
            
            //if (in_array(trim($response), $response_errors)) 
            if (!strstr($response, "Error")===false) 
            {
                echo '    <div id="message" class="updated fade">
                            <p>'.$response.'</p>
                        </div>';            
            } 
            else 
            {
                update_option('helponclick_account_email', $_POST['helponclick_account_email']);
                update_option('helponclick_account_password', $_POST['helponclick_account_password']);
                update_option('helponclick_code_script', urldecode($response));
                
                wp_redirect(admin_url().'options-general.php?page=helponclick&connect_success=1');            
            }
        }
        else
        {
                echo '<div id="message" class="updated fade">
                        <p>'.__('Email address or password missing', 'helponclick').'</p>
                    </div>';    
        }
    }
    register_setting( 'helponclick_account', 'helponclick_account_email');
    register_setting( 'helponclick_account', 'helponclick_account_password');
    register_setting( 'helponclick_code', 'helponclick_code_script');
}

function helponclick_admin_add_page() {
    add_options_page('Live Chat Software by HelpOnClick', 'Live Chat', 'manage_options', 'helponclick', 'helponclick_options_page');
}
function helponclick_footer(){
    if ($script = get_option('helponclick_code_script')) {
        echo $script;
    }
}
// display the admin options page
function helponclick_options_page() {
    $options_account_email = get_option('helponclick_account_email');
    $options_account_password = get_option('helponclick_account_password');
    $options_code_script = get_option('helponclick_code_script');
?>
<link rel="stylesheet" href="<?php bloginfo('url'); ?>/wp-content/plugins/helponclick/nyroModal.css" type="text/css" media="screen" />
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.js"></script>
<script type="text/javascript" src="<?php bloginfo('url'); ?>/wp-content/plugins/helponclick/jquery.nyroModal.custom.js"></script>
<script type="text/javascript">
$(function() {
  $('.nyroModal').nyroModal();
});
</script>
<div class="wrap">
    <h2><?php _e('Live Chat Software by HelpOnClick'); ?></h2>
    <br />
    <div id="poststuff" class="jd-settings">
        <div>
        
            <div class="postbox">
                <h3><?php _e('Account', 'helponclick'); ?></h3>
                <div class="inside">
                    <br class="clear" />
                    <p style='font-size:14px;'><a style="text-decoration:none;"></a><input name="account" value="1" type="radio" checked /> <?php _e('I already have an account', 'helponclick'); ?></p>
                    <p style='font-size:14px;'><a style="font-size:14px;text-decoration:none;color:#000000" href="http://www.helponclick.com/signup.html?background=ffffff&utm_campaign=wordpress&utm_medium=web&utm_source=plugin" class="nyroModal" target="_blank"><input name="account" value="0" type="radio" /> <?php _e('I do not have an account - quickly create account for free', 'helponclick'); ?></a></p>
                </div>
            </div>            
<?php if (!empty($options_account_email) && !empty($options_account_password)) { ?>
            <div class="postbox">
                <h3><?php _e('Connect', 'helponclick'); ?></h3>
                <div class="inside">
                    <br class="clear" />
                    <form method="post" action="http://app.helponclick.com/login" target="_blank">
                    <input type='hidden' name='logout_to' value='http://www.helponclick.com/login.php' />
                    <input type='hidden' name='email' value='<?php echo $options_account_email?>' />
                    <input type='hidden' name='password' value='<?php echo $options_account_password?>' />
                    <table class="form-table">
                        <tr>
                            <td style='font-size:14px;width:120px'><?php _e('Email', 'helponclick'); ?></td>
                            <td>
                                <input id="helponclick_account_email" name="email_dummy" size="40" type="text" value="<?php echo $options_account_email?>" disabled />
                            </td>
                        </tr>
                        <tr><td style='font-size:14px;width: 120px'><?php _e('Password', 'helponclick'); ?></td>
                            <td>
                                <input id="helponclick_account_password" name="password_dummy" size="40" type="password" value="<?php echo $options_account_password?>" disabled />
                            </td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td>                    
                                <input type="button" name="reset" onclick="location.href='<?php echo admin_url(); ?>options-general.php?page=helponclick&reset_settings=1'" value="<?php _e("Reset", 'helponclick'); ?>" class="button-primary" />
                                <input type="submit" name="submit" value="<?php _e("Open Operator Panel", 'helponclick'); ?>" class="button-primary" />
                            </td>
                        </tr>
                    </table>
                    </form>
                    <br class="clear" />
                </div>
            </div>
            
            

<?php } else { ?>
            <div class="postbox">
                <h3><?php _e('Connect', 'helponclick'); ?></h3>
                <div class="inside">
                    <br class="clear" />
                    <form method="post" action="<?php echo admin_url(); ?>options-general.php?page=helponclick&connect_account=1">
                    <table class="form-table">
                        <tr><td style='font-size:14px;width: 120px'><?php _e('Email', 'helponclick'); ?></td>
                            <td>
                                <input id="helponclick_account_email" name="helponclick_account_email" size="40" type="text" value="<?php echo $options_account_email?>" />
                            </td>
                        </tr>
                        <tr><td style='font-size:14px;width: 120px'><?php _e('Password', 'helponclick'); ?></td>
                            <td>
                                <input id="helponclick_account_password" name="helponclick_account_password" size="40" type="password" value="<?php echo $options_account_password?>" />
                            </td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td><input type="submit" name="submit" value="<?php _e("Connect", 'helponclick'); ?>" class="button-primary" /></td>
                        </tr>
                    </table>                    
                    </form>
                    <br class="clear" />
                </div>
            </div>
<?php } ?>    

            <div class="postbox">
                <h3><?php _e('Code', 'helponclick'); ?></h3>
                <div class="inside">
                    <br class="clear" />
                    <form method="post" action="options.php">
                    <?php settings_fields('helponclick_code'); ?>
                    <p style='font-size:14px;line-height:26px;'>Advanced users - log into your account, go to Admin->Code tab to customize the implementation, <br />then copy and paste the HTML code below and click on "Update Code".</p>
                    <p><textarea id="helponclick_code_script" name="helponclick_code_script" cols="80" rows="4"><?php echo trim($options_code_script); ?></textarea>
                    </p>
                    <p><input type="submit" name="submit" value="<?php _e("Update Code", 'helponclick'); ?>" class="button-primary" /></p>
                    </form>
                    <br class="clear" />
                </div>
            </div>
                    
        </div>
    </div>
</div>
<?php } ?>