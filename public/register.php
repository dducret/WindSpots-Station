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
    $logfile = "/".$station."_register.log";
  }
  $wlHandle = fopen($windspotsLog.$logfile, "a");
  $t = microtime(true);
  $micro = sprintf("%06d",($t - floor($t)) * 1000000);
  $micro = substr($micro,0,3);
  fwrite($wlHandle, Date("H:i:s").".$micro"." register.php: ".$message."\n");
  fclose($wlHandle);
}
function main() {
  // Start
  // check POST
  $post = $_POST;
  if(empty($post)) {
    logIt('No data received !');
    return;
  }
  $values = '';
  foreach($post as $key => $val) {
    $values .= '|' . $key . ' = ' . $val;
  }
  $values .= '|';
  // Check data received
  if ( !isset( $post['station'] ) || !isset( $post['date'] ) || !isset( $post['check'] )  ) {
    logIt('Missing data: process aborted !');
    return;
  }
  // Retrieve data from $post
  $StationName=$post['station'];
  $date=$post['date'];
  $check=$post['check'];
  logIt('Start', $StationName);
  $DisplayName = $post['displayName'];
  $ShortName = $post['shortName'];
  $MSName = $post['MSName'];
  $Information = $post['information'];
  $SpotType = $post['spotType'];
  $Online = $post['online'];
  $Maintenance = $post['maintenance'];
  $Reason = $post['reason'];
  $Altitude = $post['altitude'];
  $Latitude = $post['latitude'];
  $Longitude = $post['longitude'];
  $GMT = $post['gmt'];
  // create or update station
  if(!WindspotsDB::setStation($StationName, $DisplayName, $ShortName, $MSName, $Information, $Altitude,
                                      $Latitude, $Longitude, $SpotType, $Online, $Maintenance, $Reason, $GMT)) {
      logIt('Error Creating or Updating '.$StationName, $StationName);
  }
  logIt('End', $StationName);
// print_r('Upload finished'."<br>\n");
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
  <div align="center" class="style1">register_station</div>
  <div class="normal">
    <br/>Version 1.4 &copy; WindSpots.org 2022<br/>
  </div>
</body>
</html>