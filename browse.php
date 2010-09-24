<?php
/*************************************************/
/* PHP Power Browse                              */
/* Devin Smith (php@arzynik.com)                 */
/* 2004-01-31 http://www.arzdev.com              */
/*************************************************/

/* Config */
$GLOBALS['CONFIG']['PAGE_TITLE'] = 'PHP Power Browse';
$GLOBALS['CONFIG']['THUMB_HEIGHT'] = 12;
$GLOBALS['CONFIG']['THUMB_WIDTH'] = 15;
$GLOBALS['CONFIG']['DISPLAY_HIDDEN'] = FALSE;
$GLOBALS['CONFIG']['PROCESS_INDEX'] = FALSE;
$GLOBALS['CONFIG']['BG_IMAGE'] = '';

$GLOBALS['CONFIG']['BG_COLOR_1'] = '#8888aa';
$GLOBALS['CONFIG']['BG_COLOR_2'] = '#f0f0f0';
$GLOBALS['CONFIG']['BG_COLOR_3'] = '#f0f0f0';
$GLOBALS['CONFIG']['TXT_COLOR_1'] = '#000000';
$GLOBALS['CONFIG']['TXT_COLOR_2'] = '#003399';
$GLOBALS['CONFIG']['TB_COLOR_1'] = '#dddddd';
$GLOBALS['CONFIG']['TB_COLOR_2'] = '#C1C1DF';
$GLOBALS['CONFIG']['TB_COLOR_3'] = '#BDBDD6';
$GLOBALS['CONFIG']['TB_COLOR_4'] = '#FFFFFF';

$GLOBALS['CONFIG']['IGNORE_DIRS'] = array('./logs','./files','./images');
$GLOBALS['CONFIG']['HIDE_FILES'] = array('./config.php');
$GLOBALS['CONFIG']['DENY_SOURCE'] = array('config.php');
$GLOBALS['CONFIG']['WEB_FILES'] = array('php','php4','php3','phtml','html','htm','js','asp','xml','css','bml','cgi','cfm','apm','jhtml','xhtml','aspx','tpl','inc','c','h','vb','py','sh','pl');
$GLOBALS['CONFIG']['IMG_FILES'] = array('jpg','jpeg','gif','png');
$GLOBALS['CONFIG']['INDEX_FILES'] = array('index.php','index.html','index.htm','index.asp','default.asp','default.htm');

/* Security */
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
      } elseif(iswebtype($file)) {
        $filec[] = '<td width="100%"><table cellpadding="0" cellspacing="0" width="100%"><tr><td width="18"><a href="'.$dirlink.url_encode($file).'"><img src="'.$_SERVER['SCRIPT_NAME'].'?p=mime&amp;type='.getmime($directory.'/'.$file).'" border="0" alt=""></a></td>'.
                   '<td align="left"><a href="'.$_SERVER['SCRIPT_NAME'].'?p=source&amp;file='.url_encode($dirlink.$file).'">'.$file.'</a></table>'.
                   '<td nowrap>'.date('F jS Y',$stats['9']).'<td nowrap>'.getsize($stats['7']); 
      } elseif(isimgtype($file)) {
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
  echo '<tr><td colspan="3" nowrap><b>Current Directory: ';
  while($basepos = strpos($dirdis,'/')) {
    if ($dirlist[$t-1] == '') $dirlist[$t] = substr($dirdis,0,$basepos); 
    else $dirlist[$t] = $dirlist[$t-1].'/'.substr($dirdis,0,$basepos); 
    echo '/<a href="'.$_SERVER['SCRIPT_NAME'].'?dir='.$dirlist[$t].'">'.substr($dirdis,0,$basepos).'</a>';
    $dirdis = substr($dirdis,$basepos+1,strlen($dirdis));
    $t++;
  }

  echo '</b><tr class="head"><td width="100%" bgcolor="'.$GLOBALS['CONFIG']['TB_COLOR_4'].'">Name<td nowrap bgcolor="'.$GLOBALS['CONFIG']['TB_COLOR_4'].'">Last Modified<td nowrap bgcolor="'.$GLOBALS['CONFIG']['TB_COLOR_4'].'">Size';
  if ($dirc) {
    asort($dirc);
    foreach ($dirc as $dir) {
      $tcoloring  = ($a % 2) ? $GLOBALS['CONFIG']['TB_COLOR_2'] : $GLOBALS['CONFIG']['TB_COLOR_3']; 
      echo '<tr bgcolor="'.$tcoloring.'">'.$dir;
      $a++;
    }
  }
  if ($filec) {
    asort($filec);
    foreach ($filec as $file) {
      $tcoloring  = ($a % 2) ? $GLOBALS['CONFIG']['TB_COLOR_2'] : $GLOBALS['CONFIG']['TB_COLOR_3']; 
      echo '<tr bgcolor="'.$tcoloring.'">'.$file;
      $a++;
    }
  }

  $dir = $directory;
  if (!$dir) $dir = './'; else $dir = '.'.$directory;
  $count = countdir($dir);

  echo '<tr bgcolor="'.$GLOBALS['CONFIG']['TB_COLOR_4'].'"><td><b>'.number_format($count[1]).'</b> lines of code in <b>'.number_format($count[2]).'</b> files within <b>'.number_format($count[3]).'</b> directories.'.
       '<td> <td nowrap><b>'.getsize($count[0]).'</b>';
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


/* Count lines function */
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
        if(iswebtype($file)) {
          $lines = file($dir.'/'.$file);
          $linecount = 0;
          foreach ($lines as $line) if (substr(eregi_replace(' ','',$line),0,2) != '//'|'/*') $linecount++; 
          $totallines = $totallines + $linecount;
          $totalbytes = $totalbytes + filesize($dir.'/'.$file);
        }
      }
    }
    $totaldirs++;
  }
  return array (intval($totalbytes), intval($totallines), intval($totalfiles), intval($totaldirs));
}


