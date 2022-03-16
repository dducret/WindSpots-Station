<?php
$rootPath=__FILE__;
$scriptPath=baseName($rootPath);
$rootPath=str_replace($scriptPath,'',$rootPath);
$rootPath=realPath($rootPath.'../');
$rootPath=str_replace('\\','/',$rootPath);
date_default_timezone_set('Europe/Zurich');
$windspotsLog =  $rootPath."/log";
function logIt($message) {
  global $windspotsLog;
  $logfile = "/forecast.log";
  $wlHandle = fopen($windspotsLog.$logfile, "a");
  $t = microtime(true);
  $micro = sprintf("%06d",($t - floor($t)) * 1000000);
  $micro = substr($micro,0,3);
  fwrite($wlHandle, Date("H:i:s").".$micro"." forecast.php: ".$message."\n");
  fclose($wlHandle);
}
//
function main() {
  global $rootPath;
  $file = null;
  $name = $_FILES['file']['name'];
  $type = $_FILES['file']['type']; 
  $size = $_FILES['file']['size']; 
  $temp = $_FILES['file']['tmp_name'];
  $error = $_FILES['file']['error'];
  $xml = $rootPath."/tmp/" . $name;
  if($fp = fopen($temp,"rb")) {
    $file = fread ($fp, filesize($temp));
    fclose($fp);
    if($fp = fopen($xml,"wb")) {
      fwrite($fp,$file);
      fclose($fp);
      logIt('XML saved: '.$xml);
      exit();
    } else  {
      logIt('Open wb failed for '.$xml.' - '.json_encode($_FILES));
    }
  } else {
    logIt('Open rb failed.'.$xml.' - '.json_encode($_FILES));
  }
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
  <div align="center" class="style1">forecast</div>
  <div class="normal">
    <br/>Version 1.4 &copy; WindSpots.org 2022<br/>
  </div>
</body>
</html>