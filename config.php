<?php
//This is where you enter in your information
define('Blogpress_DB_USER', 'sarkaris_livetv'); //Your database user name
define('Blogpress_DB_PASSWORD', 'sarkaris_livetv'); //Your database password
define('Blogpress_DB_NAME', 'sarkaris_livetv'); //Your database name
define('Blogpress_DB_HOST', 'localhost'); //99% chance you won't need to change this
define ('SITE_NAME', 'Your Site'); //The name of your site
define ('SITE_URL', 'http://www.yoursite.com/blogpress/');  //The full URL of the site where Z.ips.ME is installed (including trailing slash)
define('Blogpress_USERNAME', 'admin'); //Admin username. You'll use this to log in to Z.ips.ME.  Max length 100 characters.
define('Blogpress_PASSWORD', 'admin'); //Admin password. You'll use this to log in to Z.ips.ME.  Max length 100 characters.
define('Blogpress_PASSWORD_SALT', '598dD63J321773DwCjk7X9q95'); //used with crypt function, change for added security http://php.net/manual/en/function.crypt.php
define ('IS_ENV_PRODUCTION', true); //set true if production environment, otherwise false to see error messages
?>