/* Checks to see if the file should be counted (required by countdir)*/
function iswebtype($file) {
  $ext = pathinfo($file);
  $ext = strtolower($ext['extension']);
  if (in_array($ext,$GLOBALS['CONFIG']['WEB_FILES'])) return true;
  else return FALSE;
}

function isimgtype($file) {
  $ext = pathinfo($file);
  $ext = strtolower($ext['extension']);
  if (in_array($ext,$GLOBALS['CONFIG']['IMG_FILES'])) return true;
  else return FALSE;
}


function pagehead($stitle) {
  if ($stitle) $dtitle = $GLOBALS['CONFIG']['PAGE_TITLE']." : ".$stitle;
  else $dtitle = $GLOBALS['CONFIG']['PAGE_TITLE'];
  echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">'.
       '<html lang="en">'.
       '<head>'.
       '<title>'.$dtitle.'</title>'.
       '<meta http-equiv="Content-Type" content="text/html;charset=utf-8" >'.
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

function pagefoot() {
  echo '</table></table></table></table>'. 
       '<table width="100%"><tr><td align="right">'. 
       '<font class="copyright">'.
       'PHP Power Browse &copy; 2004-'.date('Y').' <a href="http://arzdev.com">ArzDev</a>'.
       '</font></table></table>';
  die();
}


function showsource($file) {
  if (file_exists($file) && !is_dir($file) && !(is_array($GLOBALS['CONFIG']['DENY_SOURCE']) && in_array($file,$GLOBALS['CONFIG']['DENY_SOURCE']))) {
    pagehead($file);
    $content = highlight_file($file, 1);
    $linecount = substr_count($content, "<br />") + 1;
    $size = number_format(filesize($file), 0, ' ', ' ');
    $date = date('F d Y H:i:s.', filemtime($file));
    echo '<tr><td bgcolor='.$GLOBALS['CONFIG']['BG_COLOR_3'].'><b>'.basename($file).' has '.$linecount.' lines, size is '.$size.' bytes, last modified on '.$date.'</b></font>'.
         '<table cellspacing="0" width="100%" border="0" cellpadding="2" class="code"><tr valign="top"><td bgcolor="'.$GLOBALS['CONFIG']['TB_COLOR_3'].'" width="0" align="right"><code>';
    for($i=1; $i<=$linecount; $i++) echo '<a name='.$i.'></a><a href="#'.$i.'">'.$i.'</a><br>';
    echo '<td nowrap>';
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
    $thumb  = imagecreate($w, $h/2);
    $bgc = imagecolorallocate($thumb, 255, 255, 255);
    $tc  = imagecolorallocate($thumb, 0, 0, 0);
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


function getmime($file) {
  if (substr($file,0,1) == '/') $file = substr($file,1);

  $ext = pathinfo($file);
  $ext = strtolower($ext['extension']);
  $mime = escapeshellarg($file);
  $mime = trim(`file -bi $mime`);
  if (strstr($mime,','))  $mime = substr($mime,0,strpos($mime,','));

  if (is_dir($file)) 												$type = 'folder';
  elseif (preg_match('/^application(.*?)compress|zip(.*?)$/',$mime) || $ext == 'tar' || $ext == 'rar' || $ext == 'ace' || $ext == 'z' || $ext == 'gz') $type = 'archive';
  elseif ($mime == 'text/x-c' || $ext == 'c++' || $ext == 'c#') 	$type = 'c';
  elseif (($mime == 'application/octet-stream' || $ext == 'rpm' || $ext == 'exe' || $ext == 'bin') && (preg_match('/^(.*?)setup(.*?)$/',$file) || preg_match('/^(.*?)install(.*?)$/',$file))) $type = 'setup';
  elseif ($mime == 'application/inf' || $ext == 'inf') 				$type = 'inf';
  elseif ($ext == 'php' || $ext == 'phtml' || $ext == 'php3' || $ext == 'php4' || $ext == 'php5' || preg_match('/^application(.*?)php(.*?)$/',$mime)) $type = 'php';  elseif ($mime == 'text/html' || $ext == 'html' || $ext == 'htm') $type = 'html'; 
  elseif (preg_match('/^application(.*?)shockwave|flash(.*?)$/',$mime) || $ext == 'swf' || $ext == 'fla') $type = 'swf';

  elseif ($mime == 'text/x-h') 										$type = 'h';
  elseif ($mime == 'application/msword') 							$type = 'msword';
  elseif ($mime == 'application/x-javascript') 						$type = 'javascript';
  elseif ($mime == 'application/xml' || $mime == 'text/xml') 		$type = 'xml';
  elseif ($mime == 'application/postscript') 						$type = 'postscript';
  elseif ($mime == 'application/pdf') 								$type = 'pdf';
  elseif ($mime == 'application/octet-stream')						$type = 'exe';
  elseif ($mime == 'text/asp') 										$type = 'asp';

  elseif ($ext == 'csv') 											$type = 'csv';
  elseif ($ext == 'rpm') 											$type = 'rpm';
  elseif ($ext == 'deb') 											$type = 'deb';
  elseif ($ext == 'mk') 											$type = 'makefile';
  elseif (preg_match('/^application(.*?)powerpoint(.*?)$/',$mime)) 	$type = 'powerpoint';
  elseif (preg_match('/^application(.*?)cert(.*?)$/',$mime)) 		$type = 'crt';
  elseif (preg_match('/^(.*?)readme(.*?)$/',$file)) 				$type = 'readme';
  elseif (preg_match('/^application(.*?)java(.*?)$/',$mime)) 		$type = 'java';
  elseif (preg_match('/^application(.*?)excel$/',$mime)) 			$type = 'excel';
  elseif (preg_match('/^application(.*?)binary$/',$mime)) 			$type = 'binary';
  elseif (strstr($mime,'text/')) 									$type = 'text';
  elseif (strstr($mime,'image/')) 									$type = 'image';
  elseif (strstr($mime,'video/')) 									$type = 'video';
  elseif (strstr($mime,'audio/')) 									$type = 'audio';
  else 																$type = 'unknown';
  return $type;
}


function getmimefile($file) {
  switch ($file) {
    case 'logo':
      $img = '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';
      break;

    case 'folder':
      $img = 'R0lGODlhDAAPAPeVAHWHrebm5oCq0vDy9Xh4eJC21nGfzXGezsXc6WiWx5m92XJxcY6XtGmIs2yZyGybyqrK3p7B3KvI3ShNk7bR43WSuXmMsnqStXmm05Spw8DX5JOpx4yjwIu01rPP4H6r2J2mvu3u88bN3Iiw1Zqrw9PT00hil5C212xsbMba5Zi82X19fcPb5mFka8nf6O3t7ThlqMfc52CHuzpprHmJrXaIrlteaHun1ktmmUNvq3R0c7m4uWVpcr7E0mOEsbCwsMLV4OXl5brS4rzU4YSdvbvV41NvnaTE26DC3LHO4HV1dXV1dFV9sHd3d+vs8kxlmXJycqKiosrKzNLm7MvLysLCxG1wdenp6XOFrJa62NDQ05Wfupm71tDR1GyczainpXum0XWiz8LBvzZhozJanePm7V2Jv7++vM7i6sTX4qfG3/X19b7R4XKOs3GRull3qrnU5Hd3dnqn0aLC2m+dzoev06alpcPCxKzK3n2NsF1hapuvx7fJ2tHV32OJvnFxcF2BsUNzs87O0by7vFqFumqIsZqivK+4yHd2dNHl7Ovt8s/U4nuWusPW5k1ompKct6O1yoav1NTU2NjZ3G9vb////wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAJUALAAAAAAMAA8AAAi9AEtAoYQiyosAAYJcqVRpxY8zYr4gaqHHBo8+ZQisWSSixyE+bBpteJOHwCQSaYCkSBQDwYUJWHR0gTQFjQsNQii0IQOgiaQ9LIYU8SBBTaExNeJoyQAnCZ45XLL4gAFgiSAOEI4gUXBiBKAZAJRIIRJBRYE6AsAwCWRhQRVGHSIJCOMggZkcNP7cqSDHwIMDdAjhYOCE0o4GXjDc8OPokSKGlAa5+SDjyZYQDCHbMWLC0IDMmalYAfEZdOaAADs=';
      break;
    case 'archive':
      $img = 'R0lGODlhEAAQAOcAAAAEAAAFAAAEBwAECwACDgwSIAoMGbNNNoAvHHY6LwgAAE9WYG2DmFh/nn6w03Gt0ovM7FOUqgA0Pc0tCf+lhP+znv/AsVoUDCsAABkAAAsAAAEEEwAIHwAMKwARLhVgc5Ld4AAmE9MaCL8UA8s4Lq0tIIoWALdGJJwoAKEzAGsTAJBYN1dJPvT//wAKDKjRyQAOAMcbEf/Qyv++uf/Sxv/jw//Ch//Bav+zUv/QdvSkXR0AAPP05g0gHqi9uKElHf/g2v/l4v/g0f/5z//jmvq2R//ERf+9SOq/Yw0KAPL/+gAHDrTDygoAAKQzI//XyP/u5P/x2vbot+7ahPvLT//GO/bAQu3PbwAHAOD59gAHH7e+2xQACKceAP/gwf/p0P/vzOzJj+a+Zv/BRf+4M/26ReG3ZREMAO79/wAAM7Kz7BIAHqksAP/csP66lfTFmfTOjfvNdf+8Rf/RUv/QY6l9NAYBANzu/wASQLTB9gsAJ4c2AP/xvf/hs+7SoNzAf/TQev/GW/GvP7+FItCvaMzu7QAXJqHS4QAJDHlAFSQAABUAAA0AABoOABEBAD0aABsAAAsCAAAQBAANDB9VV5PIwh1HMQAEDERSW1xueGl+j4mlu5O404iyyIqvtwAeFAAHIwAPMgAIMgAIMwAIKQATJQAKJnKTsgAJBxQ7QgAWJkZvhQAIIy1ObwAFKUdkjC5SYAAMIh5WbwAQK0N2kQAOJixLYAAFGwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEKAP8ALAAAAAAQABAAAAjyAP8JHAgggIABBAoYGPjvAIIE/xQsYNDAwQMIESQInEChgoULGDJo2MChg4cPIEL8EzGCRAkTJ1CkULGCRQsXL2D8iyFjBo0aNm7gyKFjB48ePgL8+wEkiJAhRIoYOYIkiZIlTJr8c/IEShQpU6hUsXIFSxYtW7j86+LlC5gwYsaQKWPmDJo0atb8Y9PGzRs4ceTMoVPHzh08efT828Onj58/gAIJGkQoQCFDhxD9S6RoEaNGjnY8ghRJ0iRKlSwxFHgJUyZNmzh18vRp9UBQoUSNEkWqlO3fpk79ZogqlapVrFq5ejUcVixZs2jVsnXrX0AAOw==';
      break;
    case 'c':
      $img = 'R0lGODlhEAAQAMIAAISEhP///wAAAMbGxgAAhAAAAAAAAAAAACH5BAEKAAcALAAAAAAQABAAAANCeAfcrnCFSacAUVYqAobAxgkkKFaDYE5ES1Dp2rJvECthMOv1re0oFY5GtAl/tZ0vxwMuT7Aj87QcWK9YH2nL5R4SADs=';
      break;
    case 'setup':
      $img = 'R0lGODlhEAAQAOMAAISGhNbX1gAAAGNhzjFhzv///2PP/2P//zGezgCezjFh/8bHxgD/////AAD/AP///yH5BAEKAA8ALAAAAAAQABAAAARr8MkJgr0BiEkH+V+hcRIwGMdhIOJGAWa6Ji1ViIOKJEoNwwvTTEFoAW6ARVDJVI5gAudvutgco1WBVvCrPq5BBqNxw3qvAMZiXFBuS9XkWuvmJLlMgUPsguMXbQ51FFlHbQ0OfRJvD1taJBEAOw==';
      break;
    case 'inf':
      $img = 'R0lGODlhEAAQAMIAAAAAAP///4SGhMbHxr2+AP//AP///////yH5BAEKAAcALAAAAAAQABAAAANMeArH3iq2IKitLOIAuO/ZUQVkWQ7haJqoRgIw1wWtoq5kLb5wn6erAWHwc8mEBUJHdyMUAEka8OhUSo0lgPVqw7FSg7B4rGv0zr1IAgA7';
      break;
    case 'php':
      $img = 'R0lGODlhEAAQAIQAAHt7ewAAAL3Gxv///3uEhHuEvTlCWoSMvZScxu/v997n76211t7e7/f3/0JCWoyUxsbG3rW91r293oyUvc7W55ylzjk5ORghKRghITlCQpycpf///////////////////yH5BAEKAB8ALAAAAAAQABAAAAVo4CeKQGme4xicZyCkXzDM9BwQsBzs/HDntdovpePtfDhikHYzOo3IQGFKNQCCzekBkVAYnNHCgnE4NBxXISGwaDwKkMgX2jxAFAXJw4pdTycFFBUGFixNBoiJFxgZjY4ELCYaApSVAiEAOw==';
      break;
    case 'html':
      $img = 'R0lGODlhEAAQAPZhAAAAACMsPicxPDAwMBUrQh42Vh09ZiQ4XDdCMR5AfCFCfUhIN15YMUJCQkBSVlhYV0NkcWxsbGVvfHd1cihVhiZSmylWqTlimy9gsDduqTZotj10pDlyzE14nE15p0J2vGVygUF6zkV90GyBeUqEvliEvnybmm+Qq2+XuE+Gx0mO1FCGx1SO2WOg2nap3YN2QK6WN6mYSriaU7yec7yie7CxaNm5bvLScYyJhoyQj5mZmZ6ulYmitaScjqyjlb2oh7irlrGrobmzq7u4tYCr2rjJuLvAxcCvkcWzm8e4osK9tNPChsXAuNDAquTQgffRhvLXleXMp+LPs+7eq+jTtP7gv87Ozt3d3ercxurgyOji2fDmwfLo2Orq6vPt4/vx6f39/QAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEBAGEALAAAAAAQABAAAAf+gGFhYWEPDw8PDw8PDw9hYWFhYWFhD1dXV1dXQw86D2FhYWFhYQ9XYGBgYF0RYDoPYWFhYQAXBhA5YGBgEVZgOgNhYRkvNz4pFwZGYDgREQ0DYRlFW1tLNSMVBENgXUI4A2EUWF9TTjIdGAYgV19aQgMUJFheTzYeIhgJBEpdXIBHAwcuJ1A6PCwhGAkEQl5aRwMHKS4oNh4eHxYFBEFaWEcDYRQsLSo2MQ8GBBJBWFg/A2EEHhocMS8MBAQvSlhUPwNhYQQGFA4NCAUvQElUUjQDYWFhYQQEBA86QElSVFE0A2FhYWEPSkFBSUhHPz8zMwNhYWFhDwMDAwMDAwMGgAMDAwOBADs=';
      break;

    case 'h':
      $img = 'R0lGODlhEAAQAMIAAISEhP///wAAAMbGxgAAhAAAAAAAAAAAACH5BAEKAAcALAAAAAAQABAAAANCeAfcrnCFSacAUVYqAobAxgkkOBGEOAhmgKqsErroO62tbePymdK3mEb34w1/O+GMGGyJmr0ndDGoWq9Gkna7PSQAADs=';
      break;
    case 'msword':
      $img = 'R0lGODlhEAAQAMIAAISEhP///wAAAAAA/8bGxgAAAAAAAAAAACH5BAEKAAcALAAAAAAQABAAAANReArcrvCAQCsFQg2xOxdXkIFWNVBC+nke4QZEuolnRxEwF9SgEOPAFG21A+Z4sQHO94r1eJRTJVmqMIOrrPSWWZRcza6kaolBAOB0WoxRud0JADs=';
      break;
    case 'javascript':
      $img = 'R0lGODlhEAAQAMIAAISEhAAAAMbGxv//AP///4SEAP///////yH5BAEKAAcALAAAAAAQABAAAANQCLrcASfGJsWTBwyybbTQRBQkGBygyBGgl2YjGcwnemVbV383ThYeXmiSY9VeGUWAZLSFAEZAgOO6QAePXPAFEAik3h2SRsO0vOh0msxuzxIAOw==';
      break;
    case 'xml':
      $img = 'R0lGODlhEAAQAOMAAICAgP///8DAwAAAAACAgAD//wAAgACAAAAA/wAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEKAA8ALAAAAAAQABAAAARQ8IFJ63xYhs27ABnQjYEQgBrJDeyQmgEhb7ArFkYcFERuFAGbIBfjHYgGgW2Xkx0QPuCytDFYaZupqjOVeb/Z1HaV+prDojE5rQ4+WvB4KwIAOw==';
      break;
    case 'postscript':
      $img = 'R0lGODlhEAAQAPIAAAAAADk5OWtra4yMjM7Ozufn5////wAAACH5BAEAAAcAIf4cYnkgTWFydGluLktyYWVtZXJATWNoLlNOSS5EZQAsAAAAABAAEADCAAAAOTk5a2trjIyMzs7O5+fn////AAAAA0V4utxHxgwwohUM2kj6GZkESJuBLdpGFGADTcAYKafyGlRZP6XuOC1XD8IS9gLIQIgkixR2qZ4plGtGoNJLYyDoer3KRQIAOw==';
      break;
    case 'pdf':
      $img = 'R0lGODlhEAAQAPcAAAAAAIAAAACAAICAAAAAgIAAgACAgMDAwMDcwKbK8AQEBAgICAwMDBERERYWFhwcHCIiIikpKVVVVU1NTUJCQjk5Of98gP9QUNYAk8zs/+/Wxufn1q2pkDMAAGYAAJkAAMwAAAAzADMzAGYzAJkzAMwzAP8zAABmADNmAGZmAJlmAMxmAP9mAACZADOZAGaZAJmZAMyZAP+ZAADMADPMAGbMAJnMAMzMAP/MAGb/AJn/AMz/AAAAMzMAM2YAM5kAM8wAM/8AMwAzMzMzM2YzM5kzM8wzM/8zMwBmMzNmM2ZmM5lmM8xmM/9mMwCZMzOZM2aZM5mZM8yZM/+ZMwDMMzPMM2bMM5nMM8zMM//MMzP/M2b/M5n/M8z/M///MwAAZjMAZmYAZpkAZswAZv8AZgAzZjMzZmYzZpkzZswzZv8zZgBmZjNmZmZmZplmZsxmZgCZZjOZZmaZZpmZZsyZZv+ZZgDMZjPMZpnMZszMZv/MZgD/ZjP/Zpn/Zsz/Zv8AzMwA/wCZmZkzmZkAmcwAmQAAmTMzmWYAmcwzmf8AmQBmmTNmmWYzmZlmmcxmmf8zmTOZmWaZmZmZmcyZmf+ZmQDMmTPMmWbMZpnMmczMmf/MmQD/mTP/mWbMmZn/mcz/mf//mQAAzDMAmWYAzJkAzMwAzAAzmTMzzGYzzJkzzMwzzP8zzABmzDNmzGZmmZlmzMxmzP9mmQCZzDOZzGaZzJmZzMyZzP+ZzADMzDPMzGbMzJnMzMzMzP/MzAD/zDP/zGb/mZn/zMz/zP//zDMAzGYA/5kA/wAzzDMz/2Yz/5kz/8wz//8z/wBm/zNm/2ZmzJlm/8xm//9mzACZ/zOZ/2aZ/5mZ/8yZ//+Z/wDM/zPM/2bM/5nM/8zM///M/zP//2b/zJn//8z///9mZmb/Zv//ZmZm//9m/2b//6UAIV9fX3d3d4aGhpaWlsvLy7KystfX193d3ePj4+rq6vHx8fj4+P/78KCgpICAgP8AAAD/AP//AAAA//8A/wD//////yH5BAEAAP8ALAAAAAAQABAABwhvAP8JBECwoEEAAhMCiMewIUMGJhAenBgPYkQAJjJq1LiQgUcGGDduXNhwIsWSCRk4fPfOYTyEAlU2PMDOJcx/Mhm+Y9cSZUyH7Gg6vJnzQMudLN8RZUgz6U527G4uROqSIdGnVa2mhJpVa0yTBwMCADs=';
      break;
    case 'exe':
      $img = 'R0lGODlhEAAQAIQAAAICAoaGhgL+AgICzrKyss7Ozv4CAgICjv7+At7e3urq6gICZgL+/vv7+8nJyQIC/gAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEKABAALAAAAAAQABAAAAVdICSOZAkFaKqup+O+8BsAwWPbQ37sxzLXt4dhIDggDgwfLfjQ9XrKQGNKbSgUiYTrV6VetVta14otmB3c6hcclnbLL0J6+oUR5GI6lu24/1aAKQAQAIWGh4gmiiMhADs=';
      break;
    case 'asp':
      $img = 'R0lGODlhEAAQAOMAAISGhP//////AAAAAMbHxgCGhAD/AACGAISGAAAA/wAAhP///////////////////yH5BAEKAA8ALAAAAAAQABAAAARm8MlJKwU4awuCF14gYFUHfl4HXOEpDvAwmQFR3IRIyFJXF4bCoZDbzRBIBCAhBAiMD+RAMEDAFAlFjYcQeL3WrOIpQwqs3digWPYOANPzWlfuoqmwE7T7lbegVVOBBISFPBJqiTERADs=';
      break;
    case 'swf':
      $img = 'R0lGODlhEAASAPexAOzs7NPT0+bm5u/v7+fn5+np6dHR0cfHx+Xl5bm5udTU1NDQ0Li4uPf398nJycjIyPTz8oqYp/Ly8rKysu7t7d/f36Wlpc/Q0fj4+OPj4+jo6MvO0Ozu8ampqZ+fn729va6uruHg5qWko9HX30NogLG0uJuntuTk6kFifcDHzrvBy4iTnoSPmbzEzPv7+nWGl052j3CCkd/f3rm8v9jY2L6+vn19fVV8lsDAwF97kbG4vxdEY+zr7MrKysrJyaOjo6+2vKKioqixuqars3yQofPy8re8w4eUoO3t7dzb25GRkc/PzwAvVPX1997j5/X19bm/xrm+w/Xz8h9BX5qhqPDv7+Tj6cvR17a9yMbGxrG1uQAiR6urq7K5xOHh4eDg4JGdq4uZpuPj4uvq6tLS0urq6p6engZJb3GAjJ2nse7u7kpmfwclRu3t7p2osrG4wPv7/Impvf39/dHU17q6uv/+/tzd5AA1W6uvtLe+yJeepNrb4qq0wOno5+bl6c3R1oGBgdrc3bCwsPj393uLmvv7+42NjcrS2dvb2+Dg5cLHyvf39ry8vI6dqdTW2PLz9vPz84eHh9/d4rm6ufT08/Hx8e7s8L3BzPP09v7+/aSjo77Bw4CNnKautv/9/KKhoNPW2evp7drb3I2Zp8XJz8zR1qCgoICNm42Zps7U25Ofq/b29tbW1tfX19XV1aenp////////wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAALEALAAAAAAQABIAAAj/AGMdcODgAKMJr0y9aoUhlkOHATQIEOCF1QMGZmogGvAwFhkCCEJWcHWAy4cGBjhCLECgpYAvrBIkgCVBQQOIAMoU2Kkhw4IghpRYoFEoVoABSADwqAJBSp9JkWwA8mAglgIJAyoVmfNGCJQLPuhM+CEolqtVT9q4eRHBxKhTUZL0YJDALIZBaSKMeHQFxQ1OimRkwWE2EyhCTuB4InEmzqUjAlw9iMUKlg4+mGC1uAND0p4wgcQsidUKVicVTWCtYbIjlRUwjsYoIA0LCJYTXbbk4JDIjipRFFzR3oCqEZsph0L4ybOCghpWtCFQiUHkj6VQpdBscgFAeGlYGfQMKSGVwgiLEpTkdKcMq/2iC1rwzKhQpz0k6Ate6e/wStMnEfq9YgEIBgQEADs=';
      break;

    
    case 'csv':
      $img = 'R0lGODlhEAAQAOMAAAMDCP3+/MjAwBIJCcjMzQMTJ+no6Q5GdAIgOba8wdrZ29bK0AAAAAAAAAAAAAAAACH5BAEKAA8ALAAAAAAQABAAAARu8ElAa5V4BrE7zdLAccQGCAA4dGNgTUBRVDPgSUVgHEGAGL5SIvUoIA7IJCKncOF6ByAvUBASAcckEmHjDJ6BaBhYSASGk+whyz0Hvg+br4fonUwwGUVvMxM1HGYtBH9xggELAUJOGAADFI80HxEAOw==';
      break;
   case 'rpm':
      $img = 'R0lGODlhEAAQAMYAAFhYWN7e3ry3sZmZmf///+rq6m1tbfz8/HBwcM7OzjAwMImJiWtra0JCQktMS1ZYVltdW2RkZGlpaU5PTr6ioOKysPb19eLg3o+JhG5vbiEiIUtNS3Nzc8zMzO/v79K/vsBoZcFRTdzHwufg2b62r1JTUgcHBh8gH9/f3/7+/vv49b5GQrtAO87Iv7yulyYmJQICAv7+/fr49Pj07I1KRo4XFLaEfQAAAAwMDEZHRsTExGRiXoiCeMC1o7yrkHZ5dr67uI6OixscG7awqBcXF7OqnBAQEM2+qbyoin9+er62qrywnruslbymhQcHB1JSUsnEvt7Yzsi/s9bLutXIs9LDrNDApc+7nryjf/Tt4fLo2PDk0O7eyOzav+nWuOfRsOXMp7yheMbBtsS9rMO4o8KzmsCvkb+qiL6lf72gdryec////////////////////////////////////////////////////////////////////////////////////yH5BAEKAH8ALAAAAAAQABAAAAfBgH+CfwCFhoaDiQABjIwCAAMAiYKLBJYEBQYEkZOVlwcICZsKigGXlgsIDA2kgg4PEBESExQVFhcCGKQZGhscHQEeAR8gISIjJAolJieEKJcpKissLS4KLzCUpgQxMjM0NTbWNzg5fzc3BDroOzc8PT4KN4I3P0A3QUJDREVGR0jy6KFLkkSIEiJLjDBp0uqPkydAoESRMoVKFStXsDQkFCCLli1cunj5AibMRgBixIwhU8bMGTRp1JxUQLOmTQWBAAA7';
      break;
    case 'deb':
      $img = 'R0lGODlhEAAQAPUxAAAAADAwMEJCQlhYWGxsbHBwcJoAAK4AALYAAboqKrkpMrtaWcAAAMo5OcJfWsVlZdp5eLyec7yie4yJhpmZmb2oh7ytlLu0rby3scuShcKSksCvkcKzmsO4o9yqp+Cwr+S0tMbBuOXMp+jVt8vKydTOxtnQw93d3ezcxObe1O7gyeni1/Hl1Orp6fPt4vbx7Pz8/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEBADEALAAAAAAQABAAAAbmwFgsFhsMBoPBYDAYxGKxWCwWG5xOp9MJM6AMYrFYLBYbtGAwGKxVgFEGsVgsFjM0GAjIC1YgwYCUQCwWMzAUiwVC8ZkUCIJALBZjGEit1qmhaLUwk0AshjgMYI3H58MgvVaXQCzGiA0aj1MLxAgBWyxLIBZDxAYOzQsGQZhYK0sgFmPEBiDFB8K4pFgqSyAWQxgGMM9icTGxUKhKIBaLMQaw1yq1ArJQqFElEIvFEAnXy8VioUYjUSUQi8VihoZHxVKNRiNRJBCLxWKDkCPT4Ww2lUgkEIvFYoNAIAAMBAKBQCAQCAIAOw==';
      break;
    case 'makefile':
      $img = 'R0lGODlhEAAQAMIAAAAAAAAAhP///4SEhAAAAAAAAAAAAAAAACH5BAEKAAQALAAAAAAQABAAAANDSATc7gqESesE0erwmPgg6InAYH6nxz3hNwKhdwoqvDqkq5MDbf+CiQ/22sWGtSCFRlMsjCRMpKEUSp1OWOuKXXSkCQA7';
      break;

    case 'powerpoint':
      $img = 'R0lGODlhEAAQAKUAAIF/gMG/wP/+/3+BgAEBAP//4v//4////cDAvnd0Vf/zs//xz//wz//w0YBAAKVlJf/dvHo+HJ9jQdJSEdNTEns8G3w9HH0+HXw9HqFiQaFiQ64uAK0tAKwsAKkqCc5PLgEAAP////7+/sHBwcDAwIGBgYCAgAEBAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAD8ALAAAAAAQABAAAAaZwJ/QRCyaAACTcPkzhZ5QgYAodDwuGUs24/k8lcqHgkEuQzxPlBplYXAm3S4jQ6qHSKgMY8I5nxkXUHdsDG0FCXJ0dnh6egYGfxWCeG0dE2QcFHN1CAeMextZfIUhIgeeJx4QZAsLDQ0KDwgIJIMfHlkSERoYDw6nT3hLA8F3dXYBIMMipYJPIslLJpy0x3UjKEslawRr3ihBADs=';
      break;
    case 'crt':
      $img = 'R0lGODlhEAAQAKUAAAIKHgJGAlpqcmaaApairra+xsnO1oSRmppmMs7W3s6aZhomMtri6ioyOv7OmjZCTr7K0uLm6v7+zoqWok5aYr7Gzubq6kZOWtTb4woSJtre6pOeqrvCy8vS2+rq6sLK1FNb71Nb71Nb71Nb71Nb71Nb71Nb71Nb71Nb71Nb71Nb71Nb71Nb71Nb71Nb71Nb71Nb71Nb71Nb71Nb71Nb71Nb71Nb71Nb71Nb71Nb71Nb71Nb71Nb71Nb71Nb71Nb7yH5BAEKABAALAAAAAAQABAAAAaRQIhwSCwKBcgk5fJoLDIAAETgqVqvFgamA6AWvl8Op/LpdBKErgccvlY36ot8c/l4GAoFw5P2gj8GHgoSDggecFQXdHKBCg4KhohrBWRlHhgICBoeE3FzFx0eA6NUnQIRYwZnGKKjAR4HXREfgB0YGhoREVaxp2VpdxHAVb0dBHAQAIdSygexyVHM0dBRRtZEQQA7';
      break;
    case 'readme':
      $img = 'R0lGODdhEAAQAIQAAP///wAAAIaGhv/MAP//Zv//mQAA/8yZAP/MM///zP8A/8wAzMwAmf/78JkAmZkAZv//M5lmAMwAM////////////////////////////////////////////////////ywAAAAAEAAQAAAFcSAgBgBplmKKnqwqDAEME0FBGukrB7QNHDiRboAgFAoJETA3GCiehQUDkFgKiQRjEtAoWAGC4lMhdSS8QXAWmToTvoLjc7FwPAgQOLtdgAzgSWRSDw8If2kCKl14TXAqZ36NiAeUlZYRaQASm5ydEikhADs=';
      break;
    case 'java':
      $img = 'R0lGODlhEAAQAIQAACEhIUJCQgAAAEIhIVJSUv97e/85OXshIb1aWv8AAN4AAL29vZw5OZwAAFpaWjExMf///4SEhJx7e6WEhJycnGtra87Ozt7e3u/v74yMjK2trVNb71Nb71Nb71Nb71Nb7yH5BAEKAB8ALAAAAAAQABAAAAVv4CeOIhCQKBkIQJoCQuyicNx+wU3W8SkKvhEMMLDhZDtCwXBgHXUmAaKQUABxAV8WsGAkEg3HAwAVQCIQSaMxgVB0OXIFArFY6ItybEHvQy4CI1ksF350gCpkEBgZWRUYiDQXOSIBBBpBQpQjVyMhADs=';
      break;
    case 'excel':
      $img = 'R0lGODlhEAAQAMIAAISEhP///wAAAACEAMbGxgAAAAAAAAAAACH5BAEKAAcALAAAAAAQABAAAANPeArcrvCAQCsFQg2wK5/BlH0dSQmoMARb+xFwQGSruxEUPmvsC8e7Vk8ok6lMrp+RxBlaZramUJdZVFzFnfWnA1YllrARAuCaYV9Mar1OAAA7';
      break;
    case 'binary':
      $img = 'R0lGODlhEAAQAPQeAAAAADAwMEJCQlhYWG5ubnBwcLyec7yie4yJhpmZmb2oh7ytlL62r7y3scCvkcKzmsO6p8bBtuXMp+nVuM7Ozt7e3u3dx+7gyeji2fHn1urq6vTt4vfy6/39/QAAAAAAACH5BAEBAB4ALAAAAAAQABAAAAXIoOd53jAMwzAMw+B5nud53lBVVVU1QzKAnud5njdUXdd1GtElg+d5njdUXVd0XQFSXRJ4nucNVVcQXYcUhBB4nucNVdcRHdh1VYMEnud5Q9UVBNFxHMYEnud5Q9V1YNd13JYtged53lB1XcdxXIYtged53lCBXVdwW1ZcS+B5njdUXcEVWUFYSuB5ngcOFUdsRXYRkxJ4nucNFbcR2UUQxBF4Huh5Q7Vl1zVNk2QEnud5QxRB0LMoimEEoOd53hAEQRAEQRAEQQgAOw==';
      break;

    case 'text':
      $img = 'R0lGODlhEAAQAMIAAAAAAP///4SEhMbGxv///////////////yH5BAEKAAQALAAAAAAQABAAAANASErA3m0pEES9NqocqO/dYgVkWQ7aaJqoSAJwRwXttq41McL8nKs3WurV8w2DpB+xlzwilUihaECtWnOMYlGRAAA7';
      break;
    case 'image':
      $img = 'R0lGODlhEAAQAOMAAISEhP///8bGxv8AAIQAAAAAAISEAAD/AACEAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEKAA8ALAAAAAAQABAAAARcEMhJn70g6K0FuFbGbULwPQMhjkHhFkMqGgJtGEUgxMQjCq8CLsALaXCHJK5o1BWSBwOgQChgjgWEwAMEXp0BBMLl/A6x5WZtPfQ2g6+0j8Vx+7b4/NZqgftdFxEAOw==';
      break;
    case 'video':
      $img = 'R0lGODlhEAAQAKIEAP///8zMzJmZmQAAAP///wAAAAAAAAAAACH5BAEAAAQALAAAAAAQABAAAANLSErS7isKQCsIIqppaQCZ1FFDOYijN4RMqjbY01DwWgb4gAsrH5QEk243uOhODJwSU/Ihfb8fD3BDCq+VHyqlXXC4yM1y3FVcz4MEADs=';
      break;
    case 'audio':
      $img = 'R0lGODlhEAAQAMIAAAAAAL29AISEhP//AMbGxv///wAAAAAAACH5BAEKAAcALAAAAAAQABAAAANOeLoHwJAFAUSEYQBC7sqCQBSWUipZsRWk+aXa2J5BMQ4qwJ7oQOAxHe+QuakosxBlAnTsIo7UqnUBZKaWocIaeh60jcCGcAJbKx6I45IAADs=';
      break;

    default:
      $img = 'R0lGODlhDAAQANUAAD5Qh83g/8Tb/8jd/9Xl/9nn/93p/9Hi/+Hs/+Xu/wAAAOnx/4yl7PX4//H2//n7/+3z/0dakk5hm2N4tlNnolhtqV1zr22Dw3KJymd+vEhclENWjtrh8nqR0ZGp7Nzk9qjF+YKa3nGIyLLO/9zh9XyU142k493k+JSr6HeO0YWd44ef5VxxrsTX+oWc3P///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAC8ALAAAAAAMABAAAAZqQIZwKDy9jquHcqlCfZCNqFTUMpFeJYd2yxqBXJwUZEyOmCMdzGK9UCjYC8wlQX8n7IlLBsHv4zMTBoKCeAYTFgWJBYUFFhUEkJGSFRQHlgeMFBIBnJ2eEhoDoqOkGhsCqKmqGwCtrq+tQQA7';
      break;
  }

  header("Content-type: image/gif");
  print base64_decode($img);
  exit;

  /* --// some extra images */
  /* yellow folder */
  // $img = 'R0lGODlhEgAQAOfRAAAAAAAAAAAAAAAAAP//AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMzMzAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAJmZmQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP///wAAAAAAAAAAAAAAAAAAAP///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////yH5BAEKAP8ALAAAAAASABAAAAhhAP8JHEiwYMEqCBEaNFhFBQGHKqosHNjwocWICRVSjMaxo0eOVaxtvEjSYciNEEs+PCmwSjSVKVn+c5my5kqRLV/ajIlzZrSdF2W6hGlRaMajGXv+s8a0qVOnE6NKnfovIAA7';
  /* binary image */
  // $img = 'R0lGODlhEAAQAOMAAISGhMbHxgAAAP///wAAhAD//wAA////AP///////////////////////////////yH5BAEKAAgALAAAAAAQABAAAARcEMkJqq0zg8B7AEKGAENpfqBIEiyLhtpAFEb9SlhF1HYVkgMggGe4kTY+z0cAGFkCgqg06huVBATmBRnaGbACk6nqJUBBh6ixtNFWOClrEDqVUtgwwCE+mUr8GREAOw==';
  /* blank document */
  // $img = 'R0lGODlhEgAQAMIDAMbDxoSChAAAAP///////////////////yH5BAEKAAQALAAAAAASABAAAAM6SLHc9PCFQSsFIcJpKxiZxnWDYAoiaQFoNKpsql7tNtPyHLv3sNu3n6QnVBBrwyBSAWg6n8WTdIpMAAA7';
  /* //-- */
}

function url_encode($url) {
  return eregi_replace('[+]','%20',urlencode($url));
}


switch($_GET['p']) {
  case "source": showsource($_GET['file']); break;
  case "thumb": makethumb(base64_decode($_GET['file']),$GLOBALS['CONFIG']['THUMB_HEIGHT'],$GLOBALS['CONFIG']['THUMB_WIDTH']); break;
  case "mime": getmimefile($_GET['type']); break;
  case "logo": getmimefile('logo'); break;
  default: listdir($dir); break;
}
?>