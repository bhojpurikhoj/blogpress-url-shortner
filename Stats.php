<?php
class Stats {
	public $url_name;
	public $total_clicks;	
	
	function __construct($url_name) {
		$this->url_name = $url_name;
		$query = "SELECT COUNT(url_name) AS urlCount FROM tbl_clicks WHERE url_name = '" . $this->url_name . "'";
		$result = $GLOBALS['DB']->query($query);
		$row = $result->fetch_assoc();
		$this->total_clicks = $row['urlCount'];			
	}
	
	function __get($name) {
		return $this->$name;
	}
	
	function __set($name, $value){
		$this->$name = $value;
	}	
	
	protected function ctFormat($num) {
		return number_format($num,0,'.',',');
	}
	
	protected function pctFormat($num,$tot,$places=2,$display_pct='y') {
		$pct = number_format((($num/$tot)*100),$places,'.',',');
		if ($display_pct == 'y') {
			$pct.= '%';
		}
		return $pct;
	}
	
	function showClicks() {
		$query = "SELECT click_time, COUNT(url_name) AS monthCount FROM tbl_clicks WHERE url_name = '" . $this->url_name . "' GROUP BY EXTRACT(MONTH FROM click_time) ORDER BY click_time DESC";
		$result = $GLOBALS['DB']->query($query);
		$row = $result->fetch_assoc();
		$num = $result->num_rows;
		if ($num > 0) {
			do {				
				echo '<tr>' . "\n";
				echo '<td>' . date('F Y', strtotime($row['click_time'])) . '</td>' . "\n";
				echo '<td>' . $this->ctFormat($row['monthCount']) . '</td>' . "\n";
				echo '<td>' . $this->pctFormat($row['monthCount'],$this->total_clicks) . '</td>' . "\n";
				echo '</tr>' . "\n";
			} while ($row = $result->fetch_assoc());
		}
	}
	
	function showDomains() {
		$query = "SELECT SUBSTRING_INDEX(REPLACE(REPLACE(REPLACE(referrer,'http://',''),'https://',''),'www.',''),'/',1) AS sDomain, COUNT(referrer) AS refCount FROM tbl_clicks WHERE url_name = '" . $this->url_name . "' GROUP BY sDomain ORDER BY refCount DESC ";
		$result = $GLOBALS['DB']->query($query);
		$row = $result->fetch_assoc();
		$num = $result->num_rows;
		if ($num > 0) {
			do {
				$referrer = $row['sDomain'];				
				if (strlen($referrer)==0) {
					$referrer = 'Direct / Unavailable';
				}
				echo '<tr>' . "\n";
					echo '<td>' . prepOutputText($referrer) . '</td>' . "\n";
					echo '<td>' . $this->ctFormat($row['refCount']) . '</td>' . "\n";
					echo '<td>' . $this->pctFormat($row['refCount'], $this->total_clicks) . '</td>' . "\n";
				echo '</tr>' . "\n";
			} while ($row = $result->fetch_assoc());
		}
	}
	
	function showReferrers() {
		$query = "SELECT referrer, COUNT(referrer) AS refCount FROM tbl_clicks WHERE url_name = '" . $this->url_name . "' GROUP BY referrer ORDER BY refCount DESC";
		$result = $GLOBALS['DB']->query($query);
		$row = $result->fetch_assoc();
		$num = $result->num_rows;
		if ($num > 0) {
			do {
				$referrer = str_replace('http://', '',$row['referrer']);
				$referrer = str_replace('www.','',$referrer);
				$column = '<span class="desktop-referrer"><a href="' . $row['referrer'] . '" target="_blank">' . prepOutputText($referrer) . '</a></span><span class="mobile-referrer"><a href="' . $row['referrer'] . '" target="_blank">' . prepOutputText(substr($referrer,0,20));
				if (strlen($referrer)>20) {
					$column.= '...';
				}
				$column.= '</a></span>';
				if (strlen($referrer)==0) {
					$referrer = 'Direct / Unavailable';
					$column = $referrer;
				}
				echo '<tr>' . "\n";
					echo '<td>' . $column  . '</td>' . "\n";
					echo '<td>' . $this->ctFormat($row['refCount']) . '</td>' . "\n";
					echo '<td>' . $this->pctFormat($row['refCount'], $this->total_clicks) . '</td>' . "\n";
				echo '</tr>' . "\n";
			} while ($row = $result->fetch_assoc());
		}
	}
	
	function showBrowsers() {
		$query = "SELECT user_agent FROM tbl_clicks WHERE url_name = '" . $this->url_name . "'";
		$result = $GLOBALS['DB']->query($query);
		$row = $result->fetch_assoc();
		$num = $result->num_rows;
		$browsers = array();
		if ($num > 0) {			
			do {
				$browser_info = $this->browserDetect($row['user_agent']);
				//create text
				if ($browser_info['name'] == 'Bot') {
					$browser = 'Bot';
				} else if (($browser_info['name'] == 'Unknown') || ($browser_info['platform']) == 'Unknown') {
					$browser = 'Unknown';
				} else {
					$browser = $browser_info['name'] . ' on ' . $browser_info['platform'];
					if ($browser_info['device'] != 'Unknown') {
						$browser.= ' ' . $browser_info['device'];
					}							
				}
				//add to array
				if (isset($browsers[$browser])) {
					$browsers[$browser]++;
				} else {
					$browsers[$browser] = 1;
				}
			} while ($row = $result->fetch_assoc());		
		}		
		//sort
		arsort($browsers); 
		//output
		foreach ($browsers as $version => $count) {
			echo '<tr>' . "\n";
				echo '<td>' . $version . '</td>' . "\n";
				echo '<td>' . $this->ctFormat($count) . '</td>' . "\n";
				echo '<td>' . $this->pctFormat($count, $this->total_clicks) . '</td>' . "\n";
			echo '</tr>' . "\n";
		}
		//print_r($browsers);
	}		
	
