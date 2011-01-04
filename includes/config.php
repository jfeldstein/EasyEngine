<?php

/*
//	EasyEngine for rapid PHP prototyping
//	- Propackaged interfaces for user authentication, granular logging, local and dev environments
//		with simple security and templating. 
//
//	TODO - Integrate FB Connect into user auth, instantiate objects lazily
*/

ob_start();
session_start();

require_once('./includes/functions.php');
require_once('./smarty/Smarty.class.php');

// Some App Specific Config
$CONFIG['domain'] = '';
$CONFIG['domainregex'] = str_replace('.','\.',$CONFIG['domain']).'$';

$CONFIG['photo_size_small']	= 80;
$CONFIG['photo_size_med']	= 250;
$CONFIG['photo_size_large']	= 500;

// Google API Keys for local domains
$GoogleKeys['localhost']	= '';
$GoogleKeys['local.rechsare.com']	= '';
$GoogleKeys['local.admin.rechsare.com']	= '';

$CONFIG['email_regex'] 		= "^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$";

// Loads all class.[className].php definitions
loadClasses();

// Sanitizes and collects all form data
$FORM 	= parse_form();
 

// Pseudo-persistant variables for handling redirects
$messages 		= $_SESSION['messages']; unset($_SESSION['messages']);
$stored_form	= (isset($_SESSION['saved_form'])) ? $stored_form= unserialize($_SESSION['saved_form']) : array(); 
				  unset($_SESSION['saved_form']);
$FORM 			= array_merge($stored_form, $FORM);

// Environment Variables
if(in_array($_SERVER['SERVER_NAME'], array('localhost', 'local.'.$CONFIG['domain'], 'local.admin.'.$CONFIG['domain'])))
{
	$CONFIG['sql_server'] 	= 'localhost';
	$CONFIG['sql_user']		= 'root';
	$CONFIG['sql_pass']		= '';
	$CONFIG['sql_dbname']	= 'recshare';
	$CONFIG['google_key']	= $GoogleKeys[$_SERVER['SERVER_NAME']];
	$CONFIG['localhost']	= true;
}
 elseif($_SERVER['SERVER_NAME']=='dev.'.$CONFIG['domain'])
{
	$CONFIG['sql_server'] 	= 'mysql.'.$CONFIG['domain'];
	$CONFIG['sql_user']		= 'rentdev';
	$CONFIG['sql_pass']		= 'jG48_kkD';
	$CONFIG['sql_dbname']	= 'rentals';
	$CONFIG['google_key']	= 'ABQIAAAAXZYjCdXPNEKSbJuUce-5ohTu6bTww8vswIKRbdPN-KUEBeqOJRThptc_Ee5arSSv5Tm4T8IKZOpivg';
}
 elseif($_SERVER['SERVER_NAME']=='www.'.$CONFIG['domain'])
{
	$CONFIG['sql_server'] 	= 'mysql.'.$CONFIG['domain'];
	$CONFIG['sql_user']		= 'rentdev';
	$CONFIG['sql_pass']		= 'jG48_kkD';
	$CONFIG['sql_dbname']	= 'rentals';
	$CONFIG['google_key']	= 'ABQIAAAAXZYjCdXPNEKSbJuUce-5ohTXYM5G9pR2Fj9urmlhE4sA1Q45qhTTycT2AJqo3XgWVNMBthaSV02xeA';
}

// Database Object
$db = new SQL($CONFIG['sql_server'], $CONFIG['sql_user'], $CONFIG['sql_pass'], $CONFIG['sql_dbname']);
if(!$db->connected) exit(mysql_error(). " - Therefore, no connection to database");
$db->ShowErrors();


// Some utility classes
$smarty = new Smarty();
$mail= new PHPMailer();
	$mail->Host     = "localhost";
	$mail->Mailer   = "smtp";
	$mail->IsSMTP();

// Custom utility classes
$AllUsers 		= new AllUsers;
$AllComputers	= new AllComputers;

// Publicizing Environment variables
$smarty->assign('localhost', $CONFIG['localhost']);
$smarty->assign('google_key', $CONFIG['google_key']);  // Assign this domains google API key
$smarty->assign('form', $FORM);
$smarty->register_function('json', 'make_json');


// Authentication and security
if(!isset($_SESSION['user_id'])){
	if($FORM['email']!='' AND $FORM['pass']!='' AND ($User = $AllUsers->find_by_email_and_passhash($FORM['email'], md5($FORM['pass'])))!==false)
		$_SESSION['user_id'] = $User->id;
}

if($require_login && !isset($_SESSION['user_id']))
{	$smarty->assign('forward_to', $_SERVER['PHP_SELF']);
	$smarty->assign('body', $smarty->fetch('login.tpl'));
	$smarty->display('index.tpl');
	exit;
}
 elseif(isset($_SESSION['user_id']))
{	
	if(!$User)
		$User = new User($_SESSION['user_id']);
	$smarty->assign('logged_in_as', $User->email);
	$smarty->assign('user', $User->to_array());
}

// Enable Logging
$Computer = (!isset($_COOKIE['cid'])) ? $AllComputers->new_computer() : $AllComputers->find_by_id($_COOKIE['cid']);

// Tracking referrals
$referralArray = parse_url($_SERVER['HTTP_REFERER']);
if(eregi($CONFIG['domainregex'], $referralArray['host']) === false AND $referralArray['host'] <> '')
{
	$smarty->assign('referrer_host', $referralArray['host']);
	$Computer->log('camefrom', $referralArray['host']);
}

?>