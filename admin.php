<?php include('template_declare.php'); ?>
<?php

if ( ((isset($_POST['login_submitted'])) && ($_POST['username'] == Blogpress_USERNAME) && (getEncryptedPassword($_POST['password']) == Blogpress_PASSWORD_CRYPT)) || ((isset($_COOKIE['Blogpress_admin'])) && ($_COOKIE['Blogpress_admin'] == Blogpress_PASSWORD_CRYPT)) ) {
	$logged_in = 'y';
	if ((isset($_GET['logout'])) && ($_GET['logout'] == 'y')) {
		setcookie("Blogpress_admin", "", time() - (60*60*24*365), '/');
		$logged_in = 'n';
		redirect('admin.php?logout_complete=y');
	} else {
		setcookie("Blogpress_admin", Blogpress_PASSWORD_CRYPT, time() + (60*60*24*365), '/');		
	}
	
	if (isset($_POST['url_submitted'])) {
		$url = prepQueryText($_POST['url']);
		$url_name = prepQueryText($_POST['url_name']);
		$url_name = stripLink($url_name);
		$type = $_POST['type'];
		if (linkAvailable($url_name)) {
			insertLink($url_name, $url, $type);
			$alert = '<div class=collection-item style=background:green;color:white;width:100%;>Link created successfully! <a target="_blank" href="' . SITE_URL . $url_name . '">' . SITE_URL . $url_name . '</a></div> now redirects to ' . $url;		
		} else {
			$alert = '<div class=collection-item style=background:red;color:white;width:100%;>The link name ' . $url_name . ' is already being used.  Try a different name or edit the existing link</div>';
		}
						  
	}
	
	if (isset($_POST['edit_submitted'])) {
		$url = prepQueryText($_POST['url']);
		$url_name = prepQueryText($_POST['url_name']);
		$type = $_POST['type'];
		updateLink($url_name, $url, $type);
		$alert = 'Update successful!';
	}
	
	if (isset($_GET['summary'])) { 
		$url_name = prepQueryText($_GET['summary']);
		$summary = new Stats($url_name);
		$view = 'stats';
	}
	
	if (isset($_GET['edit'])) { 
		$url_name = prepQueryText($_GET['edit']);
		$edit = new Info($url_name);
		$view = 'edit';
	}
	
	if (isset($_GET['delete'])) { 
		$url_name = prepQueryText($_GET['delete']);
		deleteLink($url_name);
		redirect('admin.php?delete_complete=' . $url_name);		
	}
	
	if (isset($_GET['delete_complete'])) {
		$url_name = prepQueryText($_GET['delete_complete']);
		$alert = $url_name . ' has been permanently deleted.';
	}
		
} else { 
	$logged_in = 'n';
	if (isset($_POST['login_submitted'])) {
		$alert = 'Incorrect username/password combination';
	} else if (isset($_GET['logout_complete'])) {	
		$alert = 'You\'ve been logged out successfully';
	}
}