	function browserDetect($init = 'none') {
	
		if ($init != 'none') {
			$userAgent = strtolower($init);
		} else {
			$userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);			
		}	
		
		if (preg_match('/bot/', $userAgent)) {
			
			$name = 'Bot';
			$version = '';
			$platform = '';
			$userAgent = '';
			$device	= '';
			
		} else {

			// Identify the browser		
			if ((preg_match('/opera/', $userAgent)) || (preg_match('/opr/', $userAgent))) {
				$name = 'Opera';
			} else if (preg_match('/edge/', $userAgent)) {
				$name = 'Microsoft Edge';
			} else if ((preg_match('/msie/', $userAgent)) || (preg_match('/trident/', $userAgent))) {
				$name = 'Microsoft Internet Explorer';
			} else if (preg_match('/iemobile/', $userAgent)) {
				$name = 'IE Mobile';			
			} else if (preg_match('/webkit/', $userAgent)) {
				if (preg_match('/silk/', $userAgent)) {
					$name = 'Amazon Silk';
				} else if ((preg_match('/chrome/', $userAgent)) || (preg_match('/crios/', $userAgent))) {
					$name = 'Google Chrome';
				} else if (preg_match('/safari/', $userAgent)) {
					$name = 'Safari';
				} else {
					$name = 'Webkit';
				}			
			} else if (preg_match('/mozilla/', $userAgent) && !preg_match('/compatible/', $userAgent)) {
				$name = 'Mozilla Firefox';					
			} else {
				$name = 'Unknown';
			}

			// What version?
			if (($name == 'Microsoft Internet Explorer') && (preg_match('/.+(?:msie)[\/: ]([\d.]+)/', $userAgent, $matches))) {
				$version = $matches[1];
				//overwrite version if using "MSN Optimized IE8"
				if (preg_match('/optimizedie8/', $userAgent)) {
					$version = '8.0';
				}
				//overwrite version if using IE 7 or IE 8 based on IE 6 platform
				if ($version == '6.0') {
					if (preg_match('/msie 8.0/', $userAgent)) {
						$version = '8.0';
					}
					if (preg_match('/msie 7.0/', $userAgent)) {
						$version = '7.0';
					}		
				}						
			} else if (($name == 'IE Mobile') && (preg_match('/.+(?:iemobile)[\/: ]([\d.]+)/', $userAgent, $matches))) {
				$version = $matches[1];
			} else if (($name == 'Microsoft Edge') && (preg_match('/.+(?:edge\/)([\d.]+)/', $userAgent, $matches))) {
				$version = $matches[1];
			} else if (($name == 'Mozilla Firefox') && (preg_match('/.+(?:firefox)[\/: ]([\d.]+)/', $userAgent, $matches))) {
				$version = $matches[1];
			} else if (($name == 'Google Chrome') && (preg_match('/.+(?:chrome)[\/: ]([\d.]+)/', $userAgent, $matches))) {
				$version = $matches[1];		
			} else if (($name == 'Safari') && (preg_match('/.+(?:version)[\/: ]([\d.]+)/', $userAgent, $matches))) {
				$version = $matches[1];		
			} else if (($name == 'Opera') && (preg_match('/.+(?:opr\/)([\d.]+)/', $userAgent, $matches))) {
				$version = $matches[1];		
			} else if (($name == 'Amazon Silk') && (preg_match('/.+(?:silk\/)([\d.]+)/', $userAgent, $matches))) {	
				$version = $matches[1];
			} else if (preg_match('/.+(?:rv|it|ra|ie)[\/: ]([\d.]+)/', $userAgent, $matches)) { //take our best guess
				$version = $matches[1];
			}
			else {
				$version = 'Unknown';
			}

			// Running on what platform?
			if (preg_match('/windows|win32/', $userAgent)) {
				$platform = 'Windows';
			} else if (preg_match('/(iphone|ipod|ipad)/', $userAgent)) {
				$platform = 'iOS';		
			} else if (preg_match('/(macintosh|mac os x)/', $userAgent)) {
				$platform = 'Mac';
			} else if (preg_match('/silk/', $userAgent)) {
				$platform = 'Fire OS';
			} else if (preg_match('/android/', $userAgent)) {		
				$platform = 'Android';
			} else if (preg_match('/cros/', $userAgent)) {
				$platform = 'Chrome OS';
			} else if (preg_match('/linux/', $userAgent)) {
				$platform = 'Linux';			
			} else {
				$platform = 'Unknown';
			}

			//Using a device?
			if (preg_match('/ipad/', $userAgent)) {
				$device = 'Tablet';
			} else if (preg_match('/silk/', $userAgent)) {
				$device = 'Tablet';
			} else if (preg_match('/android/', $userAgent)) {
				if (preg_match('/mobile/', $userAgent)) {
					$device = 'Phone';
				} else {
					$device = 'Tablet';
				}	
			} else if (preg_match('/(iphone|ipod|blackberry|palm|webos|iemobile|googlebot-mobile|opera mini|opera mobi)/', $userAgent)) {
				$device = 'Phone';
			} else {
				$device = 'Unknown';
			}	
		}

		return array(
			'name'      => $name,
			'version'   => $version,
			'platform'  => $platform,
			'userAgent' => $userAgent,
			'device'    => $device		
		);
	}

}
?>