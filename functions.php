<?php

function getEncryptedPassword($password) {	
	return crypt($password, '$6$rounds=5000$' . Blogpress_PASSWORD_SALT . '$');
}

function prepQueryText($text) {
	$insert = $GLOBALS['DB']->real_escape_string(trim($text));
	return $insert;
}

function prepOutputText($text) {
	$output = htmlentities(stripslashes(nl2br($text)),ENT_QUOTES);
	return $output;
}

function redirect($url, $type='internal') {
	if (!headers_sent()) {
		if ($type == '301') {
			header("HTTP/1.1 301 Moved Permanently");
		}
		header("Location: $url");
	} else {
		echo '<script type="text/javascript">window.location = "' . $url . '"</script>';
	}
}

function insertClick($url_name, $referrer, $user_agent, $ip_address) {
	$query = "INSERT INTO tbl_clicks (click_time, url_name, referrer, user_agent, ip_address) VALUES (NOW(), '{$url_name}', '{$referrer}', '{$user_agent}', '{$ip_address}')";
	$result = $GLOBALS['DB']->query($query);
}

function insertLink($url_name, $url, $type) {
	$query = "INSERT INTO tbl_links (url_name, url, type, active) VALUES ('{$url_name}', '{$url}', '{$type}', 'y')";
	$result = $GLOBALS['DB']->query($query);
}

function updateLink($url_name, $url, $type) {
	$query = "UPDATE tbl_links SET url = '{$url}', type = '{$type}' WHERE url_name = '{$url_name}' LIMIT 1";
	$result = $GLOBALS['DB']->query($query);
}

function deleteLink($url_name) {
	$query = "DELETE FROM tbl_links WHERE url_name = '{$url_name}' LIMIT 1";
	$result = $GLOBALS['DB']->query($query);
	$query = "DELETE FROM tbl_clicks WHERE url_name = '{$url_name}'";
	$result = $GLOBALS['DB']->query($query);	
}

function linkAvailable($url_name) {
	$query = "SELECT url_name FROM tbl_links WHERE url_name = '{$url_name}' LIMIT 1";
	$result = $GLOBALS['DB']->query($query);
	$row = $result->fetch_assoc();
	$num = $result->num_rows;
	if ($num == 0) {
		return true;
	} else {
		return false;
	}
}

function getIpAddress() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
      $ip=$_SERVER['HTTP_CLIENT_IP'];
    }
    else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))  {
      $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
      $ip=$_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

function linkExists($url_name) {
	$query = "SELECT url_name FROM tbl_links WHERE url_name = '{$url_name}' AND active = 'y' LIMIT 1";
	$result = $GLOBALS['DB']->query($query);
	$num = $result->num_rows;
	if ($num == 1) {
		return true;
	} else {
		return false;
	}
}

function redirectClick($url_name) {
	$query = "SELECT url, type FROM tbl_links WHERE url_name = '{$url_name}' LIMIT 1";
	$result = $GLOBALS['DB']->query($query);
	$row = $result->fetch_assoc();
	redirect($row['url'], $row['type']);
}

function stripLink($url_name) {
	$stripped = preg_replace("/[^a-zA-Z0-9]/", "", $url_name);
	return $stripped;
}

function showLinkHistory() {
	//get stats
	$query = "SELECT url_name, COUNT(url_name) AS totalCount FROM tbl_clicks GROUP BY url_name ORDER BY totalCount DESC";
	$result = $GLOBALS['DB']->query($query);
	$row = $result->fetch_assoc();
	$num = $result->num_rows;	
	
	//get all links
	$query_all = "SELECT url_name FROM tbl_links";
	$result_all = $GLOBALS['DB']->query($query_all);
	$row_all = $result_all->fetch_assoc();
	$num_all = $result_all->num_rows;
	
	//combine results into one array
	$stats = array();	
	
	if ($num > 0) {
		do {
			array_push($stats, array(
				'url_name' => $row['url_name'],
				'clicks' => $row['totalCount']
			));
		} while ($row = $result->fetch_assoc());
	}
	
	$links = array();
	
	if ($num_all > 0) {
		do {
			$clicks = 0;
			if (sizeof($stats)>0) {
				foreach ($stats as $stat) {
					if ($stat['url_name'] == $row_all['url_name']) {
						$clicks = $stat['clicks'];
						break;
					}				
				}
			}
			array_push($links, array(
				'url_name' => prepOutputText($row_all['url_name']),
				'clicks' => $clicks
			));
		} while ($row_all = $result_all->fetch_assoc());
	}
	
	//sort array
	if (sizeof($stats)>0) {		
		foreach ($links as $key_sort => $row_sort) {
			$clicks_sort[$key_sort]  = $row_sort['clicks'];											
		}	
		array_multisort($clicks_sort, SORT_DESC, $links);
	}
	
	if (sizeof($links)==0) {
		echo '<tr>' . "\n";
			echo '<td>None</td>' . "\n";
			echo '<td>&nbsp;</td>' . "\n";
			echo '<td>&nbsp;</td>' . "\n";	
		echo '</tr>' . "\n";
	} else {
		foreach ($links as $link) {
			echo '<li class="collection-item">' . "\n";
				echo '' . SITE_URL . '</span>' . prepOutputText($link['url_name']) . '' . "\n";
				echo '<span class="badge blue" style="color:white;">' . $link['clicks'] . '</span>' . "\n";
				echo '<br><a href="admin.php?summary=' . $link['url_name'] . '"><i class="material-icons" style="color:black;">insert_chart</i></a><span class="options-separator"> </span><a href="admin.php?edit=' . $link['url_name'] . '"><i class="material-icons" style="color:green;">edit</i></a><span class="options-separator" style="color:red;"> </span><a href="admin.php?pre_delete=' . $link['url_name'] . '"><i class="material-icons">delete</i></a>' . '' . "\n";	
			echo '</li>' . "\n";
		}
	}		
}
?>