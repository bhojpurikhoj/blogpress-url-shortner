<?php include('config.php'); ?>
<?php include('config.settings.php'); ?>
<?php

//Install script.  Run after filling in config.php file

//create clicks table
$query = "CREATE TABLE IF NOT EXISTS tbl_clicks (click_id int(11) NOT NULL auto_increment,click_time datetime NOT NULL,url_name varchar(255) NOT NULL,referrer varchar(255) NOT NULL,user_agent varchar(255) NOT NULL,ip_address varchar(255) NOT NULL,  PRIMARY KEY  (click_id))";
if ($GLOBALS['DB']->query($query) === TRUE) {	
	//create links table
	$query = "CREATE TABLE IF NOT EXISTS tbl_links (url_name varchar(255) NOT NULL,url text NOT NULL,type varchar(255) NOT NULL,active char(1) NOT NULL, PRIMARY KEY  (url_name))";
	if ($GLOBALS['DB']->query($query) === TRUE) {		
		//create index
		$query = "ALTER TABLE tbl_clicks ADD INDEX (url_name)";
		if ($GLOBALS['DB']->query($query) === TRUE) {
			echo 'Blogpress URL Shortner installed successfully!  You can now log in to your Admin page <a href="admin.php">here</a>';
		} else {
			printf("Index Error: %s\n", $mysqli->error);
		}
	} else {
		printf("Links Table Error: %s\n", $mysqli->error);
	}
} else {
	printf("Clicks Table Error: %s\n", $mysqli->error);	
	
}

?>