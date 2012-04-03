<?php
/*************************************************/
/*                                               */
/* PHP Power Browse                              */
/* Devin Smith (php@arzynik.com)                 */
/* 2004-01-31 http://devin.la                    */
/*                                               */
/* Icons are by Mark James (famfamfam.com)       */
/*                                               */
/*************************************************/

/* Config */
$GLOBALS['CONFIG']['PAGE_TITLE'] = 'PHP Power Browse';
$GLOBALS['CONFIG']['THUMB_HEIGHT'] = 16;
$GLOBALS['CONFIG']['THUMB_WIDTH'] = 16;
$GLOBALS['CONFIG']['DISPLAY_HIDDEN'] = FALSE;
$GLOBALS['CONFIG']['PROCESS_INDEX'] = FALSE;
$GLOBALS['CONFIG']['BG_IMAGE'] = '';

$GLOBALS['CONFIG']['ENABLE_LINE_COUNT'] = true;
$GLOBALS['CONFIG']['ENABLE_SOURCE_VIEW'] = true;
$GLOBALS['CONFIG']['ENABLE_IMAGE_ICONS'] = false;
$GLOBALS['CONFIG']['ENABLE_RECURSIVE_SUMMARY'] = true;

$GLOBALS['CONFIG']['PROTECTED_USER'] = 'user';
$GLOBALS['CONFIG']['PROTECTED_PASSWORD'] = 'pass';

$GLOBALS['CONFIG']['BG_COLOR_1'] = '#8888aa';
$GLOBALS['CONFIG']['BG_COLOR_2'] = '#f0f0f0';
$GLOBALS['CONFIG']['BG_COLOR_3'] = '#f0f0f0';
$GLOBALS['CONFIG']['TXT_COLOR_1'] = '#000000';
$GLOBALS['CONFIG']['TXT_COLOR_2'] = '#003399';
$GLOBALS['CONFIG']['TB_COLOR_1'] = '#dddddd';
$GLOBALS['CONFIG']['TB_COLOR_2'] = '#C1C1DF';
$GLOBALS['CONFIG']['TB_COLOR_3'] = '#BDBDD6';
$GLOBALS['CONFIG']['TB_COLOR_4'] = '#FFFFFF';

$GLOBALS['CONFIG']['IGNORE_DIRS'] = array('./logs');
$GLOBALS['CONFIG']['HIDE_FILES'] = array('./index.php');
$GLOBALS['CONFIG']['DENY_SOURCE'] = array('index.php');
$GLOBALS['CONFIG']['PROTECTED_DIRS'] = array('./protected');
$GLOBALS['CONFIG']['CODE_FILES'] = array('php','php4','php3','phtml','html','htm','js','asp','xml','css','bml','cgi','cfm','apm','jhtml','xhtml','aspx','tpl','inc','c','h','vb','py','sh','pl','cpp','cs');
$GLOBALS['CONFIG']['IMG_FILES'] = array('jpg','jpeg','gif','png');
$GLOBALS['CONFIG']['INDEX_FILES'] = array('index.php','index.html','index.htm','index.asp','default.asp','default.htm');

$GLOBALS['STRINGS']['401_TITLE'] = 'Error 401 Unauthorized';
$GLOBALS['STRINGS']['401_CONTENT'] = 'You do not have permission to access this directory. Please contact the webmaster for permission.';
$GLOBALS['STRINGS']['SUMMARY_CODE'] = '%1$s lines of code in %2$s files within %3$s directories.';
$GLOBALS['STRINGS']['SUMMARY_NOCODE'] = '%2$s files within %3$s directories.';
$GLOBALS['STRINGS']['CURRENT_DIRECTORY'] = 'Current Directory';
$GLOBALS['STRINGS']['FILE_NAME'] = 'File Name';
$GLOBALS['STRINGS']['FILE_MODIFIED'] = 'Last Modified';
$GLOBALS['STRINGS']['FILE_SIZE'] = 'Size';
$GLOBALS['STRINGS']['SOURCE_SUMMARY'] = '%1$s has %2$s lines, size is %3$s bytes, last modified on %4$s';



/* BAD Security */
$dir = $_GET['dir'];
$basedir = '.';
if (ereg('[.][.]',$dir)) $dir = '';
if (ereg('[.][.]',$file)) $file = '';
elseif (ereg('[.]/',$dir)) $dir = ereg_replace('[.]/','',$dir);
elseif (!eregi($basedir,$dir)) $dir = '';
elseif (!$dir) $dir = '';
elseif ($dir == '.') $dir = '';
while (ereg('//',$dir)) $dir = ereg_replace('//','/',$dir);
while (ereg('//',$file)) $file = ereg_replace('//','/',$file);
if ($dir{strlen($dir)-1} == '/') $dir{strlen($dir)-1} = '';
if ($dir && $dir{0} != '/') $dir = '/'.$dir;
if ($dir{0} == '/') $dirlink = substr($dir,1).'/';
elseif (!$dir) $dirlink = substr($dir,1);
if (!is_dir($basedir.$dir)) header('Location: '.$_SERVER['SCRIPT_NAME']);
$file = ereg_replace('[.]/','',$file);

if (!$GLOBALS['CONFIG']['BG_IMAGE']) $GLOBALS['CONFIG']['BG_IMAGE'] = $_SERVER['SCRIPT_NAME'].'?p=logo';

if (isprotecteddir($dir) && $_GET['p'] != 'logo') {
    if ($_SERVER['PHP_AUTH_USER'] != $GLOBALS['CONFIG']['PROTECTED_USER'] && $_SERVER['PHP_AUTH_PW'] != $GLOBALS['CONFIG']['PROTECTED_PASSWORD']) {
  	header('WWW-Authenticate: Basic realm="'.$GLOBALS['CONFIG']['PAGE_TITLE'].'"');
		header('HTTP/1.0 401 Unauthorized');
		showmessage($GLOBALS['STRINGS']['401_TITLE'],$GLOBALS['STRINGS']['401_CONTENT']);
	}
}

set_time_limit(0);

/* Output */


function listdir($directory) {
	global $dirlink, $basedir;
	$handle=opendir($basedir.$directory);
	while ($file = readdir($handle)) $filelist[] = $file;
	$count = 1;
	natcasesort($filelist);
	while (list ($key, $file) = each ($filelist)) {
		if ($GLOBALS['CONFIG']['PROCESS_INDEX'] && in_array($file,$GLOBALS['CONFIG']['INDEX_FILES'])) header('Location: '.substr($directory,1).'/'.$file);
		if (($GLOBALS['CONFIG']['DISPLAY_HIDDEN'] || (!$GLOBALS['CONFIG']['DISPLAY_HIDDEN'] && $file{0} != ".")) 
	 	 	&& $file != '.' 
	 	 	&& $file != '..' 
	 	 	&& (!is_array($GLOBALS['CONFIG']['HIDE_FILES']) || (is_array($GLOBALS['CONFIG']['HIDE_FILES']) && !in_array($basedir.$directory.'/'.$file,$GLOBALS['CONFIG']['HIDE_FILES'])))) {
		 	$stats = stat($basedir.$directory.'/'.$file);
		 	if ($basedir.$directory.'/' == './') $dirlink = '';
		 	else $dirlink = $basedir.$directory.'/';
	
		 	if (is_dir($basedir.$directory.'/'.$file)) {
		 		$dirc[] = '<td width="100%"><table cellpadding="0" cellspacing="0" width="100%"><tr><td width="18">'.
		 		 	 	 	 '<img src="'.$_SERVER['SCRIPT_NAME'].'?p=mime&amp;type='.getmime($directory.'/'.$file).'" border="0" alt=""><td align="left"><a href="'.$_SERVER['SCRIPT_NAME'].'?dir='.$dirlink.$file.'">'.$file.'</a></table>'.
		 		 	 	 	 '<td nowrap>'.date('F jS Y',$stats['9']).'<td nowrap> - ';
		 	} elseif(iscodetype($file) && $GLOBALS['CONFIG']['ENABLE_SOURCE_VIEW']) {
		 		$filec[] = '<td width="100%"><table cellpadding="0" cellspacing="0" width="100%"><tr><td width="18"><a href="'.$dirlink.url_encode($file).'"><img src="'.$_SERVER['SCRIPT_NAME'].'?p=mime&amp;type='.getmime($directory.'/'.$file).'" border="0" alt=""></a></td>'.
		 		 	 	 		'<td align="left"><a href="'.$_SERVER['SCRIPT_NAME'].'?p=source&amp;file='.url_encode($dirlink.$file).'">'.$file.'</a></table>'.
		 		 	 	 		'<td nowrap>'.date('F jS Y',$stats['9']).'<td nowrap>'.getsize($stats['7']); 
		 	} elseif(isimgtype($file) && $GLOBALS['CONFIG']['ENABLE_IMAGE_ICONS']) {
		 		$filec[] = '<td width="100%"><table cellpadding="0" cellspacing="0" width="100%"><tr><td width="18"><a href="'.$dirlink.url_encode($file).'"><img src="'.$_SERVER['SCRIPT_NAME'].'?p=thumb&amp;file='.base64_encode($directory.'/'.$file).'" border="0" alt=""></a></td>'.
		 		 	 	 		'<td align="left"><a href="'.$dirlink.url_encode($file).'">'.$file.'</a></table>'.
		 		 	 	 		'<td nowrap>'.date('F jS Y',$stats['9']).'<td nowrap>'.getsize($stats['7']); 
		 	} else {
		 		$filec[] = '<td width="100%"><table cellpadding="0" cellspacing="0" width="100%"><tr><td width="18"><a href="'.$dirlink.url_encode($file).'"><img src="'.$_SERVER['SCRIPT_NAME'].'?p=mime&amp;type='.getmime($directory.'/'.$file).'" border="0" alt=""></a></td>'.
		 		 	 	 		'<td align="left"><a href="'.$dirlink.url_encode($file).'">'.$file.'</a></table>'.
		 		 	 	 		'<td nowrap>'.date('F jS Y',$stats['9']).'<td nowrap>'.getsize($stats['7']); 
		 	}
		}
	}

	pagehead($directory);

	$dirdis = '.'.$directory.'/';
	echo '<tr><td colspan="3" nowrap><b>'.$GLOBALS['STRINGS']['CURRENT_DIRECTORY'].': ';
	while($basepos = strpos($dirdis,'/')) {
		if ($dirlist[$t-1] == '') $dirlist[$t] = substr($dirdis,0,$basepos); 
		else $dirlist[$t] = $dirlist[$t-1].'/'.substr($dirdis,0,$basepos); 
		echo '/<a href="'.$_SERVER['SCRIPT_NAME'].'?dir='.$dirlist[$t].'">'.substr($dirdis,0,$basepos).'</a>';
		$dirdis = substr($dirdis,$basepos+1,strlen($dirdis));
		$t++;
	}

	echo '</b><tr class="head"><td width="100%" bgcolor="'.$GLOBALS['CONFIG']['TB_COLOR_4'].'">'.$GLOBALS['STRINGS']['FILE_NAME'].'<td nowrap bgcolor="'.$GLOBALS['CONFIG']['TB_COLOR_4'].'">'.$GLOBALS['STRINGS']['FILE_MODIFIED'].'<td nowrap bgcolor="'.$GLOBALS['CONFIG']['TB_COLOR_4'].'">'.$GLOBALS['STRINGS']['FILE_SIZE'];
	if ($dirc) {
		asort($dirc);
		foreach ($dirc as $dir) {
			$tcoloring	= ($a % 2) ? $GLOBALS['CONFIG']['TB_COLOR_2'] : $GLOBALS['CONFIG']['TB_COLOR_3']; 
			echo '<tr bgcolor="'.$tcoloring.'">'.$dir;
			$a++;
		}
	}
	if ($filec) {
		asort($filec);
		foreach ($filec as $file) {
			$tcoloring	= ($a % 2) ? $GLOBALS['CONFIG']['TB_COLOR_2'] : $GLOBALS['CONFIG']['TB_COLOR_3']; 
			echo '<tr bgcolor="'.$tcoloring.'">'.$file;
			$a++;
		}
	}

	$dir = $directory;
	if (!$dir) $dir = './'; else $dir = '.'.$directory;
	$count = countdir($dir);

	echo '<tr bgcolor="'.$GLOBALS['CONFIG']['TB_COLOR_4'].'"><td>';

	if ($GLOBALS['CONFIG']['ENABLE_LINE_COUNT']) 
		printf($GLOBALS['STRINGS']['SUMMARY_CODE'], '<b>'.number_format($count[1]).'</b>', '<b>'.number_format($count[2]).'</b>','<b>'.number_format($count[3]).'</b>');
	else
		printf($GLOBALS['STRINGS']['SUMMARY_NOCODE'], '<b>'.number_format($count[1]).'</b>', '<b>'.number_format($count[2]).'</b>','<b>'.number_format($count[3]).'</b>');

	echo '<td> <td nowrap><b>'.getsize($count[0]).'</b>';
	pagefoot();


}

