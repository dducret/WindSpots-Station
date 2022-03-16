# WindSpots-Station
Receiver of information sent by WindSpots weather stations

At boot, the weather station send:
   - station information on http://station.windspots.org/register.php via a POST

Each minute, when the weather station is up, the weather station send:
   - Weather data on http://station.windspots.org/data.php via a POST - Weather data are processed by a cron job in www/application/process-average.php
   - Image on http://station.windspots.org/image.php via a POST and the image is processed by station/application/process-image.sh

POST data:
<pre>
station			Station ID			6 Characters
date			Station time and date  		14 Charaters PHP "YmdHis"
data		Array
	stationtime	Station Time HoursMinutesSeconds
	direction	Wind Direction in °
	speed		Wind Speed in knts
	averagespeed	Wind Average Speed in knts
	temperature	Temperature in °C
	barometer	Barometer in hpa
	battery		Battery level (only Solar)
	name		Station Name
	imageage	Image Age in seconds
</pre>

Datas ares stored in windspots MariaDB database 
<pre>
	Table forecast
		id		int(11)		AUTO_INCREMENT
		station_name	varchar(6)
		reference_time	datetime
		speed		varchar(11)
		direction	varchar(11)
	Table sensor
 		id		int(11)
	 	station_name	varchar(6)
 		sensor_name	varchar(10)
	 	channel		tinyint(2)
	Table sensor_data 
	 	id		int(11)		AUTO_INCREMENT
 		sensor_id	int(11)
	 	sensor_time	datetime
 		battery	varchar(5)
	 	temperature	float
 		humidity	smallint(5)
	 	barometer	smallint(5)
 		direction	smallint(5)
	 	speed		float
 		average	float
	 	gust		float
 		uv		varchar(2)
	 	rain_rate	smallint(5)
		rain_total	smallint(5)
		ten		tinyint(1)
	Table station
		station_name	varchar(6)
		display_name	varchar(255)
		short_name	varchar(80)
	 	ms_name		varchar(8)
 		information	varchar(255)
		altitude	int(5)
		latitude	double
		longitude	double
		spot_type	tinyint(4)
		wind_id		smallint(5)
		barometer_id	smallint(5)
		temperature_id	smallint(5)
		water_id	smallint(5)
		online		tinyint(1)
		maintenance	tinyint(1)
		reason		varchar(255)
	 	GMT		tinyint(2)
		data_time	datetime
		image_time	datetime
</pre>


Spot Type:
<pre> 
 1 = KITE,
 2 = WINDSURF,
 4 = PADDLE,
 8 = PARAGLIDE,
 16 = SWIMMING
</pre>

station
Images are stored in tmp folder

