<?php
// WindSpots Service Get Water Temp from www.hydrodaten.ch every 10 mn
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
function logIt($message) {
  global $windspotsLog;
  $logfile = "/get-water-temp.log";
  $wlHandle = fopen($windspotsLog.$logfile, "a");
  $t = microtime(true);
  $micro = sprintf("%06d",($t - floor($t)) * 1000000);
  $micro = substr($micro,0,3);
  fwrite($wlHandle, Date("H:i:s").".$micro"." forecast.php: ".$message."\n");
  fclose($wlHandle);
}
function checkWsSensor($StationName, $SensorName, $Channel) {
  if($Channel > 3)
    $Channel = 0;
  $SensorId =  WindspotsDB::getSensor($StationName, $SensorName, $Channel);
  if($SensorId == NULL) {
    $SensorId = WindspotsDB::setSensor($StationName, $SensorName, $Channel);
    if(!$SensorId) {
      logIt('Invalid query for setSensor');
      return 0;
    }
    logIt('Sensor created: '.$SensorId);
  } 
  return $SensorId;  
}
function storeValues($StationName, $SensorName, $Channel, $LastUpdate, $Battery, $Temperature,
    $RelativeHumidity, $Barometer, $WindDirection, $WindSpeed, $WindSpeedAverage,  
    $UvIndex, $RainRate, $RainTotal) {
  // Get sensor id
  if($Channel > 3)
    $Channel = 0;
  $SensorId=checkWsSensor($StationName, $SensorName, $Channel);  
  if($SensorId == 0) {
    logIt('No Sensor Id');
    return;
  }
  // set last update seconds to zero
  $lastUpdate=strtotime($LastUpdate);
  $LastUpdate=date('Y-m-d H:i:00', $lastUpdate);  
  $WindGust = $WindSpeed;
  // store value
  $result=WindspotsDB::setSensorData($SensorId, $LastUpdate, $Battery, $Temperature, $RelativeHumidity, $Barometer, $WindDirection,
              $WindSpeed, $WindSpeedAverage, $WindGust, $UvIndex, $RainRate, $RainTotal);
  if($result==NULL) {
    logIt('Invalid query: ');
  }
  // logIt("Storing: ".$SensorName."-".$Channel.":".$SensorId.", ".$LastUpdate.", ".$Battery.", ".$Temperature.", ".$RelativeHumidity.", ". $Barometer.", ".$WindDirection.", ".$WindSpeed.", ".$WindSpeedAverage.", ".$WindGust, $StationName);
}
//
$urlLeman = 'http://www.hydrodaten.admin.ch/fr/2606.html';
$temp_date = 0;
$temp = 0;
$data = file_get_contents($urlLeman);
$bdate = false;
$nbline = 0;
foreach(explode("\n", $data) as $line) {
 if(!empty($line)) {
   if(preg_match('/\wature<br>/', $line)) {
     $temp_date = substr($line, 86, 16);
     $bdate = true;
   }
   if($bdate) {
     if(preg_match('/\wente/', $line)) {
	     if($nbline++ == 2) {
	       $pos = strpos($line, '>');
	       $pos2 = strpos($line, '<');
	       $temp = substr($line, $pos+1, $pos2-2);
	       $temp = preg_replace("/[^0-9,.]/", "", $temp);
	       break;
	     }
     }
   } 
 }
}
logIt('Temperature Leman: ' . $temp_date . ' - ' . $temp);
$StationName = 'CHGE01';
$SensorName = 'WSHDA';
$Channel = '1';
$LastUpdate = $temp_date;
$Battery = '0';
$Temperature = $temp;
$RelativeHumidity = $Barometer = $WindDirection = 0;
$WindSpeed = $WindSpeedAverage = 0.0;
$UvIndex = $RainRate = $RainTotal = 0;
storeValues($StationName, $SensorName, $Channel, $LastUpdate, $Battery, $Temperature,
        $RelativeHumidity, $Barometer, $WindDirection, $WindSpeed, $WindSpeedAverage,
        $UvIndex, $RainRate, $RainTotal);
?>