function getsize($size) {
	if ($size != 0) { 
		if ($size>=1099511627776) $size = round($size / 1024 / 1024 / 1024 / 1024, 2).' TB';
		elseif ($size>=1073741824) $size = round($size / 1024 / 1024 / 1024, 2).' GB';
		elseif ($size>=1048576) $size = round($size / 1024 / 1024, 2).' MB';
		elseif ($size>=1024) $size = round($size / 1024, 2).' KB';
		elseif ($size<1024) $size = round($size / 1024, 2).' Bytes';
	}
	return $size;
}


function countdir($dir, $level_count = 0) {
	global $totallines, $totalfiles, $totaldirs, $totalbytes;
	if (!@($thisdir = opendir($dir))) return;
	while ($item = readdir($thisdir)) if (is_dir($dir.'/'.$item) && (substr($item, 0, 1) != '.') && !in_array($dir.$item,$GLOBALS['CONFIG']['IGNORE_DIRS'])) countdir($dir.'/'.$item, $level_count + 1);
	if ($level_count >= 0) {
		$dir = ereg_replace('[/][/]', '/', $dir);
		$handle=opendir($dir);
		while ($file = readdir($handle)) {
		 	if ($file != '.' && $file != '..' && !is_dir($file)) { 
		 	 	$totalfiles++;
		 	 	if ($GLOBALS['CONFIG']['ENABLE_LINE_COUNT']) {
			 	 	if(iscodetype($file)) {
			 	 		$lines = file($dir.'/'.$file);
			 	 		$linecount = 0;
			 	 		foreach ($lines as $line) if (substr(eregi_replace(' ','',$line),0,2) != '//'|'/*') $linecount++; 
			 	 		$totallines = $totallines + $linecount;
			 	 		$totalbytes = $totalbytes + filesize($dir.'/'.$file);
			 	 	}
		 	 	}
		 	}
		}
		$totaldirs++;
	}
	return array (intval($totalbytes), intval($totallines), intval($totalfiles), intval($totaldirs));
}


function pagehead($stitle) {
	if ($stitle) $dtitle = $GLOBALS['CONFIG']['PAGE_TITLE']." : ".$stitle;
	else $dtitle = $GLOBALS['CONFIG']['PAGE_TITLE'];
	echo 	'<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">'.
	 		'<html lang="en">'.
	 		'<head>'.
	 		'<title>'.$dtitle.'</title>'.
	 		'<meta http-equiv="Content-Type" content="text/html;charset=utf-8" >'.
			'<link rel="shortcut icon" href="'.$_SERVER['SCRIPT_NAME'].'?p=favicon">'.
	 		'<style type="text/css">'.
	 		'td { font-size : 80%;font-family : tahoma;color: '.$GLOBALS['CONFIG']['TXT_COLOR_1'].';}'.
	 		'A:visited {color: '.$GLOBALS['CONFIG']['TXT_COLOR_2'].';font-weight: bold;text-decoration: underline;}'.
	 		'A:hover {color: '.$GLOBALS['CONFIG']['TXT_COLOR_1'].';font-weight: bold;text-decoration: underline;}'.
	 		'A:link {color: '.$GLOBALS['CONFIG']['TXT_COLOR_2'].';font-weight: bold;text-decoration: underline;}'.
	 		'A:active {color: #a6a6a6;font-weight: bold;text-decoration: underline;}'.
	 		'textarea {border: 1px solid '.$GLOBALS['CONFIG']['TXT_COLOR_2'].' ;color: black;background-color: white;}'.
	 		'input.button{border: 1px solid '.$GLOBALS['CONFIG']['TXT_COLOR_2'].';color: black;background-color: white;}'.
	 		'input.text{border: 1px solid '.$GLOBALS['CONFIG']['TXT_COLOR_2'].';color: black;background-color: white;}'.
	 		'BODY {color: '.$GLOBALS['CONFIG']['TXT_COLOR_1'].'; FONT-SIZE: 10pt; FONT-FAMILY: arial,verdana,helvetica; scrollbar-base-color: '.$GLOBALS['CONFIG']['BG_COLOR_2'].'; MARGIN: 0px 0px 10px; BACKGROUND-COLOR: '.$GLOBALS['CONFIG']['BG_COLOR_1'].'}'.
	 		'.title {COLOR: '.$GLOBALS['CONFIG']['TXT_COLOR_1'].'; font: 13.5pt arial; FONT-WEIGHT: bold;}'.
	 		'.code {font-size: 10pt;}'.
	 		'.head {COLOR: '.$GLOBALS['CONFIG']['TXT_COLOR_1'].'; font-size: 11pt; FONT-WEIGHT: bold;}'.
	 		'.copyright {FONT-SIZE: 8pt; FONT-FAMILY: arial,verdana,helvetica; COLOR: #FFFFFF}'.
	 		'.content {text-decoration: none; color: black; font: 10pt arial;}'.
	 		'</style>'.
	 		'</head><body>'.
	 		'<table cellpadding="0" cellspacing="0" align="center"><tr><td>'.
	 		'<table cellpadding="0" cellspacing="0" bgcolor="'.$GLOBALS['CONFIG']['BG_COLOR_2'].'"><tr><td>'.
	 		'<table width="100%" border="0" cellpadding="2" cellspacing="0">'.
	 		'<tr><td align="left" width="700" bgcolor="'.$GLOBALS['CONFIG']['TB_COLOR_4'].'"><img src="'.$GLOBALS['CONFIG']['BG_IMAGE'].'" alt="'.$pagetitle.'"> '.$pagetitle.

	 		'<tr><td>'.
	 		'<table cellpadding="0" cellspacing="0" width="100%"><tr><td width="100%">'.
	 		'<table width="100%">';

}

/* All I ask is that if you use my script you keep my footer on here */
function pagefoot() {
	echo '</table></table></table></table>'. 
	 		'<table width="100%"><tr><td align="right">'. 
	 		'<font class="copyright">'.
	 		'PHP Power Browse &copy; 2004-'.date('Y').' <a href="http://devin.la">devin.la</a>'.
	 		'</font></table></table>';
	exit;
}


/* display source of file */
function showsource($file) {
	if (file_exists($file) && !is_dir($file) && !(is_array($GLOBALS['CONFIG']['DENY_SOURCE']) && in_array($file,$GLOBALS['CONFIG']['DENY_SOURCE']))) {
		pagehead($file);
		$content = highlight_file($file, 1);
		$linecount = substr_count($content, "<br />") + 1;
		$size = number_format(filesize($file), 0, ' ', ' ');
		$date = date('F d Y H:i:s.', filemtime($file));

		echo '<tr><td bgcolor='.$GLOBALS['CONFIG']['BG_COLOR_3'].'><b>'
			.sprintf($GLOBALS['STRINGS']['SOURCE_SUMMARY'],basename($file),$linecount, $size, $date)
			.'</b></font><table cellspacing="0" width="100%" border="0" cellpadding="2" class="code"><tr valign="top"><td bgcolor="'.$GLOBALS['CONFIG']['TB_COLOR_3'].'" width="0" align="right"><code>';
		for($i=1; $i<=$linecount; $i++) echo '<a name='.$i.'></a><a href="#'.$i.'">'.$i.'</a><br>';
		echo '<td nowrap width="100%">';
		print_r ($content);
		echo '</table>';
		pagefoot();
	} else {
		if (!file_exists($file) || is_dir($file)) $error = 'No such file named "'.$file.'".';
		pagehead("Error");
		echo '<font class="error"><b>'.$error.'</b></font>';
		pagefoot();
	}
}