?>
<?php include('template_header.php'); ?>

        <?php if (isset($alert)) { ?><p class="collection"><?php echo $alert; ?></p><?php } ?>
            <?php if ($logged_in == 'y') { ?>
            
             <div class="title_head_white"> 

<?php
if (file_exists("version.php"))
 {    
        echo "<span class='tag' style='float:right;background:green;color:white;font-weight:bold;'>1.0.0</a>
     ";
 } 
else
 {     
        echo "<span class='tag' style='float:right;background:red;color:white;font-weight:bold;'>New Update Found";
  } 
?>
</span>

<?php
  $content =  file_get_contents("https://bhojpurikhoj.com/api/blogpress/url/latest.html");
  echo $content;
?>
</div>
           
            
            
            <?php if (isset($_GET['pre_delete'])) { ?>
            <p class=chip>Are you sure you want to delete the link <span ><?php echo SITE_URL; ?>/<?php echo prepOutputText($_GET['pre_delete']) ?>?  </p>
            <a class="waves-effect green btn-small" href="admin.php?delete=<?php echo prepOutputText($_GET['pre_delete']) ?>"><i class="material-icons">delete</i> Yes</a>  <a class="waves-effect black btn-small" href="admin.php"><i class="material-icons">dashboard</i> No</a></p>
            <?php } ?>	
           
				<?php if ($view == 'stats') { ?>                                
                 <div class="collection z-depth-1 teal deep-purple white"> <div class="col s12"><h2>Statistics for <strong><?php echo $summary->url_name; ?></strong></h2>
					<?php if ($summary->total_clicks > 0) { ?>
                       <h3><?php echo $summary->total_clicks; ?> Total Clicks</h3>
                        <div id="click-summary" align="left">
                            <h3>By Month</h3>                    
                            <table>                	
                                <tr>
                                    <td><strong>Month</strong></td>
                                    <td><strong>Clicks</strong></td>
                                    <td><strong>%</strong></td>
                                </tr>
                                <?php $summary->showClicks(); ?>                                
                            </table>
                             <h3>Browsers</h3>
                            <table>                	
                                <tr>
                                    <td><strong>Browser</strong></td>
                                    <td><strong>Clicks</strong></td>
                                    <td><strong>%</strong></td>
                                </tr>
                                 <?php $summary->showBrowsers(); ?> 
                            </table>  
                            <h3>Referring Domains</h3>
                            <table>                	
                                <tr>
                                    <td><strong>Domain</strong></td>
                                    <td><strong>Clicks</strong></td>
                                    <td><strong>%</strong></td>
                                </tr>
                                <?php $summary->showDomains(); ?>                                                    
                            </table>
                            <h3>Referring Links</h3>
                            <table>                	
                                <tr>
                                    <td><strong>Referrer</strong></td>
                                    <td><strong>Clicks</strong></td>
                                    <td><strong>%</strong></td>
                                </tr>
                                <?php $summary->showReferrers(); ?>                                                    
                            </table>                           					                                                                     
                                                                             
                    <?php } else { ?>
                        <p>No clicks yet!</p>
                    <?php } ?></div></div>
                <?php } else if ($view == 'edit') { ?>
                 <h2>Edit <strong><?php echo $edit->url_name; ?></strong></h2>
                    <form action="admin.php" method="post" id="url-form" class="collection z-depth-1 teal deep-purple white"><div class="input-field col s12">
                        Original Link<input type="text" name="url" size="50" value="<?php echo $edit->url; ?>" /><br />                        
                        <select name="type"><option <?php if ($edit->type == '301') { echo 'selected="selected"'; } ?> value="301">301 Permanent Redirect</option><option <?php if ($edit->type == '302') { echo 'selected="selected"'; } ?> value="302">302 Temporary Redirect</option></select><br />
                        <input type="hidden" value="1" name="edit_submitted"/>
                        <input type="hidden" value="<?php echo $edit->url_name; ?>" name="url_name"/>
                        <input class="btn waves-effect waves-light" type="submit" value="Update" id="form-button"/>
                        </div>
                
                    </form></div></div>
                <?php } else { ?>               	
                 <div class="collection z-depth-1 teal deep-purple white"> <div class="col s12">Shorten a New Link</div></div>
                <form action="admin.php" method="post" id="url-form" class="collection z-depth-1 teal deep-purple white">  <div class="input-field col s12">
                    Original Link<input type="text" name="url" size="50" /><br />
                    New Link Name<input maxlength="255" type="text" name="url_name" /><br />
                  
                    <input type="hidden" value="301">
                    <input type="hidden" value="1" name="url_submitted"/>
                    <input class="btn waves-effect waves-light" type="submit" value="Shorten It!" id="form-button"/></div>
            	</form>
            
                
      <ul class="collection with-header">
				<li class="collection-header"><h4>All URL's</h4></li>
                    <?php showLinkHistory(); ?>
                </ul>
				<?php } ?>
				
				
				
			<?php } else { ?>
            <h2>Login</h2>
            <form action="admin.php" method="post" id="login-form" class="collection z-depth-1 teal deep-purple white">
            	<label>Username</label><input type="text" maxlength="100" name="username" /><br />
            	<label>Password</label><input type="password" maxlength="100" name="password" /><br />
                <input type="hidden" value="1" name="login_submitted"/>
                <input type="submit" value="Log In" id="form-button"/>
            </form>
			<?php } ?>
<?php include('template_footer.php'); ?>