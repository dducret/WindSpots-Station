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
//
function logIt($message, $station = false) {
  global $windspotsLog;
  $dirname = dirname(__FILE__);
  if(!$station) {
    $logfile = "/error.log";
  } else {
    $logfile = "/".$station.".log";
  }
  $wlHandle = fopen($windspotsLog.$logfile, "a");
  $t = microtime(true);
  $micro = sprintf("%06d",($t - floor($t)) * 1000000);
  $micro = substr($micro,0,3);
  fwrite($wlHandle, Date("H:i:s").".$micro"." data.php: ".$message."\n");
  fclose($wlHandle);
}
function computeAverage($Array) {
  if(empty($Array))
    return 0;
  $count = 0;
  $total = 0;
  $average = 0;
  foreach($Array as $value) {
    $count++;
    $total+=(float)$value;
  }
  if($count!=0) 
    $average = round(($total / $count),2);
  return $average;
}
/**
 * The wind direction is a measure representing an angle in degrees (?).
 * We can't do a simple average as the winds' speed.
 * To compute this average, we use a more complex mathematical formula based on the sine and cosine of each direction.
 * 
 * T = arctan( Ux / Uy ) + K            =>  T average wind direction
 * OA   Ux = ( Sum of sin(T[i]) ) / N   =>  T[i] = a sample direction, N = number of samples
 *      Uy = ( Sum of cos(T[i]) ) / N   =>  T[i] = a sample direction, N = number of samples
 * 
 * K value according to Ux and Uy :
 *        | Ux = 0  | Ux > 0  | Ux < 0  |
 * Uy = 0 |    -    |  note1  |  note2  |
 * Uy > 0 |   360   |    0    |   360   |
 * Uy < 0 |   180   |   180   |   180   |
 * 
 * note 1: T will always return 90?
 * note 2: T will always return 270?
 */
