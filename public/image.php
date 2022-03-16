<?php
$rootPath=__FILE__;
$scriptPath=baseName($rootPath);
$rootPath=str_replace($scriptPath,'',$rootPath);
$rootPath=realPath($rootPath.'../');
$rootPath=str_replace('\\','/',$rootPath);
date_default_timezone_set('Europe/Zurich');
$windspotsLog =  $rootPath."/log";
// API
$windspotsAPI = $rootPath."/../api/library/windspots";
require_once $windspotsAPI.'/db.php';
function logIt($message, $station) {
  global $windspotsLog;
  $dirname = dirname(__FILE__);
  if(empty($station)) {
    $logfile = "/error.log";
  } else {
    $logfile = "/".$station."_image.log";
  }
  $wlHandle = fopen($windspotsLog.$logfile, "a");
  $t = microtime(true);
  $micro = sprintf("%06d",($t - floor($t)) * 1000000);
  $micro = substr($micro,0,3);
  fwrite($wlHandle, Date("H:i:s").".$micro"." image.php: ".$message."\n");
  fclose($wlHandle);
}
function main() {
	global $rootPath;
  $uploaddir = $rootPath."/tmp";
  // Start
  $file = $_FILES['file']['name'];
  $type = $_FILES['file']['type']; 
  $size = $_FILES['file']['size']; 
  $temp = $_FILES['file']['tmp_name'];
  $error = $_FILES['file']['error'];
  $tag = $_REQUEST['tag'];
  $camrotate = $_REQUEST['camrotate'];
  $station=$file;
  logIt('Upload Start - size:' . $size . ' tag:' . $tag . ' rotate:' . $camrotate , $station);
  if( strlen($station) != 7) {
      logIt('Station Length Error.', $station); 
      return;
  }
  if( $size >= 800000) {
      logIt('Image size >= 800 000: ' . $size, $station);
      return;
  }
  if( $size < 4800) {
      logIt('Image size < 4 800: ' . $size, $station);
      return;
  }
  $uploadfile = $uploaddir . '/'.$station.'.jpg';
  if($fp = fopen($temp,"rb")) {
    $file = fread ($fp, filesize($temp));
    fclose($fp);
  } else {
    logIt('Open rb failed.', $station);
    return;
  }
  if($fp = fopen($uploadfile,"wb")) {
    fwrite($fp,$file);
    fclose($fp);
  } else  {
    logIt('Open wb failed for '.$uploadfile, $station);
    return;
  }
  // print_r('Upload finished'."<br>\n");
  // update station image_time
  WindspotsDB::setStationImageTime(substr($station, 0, -1));
  logIt('Upload finished.', $station);
  //################################
  //# legacy send image
  $command='/usr/bin/curl -X POST -s -F "tag='.$tag.'" -F "camrotate='.$camrotate.'" -F "file=@'.$uploadfile.';filename='.$station.'" -m 30 http://station.windspots.com/image_upload.php';
  $output=shell_exec($command);
  //logIt('Shell_exec curl : '.$output, $station);
  //logIt($command, $station);
  //################################
  // process image
  $output=null;
  $output=shell_exec(''.$rootPath.'/application/process-image.sh '.$station.' '.$uploadfile.' "'.$tag.'" '.$camrotate);
// logIt('Shell_exec process-image.sh: '.$output, $station);
}
main();
?>
<!DOCTYPE HTML>
<html>
<head>
  <meta name="robots" content="noindex,nofollow"/>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style type="text/css">
  img         { vertical-align: middle; border-style: none; padding-bottom: 5px }
  .normal     { font-family: Arial, Helvetica, sans-serif; font-size: 10pt; font-weight: normal; font-style: normal }
  .style1     { font-family: Arial, Helvetica, sans-serif; font-size: 18pt; font-weight: bold; color: #009900 }
  </style>
  <title>WindSpots Station</title>
</head>
<body>
  <div align="center"><img src="logo.png" alt="logo" /></div>
  <div align="center" class="style1">WindSpots Station</div>
  <div align="center" class="style1">image_upload</div>
  <div class="normal">
    <br/>Version 1.4 &copy; WindSpots.org 2022<br/>
  </div>
</body>
</html>