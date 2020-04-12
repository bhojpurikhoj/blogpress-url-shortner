<?php

ini_set('display_errors', !IS_ENV_PRODUCTION); //whether or not to display errors to users based upon production environment setting

//establish a connection to the database server
$GLOBALS['DB'] = new mysqli(Blogpress_DB_HOST, Blogpress_DB_USER, Blogpress_DB_PASSWORD, Blogpress_DB_NAME);
if ($GLOBALS['DB']->connect_errno) {
	trigger_error("Failed to connect to MySQL: " . $GLOBALS['DB']->connect_error);	
}

//autoload classes
function my_autoloader($class) {
    include $class . '.php';
}
spl_autoload_register('my_autoloader');

//include common functions
include('functions.php');

//encrypted password
define('Blogpress_PASSWORD_CRYPT', getEncryptedPassword(Blogpress_PASSWORD));

?>