function computeDirection($Array) {
  if(empty($Array))
    return 0;
  $count = 0;
  $wind_dir_Ux = 0;
  $wind_dir_Uy = 0;
  foreach($Array as $value) {
    $count++;
    $wind_direction = (float)$value;
    $wind_dir_Ux += sin( deg2rad( $wind_direction ) );
    $wind_dir_Uy += cos( deg2rad( $wind_direction ) );
  }
  if($count == 0)
    return 0;
  $wind_dir_Ux = $wind_dir_Ux / $count;
  $wind_dir_Uy = $wind_dir_Uy / $count;
  $K = 0;
  $wind_direction_average = 0;
  if ( ( $wind_dir_Ux == 0 ) && ( $wind_dir_Uy == 0 ) ) {
            $K = 0;
    } elseif ( ( $wind_dir_Ux == 0 ) && ( $wind_dir_Uy > 0 ) ) {
            $K = 360;
    } elseif ( ( $wind_dir_Ux == 0 ) && ( $wind_dir_Uy < 0 ) ) {
            $K = 180;
    } elseif ( ( $wind_dir_Ux > 0 ) && ( $wind_dir_Uy == 0 ) ) {
            $wind_direction_average = 90;
    } elseif ( ( $wind_dir_Ux > 0 ) && ( $wind_dir_Uy > 0 ) ) {
            $K = 0;
    } elseif ( ( $wind_dir_Ux > 0 ) && ( $wind_dir_Uy < 0 ) ) {
            $K = 180;
    } elseif ( ( $wind_dir_Ux < 0 ) && ( $wind_dir_Uy == 0 ) ) {
            $wind_direction_average = 270;
    } elseif ( ( $wind_dir_Ux < 0 ) && ( $wind_dir_Uy > 0 ) ) {
            $K = 360;
    } elseif ( ( $wind_dir_Ux < 0 ) && ( $wind_dir_Uy < 0 ) ) {
            $K = 180;
    }
  $wind_direction_average = rad2deg ( atan ( ( $wind_dir_Ux / $wind_dir_Uy ) ) ) + $K;
  return $wind_direction_average;
}
function computeGust($Array) {
  if(empty($Array))
    return 0;
  $gust = (float)0;
  foreach($Array as $value) {
    if((float)$value > $gust)
      $gust = (float)$value;
  }
  return $gust;
}
// 
function storeValues($StationName, $SensorName, $Channel, $LastUpdate, $Battery, $TemperatureA,
    $RelativeHumidityA, $BarometerA, $WindDirectionA, $WindSpeedA, $WindSpeedAverageA,  
    $UvIndexA, $RainRateA, $RainTotalA) {
  // compute 
  $Temperature = round(computeAverage($TemperatureA), 1);
  $RelativeHumidity = round(computeAverage($RelativeHumidityA), 0);
  $Barometer = round(computeAverage($BarometerA), 0);
  $WindDirection = round(computeDirection($WindDirectionA), 0);
  $WindSpeed = round(computeAverage($WindSpeedA), 2);
  $WindGust = round(computeGust($WindSpeedA), 2);
  $WindSpeedAverage = round(computeAverage($WindSpeedAverageA), 2);
  $UvIndex = round(computeAverage($UvIndexA), 0);
  $RainRate = round(computeAverage($RainRateA), 2);
  $RainTotal = round(computeAVerage($RainTotalA), 2);
  // check
  if($WindSpeed > 99 || $WindGust > 99 || $WindSpeedAverage > 99) {
    logIt('Worng values for '.$StationName.' - '.$WindSpeed.' - '.$WindGust).' - '.$WindSpeedAverage;
    return;
  }
  // Get sensor id
  $SensorId = WindspotsDB::getSensor($StationName, $SensorName, $Channel);
  if($SensorId == NULL) {
    logIt('No Sensor Id for '.$StationName.' - '.$SensorName.' - '.$Channel);
    return;
  }
  $result = WindspotsDB::setSensorData($SensorId, $LastUpdate, $Battery, $Temperature, $RelativeHumidity, $Barometer, 
                                      $WindDirection, $WindSpeed, $WindSpeedAverage, $WindGust, $UvIndex, $RainRate, $RainTotal, '0');
  // logIt("Storing: ".$SensorName."-".$Channel.":".$SensorId.", ".$LastUpdate.", ".$Battery.", ".$Temperature.", ".$RelativeHumidity.", ". $Barometer.", ".$WindDirection.", ".$WindSpeed.", ".$WindSpeedAverage.", ".$WindGust, $StationName);
}
// Start
function main() {
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
  if ( !isset( $post['station'] ) || !isset( $post['date'] ) || !isset( $post['check'] ) || !isset( $post['data'] ) ) {
    logIt('Missing data: process aborted !');
    return;
  }
  // Retrieve data from $post
  $StationName=substr( $post['station'], 0, 6 );
  $date=$post['date'];
  $check=$post['check'];
  $data=$post['data'];
  logIt('Start', $StationName);
  // decode json
  // logIt(json_encode($data), $StationName);
  $data = json_decode($data);
  if(empty($data)) {
    logIt('Data was empty.', $StationName);
    return;
  }
  // sort data
  usort($data, function($a, $b) { 
    if($a->name == $b->name) {
      $ad = new DateTime($a->last_update);
      $bd = new DateTime($b->last_update);
      if ($ad == $bd) {
        return 0;
      }
      return $ad < $bd ? -1 : 1;
    }
    return $a->name > $b->name ? 1 : -1; 
  }); 
  // extract data
  $SensorName = "";
  $toBeStored = false;
  $WindDirection = 0;
  $WindSpeed  = 0;
  $WindSpeedAverage = 0;
  foreach($data as $d) {
    // logIt(json_encode($d), $StationName);
    // Check time
    // Date should not exceeds 12 hours => 43200 seconds or not be in the future more than 30 seconds
    if(((strtotime('now') - strtotime($d->last_update)) > 43200) || ((strtotime('now') - strtotime($d->last_update)) <= (-58))) {
      LogIt('Difference: ' .(strtotime('now') - strtotime($d->last_update)), $StationName);
      logIt('Data are corrupted, last_update is missing : ' . json_encode($d), $StationName);
      continue;
    }
    if($SensorName == "") {
      $SensorName = $d->name;
      $Channel = $d->channel;
      $LastUpdate = $d->last_update;
      $FirtsUpdate = $LastUpdate;
      $Battery = $d->battery;
      $TemperatureA = array($d->temperature);
      $RelativeHumidityA = array($d->relative_humidity);
      $BarometerA = array($d->barometer);
      $WindDirectionA = array($d->wind_direction);
      $WindSpeedA = array($d->wind_speed);
      $WindSpeedAverageA = array($d->wind_speed_average);
      $UvIndexA = array($d->uv_index);
      $RainRateA = array($d->rain_rate);
      $RainTotalA = array($d->total_rain);
      $toBeStored = true;
      continue;
    }
    if($d->name == $SensorName) {
      // if time not in same minute then store 
      if((strtotime($d->last_update) - strtotime($FirtsUpdate)) <= (-60)) {
        // store results
        storeValues($StationName, $SensorName, $Channel, $LastUpdate, $Battery, $TemperatureA,
          $RelativeHumidityA, $BarometerA, $WindDirectionA, $WindSpeedA, $WindSpeedAverageA,
          $UvIndexA, $RainRateA, $RainTotalA);
        $toBeStored = false;
        $SensorName = $d->name;
        $Channel = $d->channel;
        $LastUpdate = $d->last_update;
        $FirtsUpdate = $LastUpadte;
        $Battery = $d->battery;
        $TemperatureA = array($d->temperature);
        $RelativeHumidityA = array($d->relative_humidity);
        $BarometerA = array($d->barometer);
        $WindDirectionA = array($d->wind_direction);
        $WindSpeedA = array($d->wind_speed);
        $WindSpeedAverageA = array($d->wind_speed_average);
        $UvIndexA = array($d->uv_index);
        $RainRateA = array($d->rain_rate);
        $RainTotalA = array($d->total_rain);
        $toBeStored = true;
        continue;
      } 
      array_push($TemperatureA, $d->temperature);
      array_push($RelativeHumidityA, $d->relative_humidity);
      array_push($BarometerA, $d->barometer);
      array_push($WindDirectionA, $d->wind_direction);
      array_push($WindSpeedA, $d->wind_speed);
      array_push($WindSpeedAverageA, $d->wind_speed_average);
      array_push($UvIndexA, $d->uv_index);
      array_push($RainRateA, $d->rain_rate);
      array_push($RainTotalA, $d->total_rain);
    } else {  
      // store results
      storeValues($StationName, $SensorName, $Channel, $LastUpdate, $Battery, $TemperatureA,
          $RelativeHumidityA, $BarometerA, $WindDirectionA, $WindSpeedA, $WindSpeedAverageA,
          $UvIndexA, $RainRateA, $RainTotalA);
      $toBeStored = false;
      $SensorName = $d->name;
      $Channel = $d->channel;
      $LastUpdate = $d->last_update;
      $FirtsUpdate = $LastUpdate;
      $Battery = $d->battery;
      $TemperatureA = array($d->temperature);
      $RelativeHumidityA = array($d->relative_humidity);
      $BarometerA = array($d->barometer);
      $WindDirectionA = array($d->wind_direction);
      $WindSpeedA = array($d->wind_speed);
      $WindSpeedAverageA = array($d->wind_speed_average);
      $UvIndexA = array($d->uv_index);
      $RainRateA = array($d->rain_rate);
      $RainTotalA = array($d->total_rain);
      $toBeStored = true;
    }
  }
  if($toBeStored) {
    // store results
    storeValues($StationName, $SensorName, $Channel, $LastUpdate, $Battery, $TemperatureA,
          $RelativeHumidityA, $BarometerA, $WindDirectionA, $WindSpeedA, $WindSpeedAverageA,
          $UvIndexA, $RainRateA, $RainTotalA);
  }
  // update station data_time
  WindspotsDB::setStationDataTime($StationName);
  logIt('Upload finished.', $StationName);
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
  <div align="center" class="style1">data</div>
  <div class="normal">
    <br/>Version 1.4 &copy; WindSpots.org 2022<br/>
  </div>
</body>
</html>