/* displays a basic message */
function showmessage($title,$message) {
	pagehead($title);
	echo '<tr><td><table cellpadding="3" cellspacing="2" align="center" border="0"><tr><td><b>'.$title.'</b><br /><br />'.$message.'<br /><br /></td></tr></table></td></tr>';
	pagefoot();

}


/* determines if file is web type which is viewable in the source viewer */
function iscodetype($file) {
	$ext = pathinfo($file);
	$ext = strtolower($ext['extension']);
	if (in_array($ext,$GLOBALS['CONFIG']['CODE_FILES'])) return true;
	else return FALSE;
}


/* determines if tile is an img type which can display a thumbnail */
function isimgtype($file) {
	$ext = pathinfo($file);
	$ext = strtolower($ext['extension']);
	if (in_array($ext,$GLOBALS['CONFIG']['IMG_FILES'])) return true;
	else return FALSE;
}


/* determines if tile is an img type which can display a thumbnail */
function isprotecteddir($dir) {
	if ($dir != '' && $dir != '/') $dir = '.'.$dir;
	elseif ($dir == '') $dir = './';
	if (in_array($dir,$GLOBALS['CONFIG']['PROTECTED_DIRS'])) return true;
	else return FALSE;
}


/* make thumbnails from images */
function makethumb($image,$h,$w) {

	$image = $GLOBALS['basedir'].'/'.$image;

	if (substr($image,0,1) == '/') $image = substr($image,1);
	$imgname = $image;
	$img = $image;
	
	$image = @imagecreatefromjpeg($img);
	if (!$image) $image = @imagecreatefrompng($img);
	if (!$image) $image = @imagecreatefromgif($img);
	if (!$image) $error = TRUE;

	if (!$error) {
		$imagedata = getimagesize($img); 

		if ($imagedata[0] > $imagedata[1]) {
			$whold = $w/$imagedata[0];
			$wa = $w;
			$ha = $imagedata[1]*$whold;
		} else {
			$hhold = $h/$imagedata[1];
			$ha = $h;
			$wa = $imagedata[0]*$hhold;
		}

		$thumb = @imagecreatetruecolor ($wa, $ha);
		if (!$thumb) $thumb = @imagecreate ($wa, $ha);
		imagecopyresized ($thumb, $image, 0, 0, 0, 0, $wa, $ha, $imagedata[0], $imagedata[1]);
	} else {
		$thumb	= imagecreate($w, $h/2);
		$bgc = imagecolorallocate($thumb, 255, 255, 255);
		$tc	= imagecolorallocate($thumb, 0, 0, 0);
		imagestring($thumb, 1, 5, 5, "Error loading", $tc);
		imagestring($thumb, 1, 5, 20, "'$imgname'", $tc);
	}
	if (!$thumb) {
		header('Location: '.$_SERVER['SCRIPT_NAME'].'?p=mime&type=image');
		die();
	}
	header('Content-type: image/jpeg'); 
	imagejpeg($thumb); 
	imagedestroy($thumb);
}


/* return mime type */
function getmime($file) {
	if (substr($file,0,1) == '/') $file = substr($file,1);

	$ext = pathinfo($file);
	$ext = strtolower($ext['extension']);
	$mime = escapeshellarg($file);
	$mime = trim(`file -bi $mime`);
	if (strstr($mime,','))	$mime = substr($mime,0,strpos($mime,','));

	if (is_dir($file)) 													$type = 'folder';
	elseif (preg_match('/^application(.*?)compress|zip(.*?)$/',$mime) || $ext == 'tar' || $ext == 'rar' || $ext == 'ace' || $ext == 'z' || $ext == 'gz') $type = 'archive';

	elseif ($ext == 'cs') 												$type = 'cs';
	elseif ($ext == 'cpp') 												$type = 'cpp';
	elseif ($mime == 'text/x-c' || $ext == 'c') 						$type = 'c';

	elseif (($mime == 'application/octet-stream' || $ext == 'rpm' || $ext == 'exe' || $ext == 'bin') && (preg_match('/^(.*?)setup(.*?)$/',$file) || preg_match('/^(.*?)install(.*?)$/',$file))) $type = 'setup';
	elseif ($mime == 'application/inf' || $ext == 'inf') 				$type = 'inf';
	elseif ($ext == 'php' || $ext == 'phtml' || $ext == 'php3' || $ext == 'php4' || $ext == 'php5' || preg_match('/^application(.*?)php(.*?)$/',$mime)) $type = 'php';	elseif ($mime == 'text/html' || $ext == 'html' || $ext == 'htm') $type = 'html'; 
	elseif (preg_match('/^application(.*?)shockwave|flash(.*?)$/',$mime) || $ext == 'swf' || $ext == 'fla') $type = 'swf';

	elseif ($ext == 'csv') 												$type = 'csv';
	elseif ($ext == 'rpm') 												$type = 'rpm';
	elseif ($ext == 'deb') 												$type = 'deb';
	elseif ($ext == 'mk') 												$type = 'makefile';
	elseif ($ext == 'sln') 												$type = 'sln';
	elseif ($ext == 'suo') 												$type = 'suo';
	elseif ($ext == 'res') 												$type = 'res';
	elseif ($ext == 'resx') 											$type = 'resx';
	elseif ($ext == 'csproj') 											$type = 'csproj';
	elseif ($ext == 'ai') 												$type = 'ai';
	elseif ($ext == 'mysql') 											$type = 'mysql';
	elseif ($ext == 'ico') 												$type = 'ico';

	elseif ($mime == 'text/x-h') 										$type = 'h';
	elseif ($mime == 'application/msword') 								$type = 'msword';
	elseif ($mime == 'application/x-javascript') 						$type = 'javascript';
	elseif ($mime == 'application/xml' || $mime == 'text/xml') 			$type = 'xml';
	elseif ($mime == 'application/postscript') 							$type = 'postscript';
	elseif ($mime == 'application/pdf') 								$type = 'pdf';
	elseif ($mime == 'application/octet-stream')						$type = 'exe';
	elseif ($mime == 'text/asp') 										$type = 'asp';

	elseif (preg_match('/^application(.*?)powerpoint(.*?)$/',$mime))	$type = 'powerpoint';
	elseif (preg_match('/^application(.*?)cert(.*?)$/',$mime)) 			$type = 'crt';
	elseif (preg_match('/^(.*?)readme(.*?)$/',$file)) 					$type = 'readme';
	elseif (preg_match('/^application(.*?)java(.*?)$/',$mime)) 			$type = 'java';
	elseif (preg_match('/^application(.*?)excel$/',$mime)) 				$type = 'excel';
	elseif (preg_match('/^application(.*?)binary$/',$mime)) 			$type = 'binary';
	elseif (strstr($mime,'text/')) 										$type = 'text';
	elseif (strstr($mime,'image/')) 									$type = 'image';
	elseif (strstr($mime,'video/')) 									$type = 'video';
	elseif (strstr($mime,'audio/')) 									$type = 'audio';
	else 																$type = 'unknown';
	return $type;
}


