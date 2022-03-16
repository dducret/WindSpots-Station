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
function logIt($message) {
  global $windspotsLog;
  $logfile = "/forecast.log";
  $wlHandle = fopen($windspotsLog.$logfile, "a");
  $t = microtime(true);
  $micro = sprintf("%06d",($t - floor($t)) * 1000000);
  $micro = substr($micro,0,3);
  fwrite($wlHandle, Date("H:i:s").".$micro"." process-forecast.php: ".$message."\n");
  fclose($wlHandle);
}
function get_unique_val($val, $arr) {
  if ( in_array($val, $arr) ) {
    $d = 2; // initial prefix 
    preg_match("~_([\d])$~", $val, $matches); // check if value has prefix
    $d = $matches ? (int)$matches[1]+1 : $d;  // increment prefix if exists
    preg_match("~(.*)_[\d]$~", $val, $matches);
    $newval = (in_array($val, $arr)) ? get_unique_val($matches ? $matches[1].'_'.$d : $val.'_'.$d, $arr) : $val;
    return $newval;
  } else {
    return $val;
  }
}
function unique_arr($arr) {
  $_arr = array();
  foreach ( $arr as $k => $v ) {
    $arr[$k] = get_unique_val($v, $_arr);
    $_arr[$k] = $arr[$k];
  }
  unset($_arr);
  return $arr;
}
function storeForecast($attachmentId = array()) {
  logIt('Storing Forecast.' );
  $nbForecast = 0;
  foreach ( $attachmentId as $id => $file ) {
    logIt('  ' . $file );
    $csv_data = array_map(function($v){return str_getcsv($v, ";");}, file($file));// reads the csv file in php array
    $csv_header = $csv_data[18];//creates a copy of csv header array
    $csv_header = unique_arr($csv_header);
    for($i=0;$i<19;$i++) {
      unset($csv_data[$i]);//removes unused lines
    }
    $data = array();
    foreach($csv_data as $row){
      $data[] = array_combine($csv_header, $row);// adds header to each row as key
    }
    // unlink($file);  // delete file
    rename($file, $file.".pf");  // rename the file to keep history
    foreach($data as $station => $station_forecast ) {
      $reference_time = NULL;
      $lead_time = NULL;
      $ms_name = NULL;
      foreach($station_forecast as $forecast_id => $forecast_data ) {
        if(!$reference_time) {
          $reference_time = date( 'Y-m-d H:i:s', strtotime( $forecast_data ) );
          continue;
        }
        if(!$lead_time) {
          $lead_time = $forecast_data;
          continue;
        }
        if(!$ms_name) {
          $ms_name = $forecast_id;
          $station = WindspotsDB::getStationByMSName($ms_name);
          if($station) {
            $station_name = $station["station_name"];
            logIt(''.$station_name);
          } else {
            $station_name = null;
            logIt('Error Station name not found: '.$ms_name);
          }
          $speed = $forecast_data;
          continue;
        }
        $direction = $forecast_data;
        if($station_name) {
           if(!WindspotsDB::setForecast($station_name,$reference_time,$speed,$direction)) {
             logIt('Query has failed ( insert `forecast` ) : ' . mysqli_error( $dbLink ));
          } else {
            logIt('    '.$reference_time);
          }
          $nbForecast++;
          // special case
          // duplicate Saleve table to Saleve les crets
          if(strcmp($station_name, "CHGE04")==0) {
            if(!WindspotsDB::setForecast("CHGE06",$reference_time,$speed,$direction)) {
              logIt('Query has failed ( insert `forecast` ) : ' . mysqli_error( $dbLink ));
            }
          }
          // duplicate Monteynard
          if(strcmp($station_name, "FRMT01")==0) {
            if(!WindspotsDB::setForecast("FRMT02",$reference_time,$speed,$direction)) {
              logIt('Query has failed ( insert `forecast` ) : ' . mysqli_error( $dbLink ));
            }
            if(!WindspotsDB::setForecast("FRMT03",$reference_time,$speed,$direction)) {
              logIt('Query has failed ( insert `forecast` ) : ' . mysqli_error( $dbLink ));
            }
          }
        }    
        $ms_name = NULL;
      }
    }
  }
  logIt('Forecast Stored: ' . $nbForecast );
  return true;
}
// Override time & memory limit to be able to finish the script ;-)
$MEMLIMIT = ini_get( 'memory_limit' );
$TIMELIMIT = ini_get( 'max_execution_time' );
ini_set( 'memory_limit', '-1' );
set_time_limit( 0 );
$aryAttachmentsId = array();
$attachmentId = 0;
$xmlDir = $rootPath."/tmp/";
$files = glob($xmlDir . '*.xml', GLOB_BRACE);
foreach($files as $file) {
  //do your work here
  logIt('FILE - Get data from ' . $file);
  $aryAttachmentsId[$attachmentId++] = $file;
}
if (!storeForecast($aryAttachmentsId)) {
    LogIt('There has been a problem with forecasts\' storage !');
    return false;
}
logIt('FILE - Terminated !');
// Restore time & memory limit as original !!
ini_set( 'memory_limit', $MEMLIMIT );
set_time_limit( $TIMELIMIT );