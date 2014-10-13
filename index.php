<?php
include 'library.php';
?><?php 
$filename = 'blogpress/version.php';
if (!file_exists($filename)) { 
echo "<a href='install/index.php'><font style='color:#555555;font-family:verdana'>It seems to be script is not installed.</font></a>";
die();
}
//include database connection details
include('blogpress/config.php');

//redirect to real link if URL is set
if (!empty($_GET['url'])) {
	$redirect = mysql_fetch_assoc(mysql_query("SELECT url_link FROM urls WHERE url_short = '".addslashes($_GET['url'])."'"));
	$redirect = "http://".str_replace("http://","",$redirect[url_link]);
	header('HTTP/1.1 301 Moved Permanently');  
	header("Location: ".$redirect);  
}
//

//insert new url
if ($_POST['url']) {

//get random string for URL and add http:// if not already there
$short = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 5);

mysql_query("INSERT INTO urls (url_link, url_short, url_ip, url_date) VALUES

	(
	'".addslashes($_POST['url'])."',
	'".$short."',
	'".$_SERVER['REMOTE_ADDR']."',
	'".time()."'
	)

");

$redirect = "&bp-key=$short";
header('Location: '.$redirect); die;

}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo $lang["title"]; ?></title>
  <link href="blogpress/css/styles.css" rel="stylesheet" type="text/css" />
</head>

<body>
<div id="central">
    <table width="80%" align="center" border="0">
      <tr>
        <td align="center">
          <img src="blogpress/css/logo.png" /><br><br>
          <?php echo $lang["longurl"]; ?></td>
      </tr>
    </table>
<center>
<select onChange="window.location = '?lang='+this.value+''">
<option value="" selected="selected" disabled="disabled">Changed your language</option>
<option value="en">English - Global</option>
<option value="hi">Hindi - India</option>
<option value="bh">Bhojpuri - India</option>
</select><form id="form1" name="form1" method="post" action="">
  <input name="url" type="text" id="url" value="http://" size="75" style=
          "font-size: 18px; border: #cccccc 1px solid ; background-color: #F8F8F8">
  <input type="submit" name="Submit" value="<?php echo $lang["submit"]; ?>" id="incinput">
</form></center>


<?php if (!empty($_GET['bp-key'])) { ?>
<br />
<center>
<h2><?php echo $lang["output"]; ?><a href="<?php echo $server_name; ?>url/<?php echo $_GET['bp-key']; ?>" target="_blank"><?php echo $server_name; ?>url/<?php echo $_GET['bp-key']; ?></a></h2>
<input type="text" value="<?php echo $server_name; ?>url/<?php echo $_GET['bp-key']; ?>" style="width:210px;"><?php } ?>


<br />
<br />
<?php echo $lang["copyright"]; ?>
</body>
</html>