/* display stored image */
function getmimefile($file) {
	switch ($file) {
		case 'logo':
			$img['data'] = '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';
			$img['type'] = 'gif';
			break;

		case 'folder':
			$img['data'] = 'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAGrSURBVDjLxZO7ihRBFIa/6u0ZW7GHBUV0UQQTZzd3QdhMQxOfwMRXEANBMNQX0MzAzFAwEzHwARbNFDdwEd31Mj3X7a6uOr9BtzNjYjKBJ6nicP7v3KqcJFaxhBVtZUAK8OHlld2st7Xl3DJPVONP+zEUV4HqL5UDYHr5xvuQAjgl/Qs7TzvOOVAjxjlC+ePSwe6DfbVegLVuT4r14eTr6zvA8xSAoBLzx6pvj4l+DZIezuVkG9fY2H7YRQIMZIBwycmzH1/s3F8AapfIPNF3kQk7+kw9PWBy+IZOdg5Ug3mkAATy/t0usovzGeCUWTjCz0B+Sj0ekfdvkZ3abBv+U4GaCtJ1iEm6ANQJ6fEzrG/engcKw/wXQvEKxSEKQxRGKE7Izt+DSiwBJMUSm71rguMYhQKrBygOIRStf4TiFFRBvbRGKiQLWP29yRSHKBTtfdBmHs0BUpgvtgF4yRFR+NUKi0XZcYjCeCG2smkzLAHkbRBmP0/Uk26O5YnUActBp1GsAI+S5nRJJJal5K1aAMrq0d6Tm9uI6zjyf75dAe6tx/SsWeD//o2/Ab6IH3/h25pOAAAAAElFTkSuQmCC';
			$img['type'] = 'png';
			break;
		case 'archive':
			$img['data'] = 'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAEUSURBVCjPXdFNSsMAEIbh0Su4teAdIgEvJB5C14K4UexCEFQEKfivtKIIIlYQdKPiDUTRKtb0x6ZJ+volraEJ3+zmycwkMczGzTE3lwkbxeLE5XTqQfTIjhIm6bCy9E/icoOoyR4v7PLDN+8ibxQHxGzE3JBfHrgUalDnQ6BNk1WRFPjs66kDNTxqg0Uh5qYg4IkrjrS9pTWfmvKaBaGaNU4EY+Lpkq88eKZKmTAhbd3i5UFZg0+TzV1d1FZy4FCpJCAQ8DUnA86ZpciiXjbQhK7aObDOGnNsUkra/WRAiQXdvSwWpBkGvQpnbHHMRvqRlCgBqkm/dd2745YbtofafsOcPiiMTc1fzNzHma4O/XLHCtgfTLBbxm6KrMIAAAAASUVORK5CYII=';
			$img['type'] = 'png';
			break;
		case 'cs':
			$img['data'] = 'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAJOSURBVDjLjZPbaxNBFMarf4cFwb9AIgXBR18Enyw+i1gs4g01kphSlPjQeAtNzNqGNLVpNCGhEvBS21Rr0ZIK6ovFiKbNbXNpdpNsstncUz9nNiauErEDHwMz8/1mzjlz+gD0UZGxh0hFNPAf7SXa3fUpAKparVZoNpvbrVYLvUT2YbFYTEqIEjBAzZIkoVwud1UsFiEIAjKZjAxJp9NgGKYL6Zh3UQA9UK1WUa/X5ZmqVCqhUCiA4zgZUKlUQC+xWq1tCAUM3v6+74hu2cH4eUz6OcwFcvgYEmUANYiiiFF3Aq5XHIJRCeqHLOJbFcg5OW6Mqm495fL2NznYl7OwveYxsZSF6QUHEpIc9+eQgOvuFL6EMjC6wrg4GZZfIwOGbazX8TaPY/qAr5Ms72oOBt8WknwVem8KWmcCY0/S0E1HcXYyhjNMBAYH2waYF8izl3I4eGLqmjLjz9by+PRNxCMS0k0C0c+yMDjj0MwmMOGJ4+Vqtg0Yn+dwf5HH/sG75/4uWzAiwbfCQ+dMYSGQxdhMHMPmMFY+8MgX623AiDu9+YAADg35LErzHU8SGkcSI4+T0DoSuGRnoZ5mcdIUwdC9zd85OHpjQzP+nMOVmZj4NSZBKNVh9LbN6xslnGai8CxmMP+Ol81criwntgugZTysDmovTEXEUVcKV8lt520s5kjJvP4MTpkjyApVXCZmvTWKRqMh6w9A5yO9Xy9ijUgZCi1lL/UEkMUf/+qDHtruAn5BDpAvXKYbOzGTsyW5exWAfgrZQTt3RFu//yfHVsX/fi5tjwAAAABJRU5ErkJggg==';
			$img['type'] = 'png';
			break;
		case 'cpp':
			$img['data'] = 'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAH/SURBVDjLjZPNaxNRFMWrf4cFwV13JVKXLuta61apIChIV0rblUqhjYpRcUaNboxIqxFTQgVti4hQQTe1C7FFSUmnmvmM85XJzCSpx3efzmTSRtqBw7yZ9+5v7rl3bg+AHhK7DjClmAZ20UGm/XFcApAKgsBqNptbrVYL3cT2IQjCnSQkCRig4FqtBs/zYtm2DdM0oaoqh8iyDFEUY0gUvI8AdMD3fYRhyO8k13VhWRY0TeOAer0O+kg2m/0LIcDx9LdDgxff5jJzKjJzCmbe6fi0anEABTiOA13Xd1jiNTlxfT01UVB/CfMG7r/WILxScaOo4FpeBrPEfUdWDMPgmVQqlTbgtCjls4sGjl16PxuRny5oGH3yA7oZoPjR4BDbqeHlksLrUa1W24DJWRU3Wer9Qw/Gk+kVmA2lGuDKtMQzsVwfl6c3eE3IUgyYeCFjsqCgb3DqQhJwq/gTY7lyV61Jdhtw7qFUSjNA/8m8kASkc5tYXnN4BvTs1kO23uAdIksx4OjI19Grzys4c7fkfCm5MO0QU483cf5eGcurNq8BWfD8kK11HtwBoDYeGV4ZO5X57ow8knBWLGP49jqevVF5IKnRaOxQByD6kT6smFj6bHb0OoJsV1cAe/n7f3PQRVsx4B/kMCuQRxt7CWZnXT69CUAvQfYwzpFo9Hv/AD332dKni9XnAAAAAElFTkSuQmCC';
			$img['type'] = 'png';
			break;
		case 'c':
			$img['data'] = 'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAHdSURBVDjLjZNLS+NgFIad+R0KwuzcSQddunTWXraKA4KCuFKcWYqgVbE4TKJWNyqC2oHKoDBeEBF04UpFUVQqUoemSVOTJr2lrb5+5xsTUy+jgYdc3yfnnOQrAVBCsK2U4WFUvUE546OTcwk82WxWz+fzt4VCAS/B7kMQhB9uiVtQReFkMolUKuWQSCSgaRpkWeYSSZIgiqIjscMfSEAPZDIZWJbF94RpmtB1HYqicEE6nQa9xO/3/5OQoM57/qm2a3PGtyzDtxzF/FYMe6c6F1DAMAzEYrFnLfGZ1A9devqC8o2wpmL8jwJhRcbw7ygGAxJYS7xvuxVVVXklkUjkUdAshgP+DRVfureXbPPcuoKe2b/QDKtIQpXQPOLx+KOgf0nGCCu9smHiu7u8IGuDBHRsS6gdmgmJHEHfLwn9wSgqagc6Xvt8RC6X48MlCeEI2ibDIS8TVDYGBHfAO3ONowvTOacqSEBQNY6gpvOkp3cxgq8/Q8ZxyISWsDAwfY32sSscnhk8SFAFBIWLBPQZq1sOvjX5LozOqTBaxSu0jF5iYVV+FnZTJLB/pN0DDTv7WlHvtuQpLwrYxbv/DfIJt47gQfKZDShFN94TZs+afPW6BGUkecdytqGlX3YPTr7momspN0YAAAAASUVORK5CYII=';
			$img['type'] = 'png';
			break;

		case 'sln':
		case 'suo':
		case 'res':
		case 'resx':
		case 'csproj':
			$img['data'] = 'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAJQSURBVDjLjZNvSBNxGMeX9O+FOAkaLbehozdGRGiMQqTIlEqJMIig3oxl0YxcgYt6FUZRryLYwpFWCr2wXgjBIMJMYhFjgZSiEXOg5c5N593udne7u+2+3V3tT22SBx/uxe/5fu7uuefRAdCpKJdJoVHB9h9qFSryuSJBYzqdpiRJymYyGZRDOYfH43lULCkW2NRwKpUCy7J5kskkSJJELBbTJARBwOv15iW58AZVoBbwPA9BELS7CsMwoCgK8XhcE3AcB/UhPp/vtyQnGBi03pYXjyAbPQuRD2sSbmUFVN9NLJ5ux9DryZJP0nqiChzjl48Oh9oYRPTAXBVksgnS0hRWu7uxXG/EfL0ZZ9yjGHgb1t4kGo0WBO6AvcUVsFP9oTZZjlQCP7ZA/r4JpHM3lup2Im6pRsRai2PX/GjoDWEk8BWJRKIg6P147mfP+CW63d16RUyOQP5SA6rLAsKyA0TNNizvM4D9/A4Tk2Ec7nuPE0+vgqbpgqBnzLl6vv8N3+x4eEsS0mAvHAJhMoAw6kHUVUF4rkeWHAKXZtA15kDL6C6tkXmBffiZs/P+NE7dC4pBhwsJY6USVjBtBO/bCswrbfq2GS+Ce9DwyooHoRvaPPzVxI67IVfHnQA+2JqQMFQgur0anP8J5IVmYEopmdbh5YQO1wMu0BxdKlB/44GLg48/HT8J8uBesH6/ViDxC5DnWiHPWjAz0wleYCGKokaJIDdI/6JMZ1nWEshr7UEZsnnBH8l+ZfpY9WA9YaWW0ba3SGBWJetY5xzq6pt/AY6/mKmzshF5AAAAAElFTkSuQmCC';
			$img['type'] = 'png';
			break;

		case 'inf':
			$img['data'] = 'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAEkSURBVCjPbdE9S0IBGIbhxxobWxP8D8r5I60RLg0NNTS21VBRQwg1aA4VOAWBpBVCFhKUtkVJtPQx9GFFWh49x3P0bvAjjsWzXrzvcAtpREEZfQtoACEkpKBVdpouv7NYi3SJkAynWcXExKTCJ6+4PLPeIZJPhksdmzp1vilTwqVGlWhEgR6wsbGpU+OLt94rGfJ1gIOLi4OFSYV3Sjx5QXdtkiHFx//gjiwlTshyT5LV3T8gwy3HFLnhkCuWmB3qA0Uu2WGOZVIUmN/ru5CiwAsLNLCI8cg+i3hAggMeiNOgwQbXRJnwghoX5DkiTow0OcLJ8HAbtLpkkzwJCuTY4pQppgeFFLJNtxMrzSRFtlnhvDXO6Fk7ll8hb+wZxpChoPzoB6aiXIYcSLDWAAAAAElFTkSuQmCC';
			$img['type'] = 'png';
			break;
		case 'php':
			$img['data'] = 'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAGsSURBVDjLjZNLSwJRFICtFv2AgggS2vQLDFvVpn0Pi4iItm1KItvWJqW1pYsRemyyNILARbZpm0WtrJ0kbmbUlHmr4+t0z60Z7oSSAx935txzvrlPBwA4EPKMEVwE9z+ME/qtOkbgqtVqUqPRaDWbTegE6YdQKBRkJazAjcWapoGu6xayLIMoilAoFKhEEAQIh8OWxCzuQwEmVKtVMAyDtoiqqiBJEhSLRSqoVCqAP+E47keCAvfU5sDQ8MRs/OYNtr1x2PXdwuJShLLljcFlNAW5HA9khLYp0TUhSYMLHm7PLEDS7zyw3ybRqyfg+TyBtwl2sDP1nKWFiUSazFex3tk45sXjL1Aul20CGTs+syVY37igBbwg03eMsfH9gwSsrZ+Doig2QZsdNiZmMkVrKmwc18azHKELyQrOMEHTDJp8HXu1hostG8dY8PiRngdWMEq467ZwbDxwlIR8XrQLcBvn5k9Gpmd8fn/gHlZWT20C/D4k8eTDB3yVFKjX6xSbgD1If8G970Q3QbvbPehAyxL8SibJEdaxo5dikqvS28sInCjp4Tqb4NV3fgPirZ4pD4KS4wAAAABJRU5ErkJggg==';
			$img['type'] = 'png';
			break;
		case 'html':
			$img['data'] = 'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAHtSURBVDjLjZM9T9tQFIYpQ5eOMBKlW6eWIQipa8RfQKQghEAKqZgKFQgmFn5AWyVDCipVQZC2EqBWlEqdO2RCpAssQBRsx1+1ndix8wFvfW6wcUhQsfTI0j33PD7n+N4uAF2E+/S5RFwG/8Njl24/LyCIOI6j1+v1y0ajgU64cSSTybdBSVAwSMmmacKyLB/DMKBpGkRRZBJBEJBKpXyJl/yABLTBtm1Uq1X2JsrlMnRdhyRJTFCpVEAfSafTTUlQoFs1luxBAkoolUqQZbmtJTYTT/AoHInOfpcwtVtkwcSBgrkDGYph+60oisIq4Xm+VfB0+U/P0Lvj3NwPGfHPTcHMvoyFXwpe7UmQtAqTUCU0D1VVbwTPVk5jY19Fe3ZfQny7CE51WJDXqpjeEUHr45ki9rIqa4dmQiJfMLItGEs/FcQ2ucbRmdnSYy5vYWyLx/w3EaMfLmBaDpMQvuDJ65PY8Dpnz3wpYmLtApzcrIAqmfrEgdZH1grY/a36w6Xz0DKD8ES25/niYS6+wWE8mWfByY8cXmYEJFYLkHUHtVqNQcltAvoLD3v7o/FUHsNvzlnwxfsCEukC/ho3yUHaBN5Buo17Ojtyl+DqrnvQgUtfcC0ZcAdkUeA+ye7eMru9AUGIJPe4zh509UP/AAfNypi8oj/mAAAAAElFTkSuQmCC';
			$img['type'] = 'png';
			break;

		case 'h':
			$img['data'] = 'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAHtSURBVDjLjZNLS9xQFMe138C9A/0OynyBUjeFQjduROi2MMtCEalS0ToLEdQMdEShoKDWRymKigWxII7PhaB9aBFUJjHJpHlnnvbfe27NJcVIDfwIyT3nd885cOoA1BHsaWQ0MZL/4SHjgciLCJpKpZJVrVava7Ua4mDnkCRpKCqJCpKU7HkefN8X2LYN0zShqiqXKIqCTCYjJGFyPQkooFgsolwu8zfhui4sy4KmaVwQBAHokmw2+1cSClpSUmr12MP7LQunii8klOA4DnRdv9USn0koePRiJDW+aTGBjcOLgAewlnjfYSuFQoFXIsvybQF9jG2avIKFPQtzOyZmcyZMtywkVAnNwzCMeMG7jV+YyFmQ1g30L2kYWitAWtZFJdQOzYREsYLhzwZGGF+OHez/9PD2k4aeeYUHVyoVPheSELGCwRUdA+zG/VMPeycu3iyo6J5WxDxIQFA1QtCauUwPrOpIPh/vSC+qSC/qPHn3u4uu2Su8nsrzZKqAoOR/BO2j+Q+DTPC0/2CdSu79qOLVlIyXk3l0zsjomJYxv6ELQYgQPOk7a2jpOnmcaG57tvuD3fzNxc5XB9sEm0XuyMb5VcCriBI7A/bz9117EMO1ENxImtmAfDq4TzKLdfn2RgQJktxjnUNo9RN/AFmTwlP7TY1uAAAAAElFTkSuQmCC';
			$img['type'] = 'png';
			break;
		case 'msword':
			$img['data'] = 'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAIdSURBVDjLjZO7a5RREMV/9/F9yaLBzQY3CC7EpBGxU2O0EBG0sxHBUitTWYitYCsiiJL0NvlfgoWSRpGA4IMsm43ZXchmv8e9MxZZN1GD5MCBW8yce4aZY1QVAGPMaWAacPwfm8A3VRUAVJWhyIUsy7plWcYQgh7GLMt0aWnpNTADWFX9Q2C+LMu4s7Oj/X5/xF6vp51OR1utloYQtNls6vLy8kjE3Huz9qPIQjcUg/GZenVOokIEiSBBCKUSQ+TFwwa1Wo2iKBARVlZW3iwuLr7izssPnwZ50DLIoWz9zPT+s/fabrf/GQmY97GIIXGWp28/08si5+oV1jcGTCSO6nHH2pddYqmkaUq320VECCFQr9cBsBIVBbJcSdXQmK7Q6Qsnq54sj2gBplS896RpSpIkjI2NjVZitdh7jAOSK6trXcpC2GjlfP1esHD+GDYozjm893jvSZJkXyAWe+ssc6W5G9naLqkaw/pGxBrl1tVpJCrWWpxzI6GRgOQKCv2BYHPl5uUatROeSsVy7eIkU9UUiYoxBgDnHNbagw4U6yAWwpmphNvXT6HAhAZuLNRx1iDDWzHG/L6ZEbyJVLa2c54/PgsKgyzw5MHcqKC9nROK/aaDvwN4KYS7j959DHk2PtuYnBUBFUEVVBQRgzX7I/wNM7RmgEshhFXAcDSI9/6KHQZKAYkxDgA5SnOMcReI5kCcG8M42yM6iMDmL261eaOOnqrOAAAAAElFTkSuQmCC';
			$img['type'] = 'png';
			break;
		case 'javascript':
			$img['data'] = 'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAIvSURBVDjLjZPLaxNRFIeriP+AO7Gg7nRXqo1ogoKCK0Fbig8QuxKhPop04SYLNYqlKpEmQlDBRRcFFWlBqqJYLVpbq6ktaRo0aWmamUxmJpN5ZvKoP++9mmlqWuzAt7jc+X2Hcy6nDkAdhXxbCI2Epv+wlbDeyVUJGm3bzpVKpcVyuYyVIPcIBAL3qiXVgiYaNgwDpmk6qKoKRVEgCAKT8DyPYDDoSCrhdYHrO9qzkdOQvp+E+O04hC+tED63gBs+QiDnhQgTWJYFWiQUCv2RUEH/g4YNXwdcT/VEJ6xkF8zEDRixq1CnriD94SikH08gikJNS2wmVLDwybONH3GbNt8DY+YMrDk/tGkvhOFmKPE+pxVJkpDJZMBx3JJAHN+/MTPq8amxdtj8fWjhwzB+diH5ag9y8V6QubDhUYmmaWwesiwvCYRRtyv9ca9oc37kk3egTbbBiPowP+iGOHGT0A1h7BrS43ehiXHous5EjoCEx3IzF6FMnYMcPgs95iOCW1DDXqTfnEBqsBnRR9shTvYibyhsiBRHwL13dabe7r797uHOx3Kkm1T2IDfhhTRyAfMDh5Aauox8Ns5aKRQKDNrSsiHSZ6SHoq1i9nkDuNfHkHi2D9loHwtSisUig4ZXFaSG2pB8cZBUPY+ila0JV1Mj8F/a3DHbfwDq3Mtlb12R/EuNoKN10ylLmv612h6swKIj+CvZRQZk0ou1hMm/OtveKkE9laxhnSvQ1a//DV9axd5NSHlCAAAAAElFTkSuQmCC';
			$img['type'] = 'png';
			break;
		case 'xml':
			$img['data'] = 'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAHdSURBVDjLjZNPaxNBGIdrLwURLznWgkcvIrQhRw9FGgy01IY0TVsQ0q6GFkT0kwjJId9AP4AHP4Q9FO2hJ7El2+yf7OzMbja7Sf0578QdNybFLjwszLu/Z2femZkDMEfI54FkRVL4Dw8l8zqXEawMBgM2HA6vR6MRZiHraDabH7KSrKBA4SAIEIahxvd9eJ6HbrerJKZpotVqaUkavkMC+iCKIsRxrN6EEAKMMViWpQT9fh/0k3a7PZZkBUPmqXAKCSjAOYdt21NLUj1JBYW7C6vi6BC8vKWKQXUXQcNA5Nh6KY7jqJl0Op1JwY/Hi7mLp/lT/uoA/OX2WLC3C9FoQBwfILKulIRmQv1wXfevwHmyuMPXS5Fv1MHrFSTmhSomnUvw/Spo3C+vg3/+pJZDPSGRFvilNV+8PUZvoziKvn+d3LZvJ/BelMDevIZXK2EQCiUhtMDM53bY5rOIGXtwjU3EVz/HM5Az8eplqPFKEfzLR91cOg8TPTgr3MudFx+d9owK7KMNVfQOtyQ1OO9qiHsWkiRRUHhKQLuwfH9+1XpfhVVfU0V3//k4zFwdzjIlSA/Sv8jTOZObBL9uugczuNaCP5K8bFBIhduE5bdC3d6MYIkkt7jOKXT1l34DkIu9e0agZjoAAAAASUVORK5CYII=';
			$img['type'] = 'png';
			break;
		case 'postscript':
		case 'ai':
			$img['data'] = 'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAIWSURBVDjLhZNPbxJRFMWhRrYu3NrExIUbdzWte6M7d34Eo2Hjxm8gwZUxIYEARUKAWgwbV0BpxAW11bpQFrCoCVEMDplhQMow782/enx3WsiU0jrJ2bz7zu+9e95cHwAfSXzXhFaEVv+j60JLM58HsGIYxsi27SPHcbBIoo5oNBrxQryAVTJPJhPouu6q0+mgVquh0WhAlmUX0uv1EIvFZpCp2U8A2sA5h2maYIyhUChA0zTU63UoiuICaJ0OSSaTx5B5AJnpqqVSCbmNTWxVt9FsNtHv98+05GYyD7AsC5VKBZvFd/j2k6Etc6gjHfLgELKiujeRJGkxQGSAYDCIx8+eI/ORIb3Lkf0sWvmio9aaoC2NoQ7+QFUHCwFr5XIZ8bfvhZFhq2XgU9tEb2Tj99DCgcTx9YeOg64GZTCGPQdYEnpaLBbxZl9HfIejo1rg5nGvti3CMyxouonhIYM8ZG7NBWSz2YepVKobiUR+UXjrwry+wzBm9qnAqD03YHohbsASUP+ly2u+XC7XzmQyt9LpdJc2xuscr0ULU9NUFC6JDiFRCy4gn88/EWqFw+EEmfL7HK8+8FOAqdmrWYjC7E8kElcCgcAdWmx2LbzY5mCmc+YWXp33H/w1LQehKhPPZuK8mTjR0QxwArktQtKpsLHHEarwC81ir+ZOrwewTBCiXr157/7d0PfqjQcvH10w1jT6y/8A/nHJHcAgm2AAAAAASUVORK5CYII=';
			break;
		case 'pdf':
			$img['data'] = 'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAHhSURBVDjLjZPLSxtRFIfVZRdWi0oFBf+BrhRx5dKVYKG4tLhRqlgXPmIVJQiC60JCCZYqFHQh7rrQlUK7aVUUfCBRG5RkJpNkkswrM5NEf73n6gxpHujAB/fOvefjnHM5VQCqCPa1MNoZnU/Qxqhx4woE7ZZlpXO53F0+n0c52Dl8Pt/nQkmhoJOCdUWBsvQJ2u4ODMOAwvapVAqSJHGJKIrw+/2uxAmuJgFdMDUVincSxvEBTNOEpmlIp9OIxWJckMlkoOs6AoHAg6RYYNs2kp4RqOvfuIACVFVFPB4vKYn3pFjAykDSOwVta52vqW6nlEQiwTMRBKGygIh9GEDCMwZH6EgoE+qHLMuVBdbfKwjv3yE6Ogjz/PQ/CZVDPSFRRYE4/RHy1y8wry8RGWGSqyC/nM1meX9IQpQV2JKIUH8vrEgYmeAFwuPDCHa9QehtD26HBhCZnYC8ucGzKSsIL8wgsjiH1PYPxL+vQvm5B/3sBMLyIm7GhhCe90BaWykV/Gp+VR9oqPVe9vfBTsruM1HtBKVPmFIUNusBrV3B4ev6bsbyXlPdkbr/u+StHUkxruBPY+0KY8f38oWX/byvNAdluHNLeOxDB+uyQQfPCWZ3NT69BYJWkjxjnB1o9Fv/ASQ5s+ABz8i2AAAAAElFTkSuQmCC';
			$img['type'] = 'png';
			break;
		case 'exe':
			$img['data'] = 'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAFiSURBVBgZpcEhbpRRGIXh99x7IU0asGBJWEIdCLaAqcFiCArFCkjA0KRJF0EF26kkFbVVdEj6/985zJ0wBjfp8ygJD6G3n358fP3m5NvtJscJYBObchEHx6QKJ6SKsnn6eLm7urr5/PP76cU4eXVy/ujouD074hDHd5s6By7GZknb3P7mUH+WNLZGKnx595JDvf96zTQSM92vRYA4lMEEO5RNraHWUDH3FV48f0K5mAYJk5pQQpqIgixaE1JDKtRDd2OsYfJaTKNcTA2IBIIesMAOPdDUGYJSqGYml5lGHHYkSGhAJBBIkAoWREAT3Z3JLqZhF3uS2EloQCQ8xLBxoAEWO7aZxros7EgISIIkwlZCY6s1OlAJTWFal5VppMzUgbAlQcIkiT0DXSI2U2ymYZs9AWJL4n+df3pncsI0bn5dX344W05dhctUFbapZcE2ToiLVHBMbGymS7aUhIdoPNBf7Jjw/gQ77u4AAAAASUVORK5CYII=';
			$img['type'] = 'png';
			break;
		case 'asp':
			$img['data'] = 'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAJQSURBVDjLjZNvSBNxGMeX9O+FOAkaLbehozdGRGiMQqTIlEqJMIig3oxl0YxcgYt6FUZRryLYwpFWCr2wXgjBIMJMYhFjgZSiEXOg5c5N593udne7u+2+3V3tT22SBx/uxe/5fu7uuefRAdCpKJdJoVHB9h9qFSryuSJBYzqdpiRJymYyGZRDOYfH43lULCkW2NRwKpUCy7J5kskkSJJELBbTJARBwOv15iW58AZVoBbwPA9BELS7CsMwoCgK8XhcE3AcB/UhPp/vtyQnGBi03pYXjyAbPQuRD2sSbmUFVN9NLJ5ux9DryZJP0nqiChzjl48Oh9oYRPTAXBVksgnS0hRWu7uxXG/EfL0ZZ9yjGHgb1t4kGo0WBO6AvcUVsFP9oTZZjlQCP7ZA/r4JpHM3lup2Im6pRsRai2PX/GjoDWEk8BWJRKIg6P147mfP+CW63d16RUyOQP5SA6rLAsKyA0TNNizvM4D9/A4Tk2Ec7nuPE0+vgqbpgqBnzLl6vv8N3+x4eEsS0mAvHAJhMoAw6kHUVUF4rkeWHAKXZtA15kDL6C6tkXmBffiZs/P+NE7dC4pBhwsJY6USVjBtBO/bCswrbfq2GS+Ce9DwyooHoRvaPPzVxI67IVfHnQA+2JqQMFQgur0anP8J5IVmYEopmdbh5YQO1wMu0BxdKlB/44GLg48/HT8J8uBesH6/ViDxC5DnWiHPWjAz0wleYCGKokaJIDdI/6JMZ1nWEshr7UEZsnnBH8l+ZfpY9WA9YaWW0ba3SGBWJetY5xzq6pt/AY6/mKmzshF5AAAAAElFTkSuQmCC';
			$img['type'] = 'png';
			break;
		case 'swf':
			$img['data'] = 'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAHYSURBVDjLjZPLSxtRFMa1f0UXCl0VN66igg80kQZtsLiUWhe14MKFIFHbIEF8BNFFKYVkkT9GKFJooXTToq2gLkQT82oyjzuvO8nXe65mmIkRHfg2c+/3O+d8l9MBoIMkvi6hkNDAA3om9MTz+QAhy7JqnPO667poJ3GOdDr92Q/xAwbIrOs6GGOeFEVBtVpFoVCQkHw+j0wm40Ga5k4C0AXTNGHbNsxv32Hu7YNtp1Cr1VAsFiXAMAxQkWw2ewNpBZDZPjiA+XYebioJ9nIKqqqiVCrdGUlm0gpwzs5hzrwGX1uGMTMLtvrBG6VcLstOcrncPQDOYW3tgCffw0isg4uqnP6J8AhCnVAelUqlPYD/PYE59wZ67BXsL4fg/6ryYhNC82uaJkFtAdbHT+CJFbgbCagjYbDNlDev4zgyH4KQ7gA2n/fMUWWeiAtzBMrgWABAXciAhaibAKAYnXyaGx3/5cSXoIajsH/8hHP8B87llTSSqAMSmQMAfSL2VYtET5WRCLcW3oHt7Aaq+s1+eQAt/EJXh8MNe2kRSmwa/LoQeOsmpFUeQB0ag9I/jIve0G/n6Lhx3x60Ud3L4DbIPhEQo4PHmMVdTW6vD9BNkEesc1O0+t3/AXamvvzW7S+UAAAAAElFTkSuQmCC';
			$img['type'] = 'png';
			break;

		case 'csv':
		case 'mysql':
			$img['data'] = 'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAHVSURBVDjLjZPLaiJBFIZNHmJWCeQdMuT1Mi/gYlARBRUkao+abHUhmhgU0QHtARVxJ0bxhvfGa07Of5Iu21yYFPyLrqrz1f+f6rIRkQ3icca6ZF39RxesU1VnAVyuVqvJdrvd73Y7+ky8Tk6n87cVYgVcoXixWNByuVSaTqc0Ho+p1+sJpNvtksvlUhCb3W7/cf/w+BSLxfapVIqSySRlMhnSdZ2GwyHN53OaTCbU7/cFYBgG4RCPx/MKub27+1ur1Xqj0YjW6zWxCyloNBqUSCSkYDab0WAw+BBJeqLFtQpvGoFqAlAEaZomuc0ocAQnnU7nALiJ3uh8whgnttttarVaVCgUpCAUCgnQhMAJ+gG3CsDZa7xh1mw2ZbFSqYgwgsGgbDQhcIWeAHSIoP1pcGeNarUqgFKpJMLw+/0q72azkYhmPAWIRmM6AGbXc7kc5fN5AXi9XgWACwAguLEAojrfsVGv1yV/sVikcrksAIfDIYUQHEAoPgLwT3GdzWYNdBfXh3xwApDP5zsqtkoBwuHwaSAQ+OV2u//F43GKRCLEc5ROpwVoOngvBXj7jU/wwZPPX72DT7RXgDfIT27QEgvfKea9c3m9FsA5IN94zqbw9M9fAEuW+zzj8uLvAAAAAElFTkSuQmCC';
			$img['type'] = 'png';
			break;
		case 'rpm':
		case 'deb':
		case 'setup':
			$img['data'] = 'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAALnSURBVDjLfZNLaFx1HIW/e2fuzJ00w0ymkpQpiUKfMT7SblzU4kayELEptRChUEFEqKALUaRUV2YhlCLYjYq4FBeuiqZgC6FIQzBpEGpDkzHNs5PMTJtmHnfu6//7uSh2IYNnffg23zmWqtIpd395YwiRL1Q0qyIfD56cmOvUs/4LWJg40auiH6jI+7v3ncybdo2Hy9ebKvqNGrn03Nj1+x0Bi1dHHVV9W0U+ye4d2d83+Ca2GJrlGZx0gkppkkfrsysqclFFvh8++3v7CWDh6ugIohfSPcPH+w6fwu05ABoSby9yb3Kc/mePYXc9TdCqslWapVGdn1Zjxo++O33Fujtx4gdEzj61f8xyC8/jN2rsVOcxYZOoVSZtBewZOAT+NonuAWw3S728wFZpFm975cekGjlz8NXLVtSo0SxPImGdtFfFq5epr21wdOxrnMwuaC2jrRJWfYHdxRfIFeDWr0unkyrSUqxcyk2TLQzQrt6hqydPvidDBg/8VTAp8DegvYa3OU1z+SbuM6dQI62kioAAVgondwAnncWvzCDNCk4CLO9vsJVw8xqN+iPiTB5SaTSKURGSaoTHHgxoAMlduL1HiFMZXP8BsvkbO1GD2O3GpLOIF0KsSBijxmCrMY+FqgGJQDzQgGT3XrJ7DuI5EKZd4iDG+CHG84m8AIki1Ai2imRsx4FEBtQHCUB8MG1wi8QKGhjEC4mbAVHTx8kNYSuoiGurkRtLN76ivb0K6SIkusCEoBEgaCQYPyT2QhKpAXKHTiMmQ2lmChWZTrw32v9TsLOyVlu8Nhi2G4Vs32HsTC9IA2KPRuU2Erp097+O5RRYvz3H1r3JldivfY7IR0+mfOu7l3pV5EM1cq744mi+OPwaRD71tSk0Vsp3/uLB6s2minyrIpeOf7a00fFMf1w+MqRGzqvIW/teecdqV5a5P/8ncXv9ZxUdf/lCae5/3/hvpi4OjajIp4ikVOTLY+cXr3Tq/QPcssKNXib9yAAAAABJRU5ErkJggg==';
			$img['type'] = 'png';
			break;
		case 'makefile':
			$img['data'] = 'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAH3SURBVDjLjZNPaxpRFMXTfo4E+h1S8hmy7bJ0G+i60ECg2QXaIqVaqwttplXCSEMNDlZTqdEaEkyslIo4s9BG4yIOYzIo/v+Xnrz7UofR2DYDh1m8d37v3vPenQMwR2LfPNMi09J/dI/pruEzARZ7vV59OBxejkYjzBJbh8PheGOGmAFLZG61Wmi328jlchBFEYIgIB6PQ9M0DlFVFU6n04CMzXcIQBu63S5qtRp8Ph/K5TKy2SxCoRCq1SoHdDod0CEul+saMg3o9/vI5/MIh8OoVCpwu92QJAmBQACxWGyiJZ7JNIC1gUKhgGAwCEVR4PF4YLfbkUgkYLFYeCUE/ifA7/cjnU7z8lOpFP8nk0lYrVbenq7rswFJpQ7bThFbgUPIsoxoNMqzoCpsNhuHNZtNNBqNm4Anr3btL7dPIR2dY917Aqe4xyvJZDL8RuhkyodCJE0Ann8srW2Ipd+fj3VEfujYimtYea1MBEatEYBE78EAMOPqM2+pLx1dIPxdx6cDqqCID19VbhprMBhwkXkCsCqcdHcOzxE8vsD2fhVr74vwRlXjVLPZLAPw+G2hthmpQPym4em7XxAiZzdKnyUD8PCF/OjBhlxfXv/ZcX85Y/Qh/jYLU7o0QvxzE/dZQG1auI2Z7W3y6TUBFghyi3Eei0Z/4QrVT8W6WBitpQAAAABJRU5ErkJggg==';
			$img['type'] = 'png';
			break;

		case 'powerpoint':
			$img['data'] = 'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAHeSURBVDjLjZO/i1NBEMc/u+/lBYxiLkgU7vRstLEUDyxtxV68ykIMWlocaGHrD1DxSAqxNf4t115jo6DYhCRCEsk733s7u2PxkuiRoBkYdmGZz3xndsaoKgDGmC3gLBDxbxsA31U1AKCqzCBXsywbO+e8iOgqz7JM2+32W+AiYFX1GGDHOeen06mmabrwyWSio9FI+/2+ioj2ej3tdDoLiJm+bimAhgBeUe9RmbkrT5wgT97RaDQoioIQAt1ud7/Var1h+uq+/s9+PLilw+FwqSRgJ1YpexHSKenHF4DFf/uC3b7CydsPsafraO5IkoTxeEwIARGh2WwCYNUJAOmHZ5y4eY/a7h4hPcIdHvDz/fMSnjviOCZJEiqVCtVqdfEl8RygHkz9DLZWQzOHisd9OizfckcURRhjMMbMm14CQlEC/NfPjPd2CSJQCEEEDWYBsNZijFkaCqu5Ky+blwl5geaOUDg0c8TnNssSClkER1GEtXYZcOruI6ILl1AJqATirW02Hr8sFThBVZfklyXMFdQbbDzdXzm78z4Bx7KXTcwdgzs3yizuzxAhHvVh4avqBzAzaQa4JiIHgGE9C3EcX7ezhVIgeO9/AWGdYO/9EeDNX+t8frbOdk0FHhj8BvUsfP0TH5dOAAAAAElFTkSuQmCC';
			$img['type'] = 'png';
			break;
		case 'crt':
			$img['data'] = 'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAJUSURBVDjLhZPPS1RRHMU/997nzDihTx0zFxrRxkqyQKNaBBGCWzetXUhYCUKLfrhtEdFGCqEWQf9AuK5FQouiokWrFoYuBioZ480o47x57/5q0cw4ReUXzvKce8/3fI/w3gMghBgCDgGK/08JKHrvHQDeexoi4/V6vaK1tsYY/zfU63W/vLz8EDgCSO894sKtF2Z4IKcS5XHG4qzHGEdeKDaKEasPpnDOkaYphUKBra0tVlZWHs3Pzy8BxSCXzzJ+cpC1qEaaGoy2OOMYKXSytl5CSon3HiEEAGEYMjMzsxAEAXNzc0vSGmsq2zFHuzpIYk1SN4z0dVKuxOAsSimklC2BTCZDLpdjdnZ2ARiQ1pibqx++plE55nghz4n+PFF5l1dvNrhx+TRSSpRSZDIZKpUKURRRKpVaGxX23VD4fnPs+bGe4uQBVcVZTznO8M1PcerSXVBdWGt/g3OOMAwJguBs4CyLZ0bHJkXfHZxLkGabXr1N1+ZL4s/3yY/dQynVjBohRDM1AKTTtWuifxqrK9i4iN1dx8YbyL4Jdj4+aRGbVpRSBEHQEgis0d3Sg7dVsDW8reF1BYQiKe/seW28rJT64wfG4X2Kt/Evsqvh7S7YOjrZO7RmlO1kAGl0uuP1DjjdEInBW7yponV361pbBCmRUrYJVPXj+MszhOxAZg8ic4MI1cOPT2/Jj179bXntaFn7/pQwiVisRVx3KV06BeN6Cc9d4fD0bYJ8+K9SuSAIzou2Nk4kSfJaKZUD5D6NdNbaWjabvdguMNyo837k5lig9BMTlFjmy9KhMwAAAABJRU5ErkJggg==';
			$img['type'] = 'png';
			break;
		case 'java':
			$img['data'] = 'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAIRSURBVDjLjZPJa1NRFIera/8ECy7dV7txkb2UOoDgzo0R3YuLrFwWIVglWQRtN0GCLkIixJDJQJKGQOYBA4akmec5eSFT/XnPsXlNsWIffOTdd3O+e+6PezcAbBDiuS7YEmz/hxuCq3LdmmBrOp32F4vFyXK5xEWIeWg0mnfrknXBNhWPx2NIkiQzGAzQ6/XQaDRYUqvVoNVqZQkXGwyGm2q1+k00GkUkEkE4HEYwGGQCgQDS6TSKxSILJpMJaBGdTvdHYjKZHvp8vuNsNot6vc7QavRLq1UqFcTjcbhcLrmLFZyJ2+0u9Pt9hC1f8OHpDt4/uoO3928zmscKHD5/gKPPB8jn8yxpNpuoVqtnAqPRiOFwiPGgB/fhPr7uvcJH5S4Ont3Dp5dP8G3/NX4cfedCi8XCeXQ6nTOBzWaT5vM5J0yTFFy325WhtmkbhN1ux2g04gVlgcfj+UmDUqkEh8OBcrnM7xRaLpdDIpHgcSqVYihEYr0DL61O6fv9fhQKBd4vhUrpk6DdbsNsNrN8Nptxt7JApVK9EMW9TCbDEgqI2qUOSELvJPF6vbw9Kj4nEM81pVJ5V6/XH8diMQ6IaLVaLAmFQnA6nfyNslohC05P4RWFQrFLHVitVoYSF2cEyWSSgxOn9Bx/CWggPv761z24gBNZcCq5JQKSaOIyxeK/I769a4JNklziOq+gq7/5Gx172kZga+XWAAAAAElFTkSuQmCC';
			$img['type'] = 'png';
			break;
		case 'excel':
			$img['data'] = 'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAIpSURBVDjLjZNPSFRRFMZ/9707o0SOOshM0x/JFtUmisKBooVEEUThsgi3KS0CN0G2lagWEYkSUdsRWgSFG9sVFAW1EIwQqRZiiDOZY804b967954249hUpB98y/PjO5zzKREBQCm1E0gDPv9XHpgTEQeAiFCDHAmCoBhFkTXGyL8cBIGMjo7eA3YDnog0ALJRFNlSqSTlcrnulZUVWV5elsXFRTHGyMLCgoyNjdUhanCyV9ayOSeIdTgnOCtY43DWYY3j9ulxkskkYRjinCOXy40MDAzcZXCyVzZS38MeKRQKf60EZPXSXInL9y+wLZMkCMs0RR28mJ2grSWJEo+lH9/IpNPE43GKxSLOOYwxpFIpAPTWjiaOtZ+gLdFKlJlD8u00xWP8lO/M5+e5efEB18b70VqjlMJai++vH8qLqoa+nn4+fJmiNNPCvMzQnIjzZuo1V88Ns3/HAcKKwfd9tNZorYnFYuuAMLDMfJ3m+fQznr7L0Vk9zGpLmezB4zx++YggqhAFEZ7n4ft+HVQHVMoB5++cJNWaRrQwMjHM9qCLTFcnJJq59WSIMLAopQDwfR/P8+oAbaqWK2eGSGxpxVrDnvQ+3s++4tPnj4SewYscUdUgIiilcM41/uXZG9kNz9h9aa+EYdjg+hnDwHDq+iGsaXwcZ6XhsdZW+FOqFk0B3caYt4Bic3Ja66NerVACOGttBXCbGbbWrgJW/VbnXbU6e5tMYIH8L54Xq0cq018+AAAAAElFTkSuQmCC';
			$img['type'] = 'png';
			break;
		case 'binary':
			$img['data'] = 'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAGNSURBVDjLpVM9SwNBEJ297J1FQBtzjQj2dgppYiP4A1KZRoiFrYWt9rHyH6QUPBDTCimtLNSAnSB26YKg4EdMdsd5611cjwsIWRhmZ3f2zZuPVcxMsyx9fPF0NRfS2vM7lx2WtcQiJHvDRvZMluXMGNHstJH7+Wj09jHkOy1+tc3VxeC+P6TXT1sYZX2hT7cvS6lepv3zHUp2T8vXNw81dXT2yGwEGeERSbSVCC5qysYa+3vm9sJGmLFojceXJ9uklCqUIAic5G3IytahAAhqqVSiwWDwx6nogW9XKhWphaGAvC50Oh1qtVr/7oAdCwBQwjB00mg0qFqtUr1ed3YURZM7X7TWTqM2Gm3CASRJEur1etTtdp1DnrafFtJGMbVNGSBas9l0DrAzR6x8DdwASUB0RqNNGS2/gH7EInvCwMhkZTnlnX0GsP09tJER0BgMoAEAa1rETDIQvBkjBZeHMIjjuNB5Ggg0/oZWPGrHGwd7Fp9F2CAlgHKqf0aYXb6Y2mzE8d/IfrXVrN/5G81p6oa2mIEUAAAAAElFTkSuQmCC';
			$img['type'] = 'png';
			break;

		case 'text':
		case 'readme':
			$img['data'] = 'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAADoSURBVBgZBcExblNBGAbA2ceegTRBuIKOgiihSZNTcC5LUHAihNJR0kGKCDcYJY6D3/77MdOinTvzAgCw8ysThIvn/VojIyMjIyPP+bS1sUQIV2s95pBDDvmbP/mdkft83tpYguZq5Jh/OeaYh+yzy8hTHvNlaxNNczm+la9OTlar1UdA/+C2A4trRCnD3jS8BB1obq2Gk6GU6QbQAS4BUaYSQAf4bhhKKTFdAzrAOwAxEUAH+KEM01SY3gM6wBsEAQB0gJ+maZoC3gI6iPYaAIBJsiRmHU0AALOeFC3aK2cWAACUXe7+AwO0lc9eTHYTAAAAAElFTkSuQmCC';
			$img['type'] = 'png';
			break;
		case 'image':
		case 'video':
		case 'ico':
			$img['data'] = 'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAIcSURBVDjLjZO/T1NhFIafc+/trdRaYk1KUEEWjXHRaCSik+E/cDHGzYXB2YHRhMRFY1SYmRgYHZ3VxIRFDYMraMC2hrbQXm7v9+M4UGobiOEk7/adN+9zvnNEVQEQkYvAGBDy/6oBm6rqAVBVeia30jRtGmOctVaPU5qmuri4+AaYAgJVHTKYNsa4drutnU6nr1arpY1GQ6vVqlprdXt7W5eWlvomMv/uw6tSofB4p+NOF0biYtc48tEAhXiuTZzh/s1xyuUyWZbhvWdlZeXt3Nzca14sf6zW6nXf7uzrcfq9s6sLy5+1Xq8fQQKmo1ZCvlAoyo+tXT5tPGO09IckM2zWznH3/AJ3rl5ACInjmGazifceay2VSgWASISSBaz3FIs1RnJlPF18vEG1keDVk1lLFEWICM45wvAfYqTKriqje0lGI01x2qFtuuwkKQ26oEKcCwnDEBFBRA6HfmBw8JWwl3o2ti7j8+u0TUKzcYkrY/n+wyAIEJEjSxEglLyH5r7j+tg8T1oVZr8GzE69JIoiFMiM7zeHYUgQBAMJVBGU77+eYoxhLcvIxnNk6w8xxvDo3hqH+yIieO+HEkQB/qe6bPL5g/cckCkDiBhjOJULhlCGDJIkXX2z+m3GeW4UCnExyxxxHIIOLNLk2WP5AaQXTYDb1tovgHCy8lEUzQS9g1LAO+f2AX+SZudcAjgZOOeJ3jkHJ0zggNpfYEZnU63wHeoAAAAASUVORK5CYII=';
			$img['type'] = 'png';
			break;
		case 'audio':
			$img['data'] = 'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAIsSURBVDjLjZNfa9NQGIdnP4cDv8Nkn8PL6UfwSgQZOoSBYkUvZLN1lMFArQyHrsIuWkE3ug2t1K3O0LXrZotdlzZp0qZp/qc9P8852qyyigs8F8nJ7znveZN3DMAYg14XKROUyf9wiRIKckOCCcdxNN/3+71eD6Og64hEInPDkmHBJAsbhgHTNAM6nQ7a7TYkSeKSer2OaDQaSAbhC7efJGY28gZWPrUQTyt4l2lCKLfR7XahaRpkWeYCy7LANonFYr8lqzt26PUXIxzf7pCfioeS5EI2fVQkG+GVH0hlRVqFjmazeeZIvCc0PBXf1ohu96GZBEnBQMMmcAjgeH3cWRKQyTf4URRF4ZWIongqoOFURXZpUEOt1YNm+BzDI6AeFKo6IqsF3g9d13k/VFU9FSytK9V8zUJiR0WbBh+/2cVich+trodvNQeFEwvTsa/8C7Dzs54wUSBYeN+ofq+ageDZmoBX64dQdRcbByaEqoGbTzPwPA+u63IJIxDMrR2nDkUTR6oPxSJ8ZxYuNlxsHtnYLal48DIH+om5gMGqCQSP3lam7i+XSMfp40AFsjWCrbKHdMlGpeng2uxHpHM1XgGDhf8S3Fsuhe4+3w9PL+6RvbKGguhAODaRLSq4OvsBL5JFvutAMCAQDH6kK9fnZyKJAm4tZHFj/jMexnPYzJ3w0kdxRsBu6EPyrzkYQT8Q/JFcpqWabOE8Yfpul0/vkGCcSc4xzgPY6I//AmC87eKq4rrzAAAAAElFTkSuQmCC';
			$img['type'] = 'png';
			break;

		case 'favicon':
			$img['data'] = 'AAABAAEAEBAAAAEAIABoBAAAFgAAACgAAAAQAAAAIAAAAAEAIAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAL59VY+9fFS1vHtT77p5U/+2d1H/tHZR/7F0UP+tck//qnFP/6hvTv+nbk3/pm5N/6RuTfGmbk3FAAAAAAAAAADAgFXf////////////////////////////////////////////////////////////////pm5N7QAAAAAAAAAAwoJY//////+vZyz/rWQr/61kK///////8OXe//Dl3v/w5d7/8OXe//Dl3v/w5d7//////6dvTv8AAAAAAAAAAMSGWv//////r2cs/82fdP+tZCv///////Dl3v/27+v/9u/r//bv6//27+v/8OXe//////+ocU//AAAAAAAAAADFhlv//////69nLP/NoHX/rWQr///////w5d7/9u/r//Xs5v/17Ob/9u/r//Dl3v//////rHJQ/wAAAAAAAAAAx4ha//////+0bjP/0KV+/7JsLv//////8OXe//bv6//17Ob/9ezm//bv6//w5d7//////7J2Uf8AAAAAAAAAAMiKW///////t3ZA/9Gmg/+2czj///////Dl3v/27+v/9u/r//bv6//27+v/8OXe//////+2eFP/AAAAAAAAAADKjFz//////7x/VP+6fUz/unxK///////w5d7/8OXe//Dl3v/w5d7/8OXe//Dl3v//////uXxU/wAAAAAAAAAAy45d/////////////////////////////////////////////////////////////////7x+Vf8AAAAAAAAAAMyQXvv/////7cSb/+3Em//txJv/7cSb/+3Em//txJv/7cSb/+3Em//txJv/7cSb//////++gVf/AAAAAAAAAADNkV/x/////+3EnP/02sH/9NrB//Tbwv/028L/9NvC//Tbwv/028L/9NvC/+3Em///////wYRY/wAAAAAAAAAAzpNf2f/////txJz/7cSc/+3EnP/txJv/7cSb/+3Em//txJv/7cSb/+3Em//txJv//////8aMYfkAAAAAAAAAAM6TX5v////////////////////////////////////////////////////////////////Wqon/AAAAAAAAAADOk19wzpNfkc6TX83Ok1//zZNf/82SX//MkF//zZJh/82TY//LkWH/y45f78qSZMvYrov/16yL/wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//8AAIABAACAAQAAgAEAAIABAACAAQAAgAEAAIABAACAAQAAgAEAAIABAACAAQAAgAEAAIABAADAAQAA//8AAA==';
			$img['type'] = 'ico';
			break;

		default:
			$img['data'] = 'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAC4SURBVCjPdZFbDsIgEEWnrsMm7oGGfZrohxvU+Iq1TyjU60Bf1pac4Yc5YS4ZAtGWBMk/drQBOVwJlZrWYkLhsB8UV9K0BUrPGy9cWbng2CtEEUmLGppPjRwpbixUKHBiZRS0p+ZGhvs4irNEvWD8heHpbsyDXznPhYFOyTjJc13olIqzZCHBouE0FRMUjA+s1gTjaRgVFpqRwC8mfoXPPEVPS7LbRaJL2y7bOifRCTEli3U7BMWgLzKlW/CuebZPAAAAAElFTkSuQmCC';
			$img['type'] = 'png';
			break;
	}

	header('Content-type: image/'.$img['type']);
	print base64_decode($img['data']);
	exit;

}


/* encode urls for transport */
function url_encode($url) {
	return eregi_replace('[+]','%20',urlencode($url));
}


/* read image for storage */
function readimg($img) {
	echo base64_encode(file_get_contents($img));
}


/* for security reasons, keep the read case commented out when you are not using it */
switch($_GET['p']) {
	case 'source': showsource($_GET['file']); break;
	case 'thumb': makethumb(base64_decode($_GET['file']),$GLOBALS['CONFIG']['THUMB_HEIGHT'],$GLOBALS['CONFIG']['THUMB_WIDTH']); break;
	case 'mime': getmimefile($_GET['type']); break;
	case 'logo': getmimefile('logo'); break;
	case 'favicon': getmimefile('favicon'); break;
	case 'read': readimg($_GET['img']); break;
	default: listdir($dir); break;
}

?>