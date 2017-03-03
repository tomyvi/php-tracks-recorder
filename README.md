# owntracks-php-client
A simple and responsive self-hosted solution to record and map [Owntracks](https://owntracks.org/) [http payloads](http://owntracks.org/booklet/tech/http/).

## Screenshots
### Location records mapping
![Desktop view](https://cloud.githubusercontent.com/assets/2725792/23558839/5bfc332e-0035-11e7-95ac-45e3b909e5ea.png)

### Responsible interface & controls
![Responsive view](https://cloud.githubusercontent.com/assets/2725792/23558838/5be76e94-0035-11e7-9d39-84f4e9760fb3.png)

## Features
* Owntracks HTTP payloads recoding into database
* Interface to map location records
* Responsive : accessible on mobile and tablet !
* Calendar to select location records period

## Installation
### Requirements
- PHP 5 and above
- MySQL or equivalent (MariaDB,...)
- self hosted / dedicated server / mutualized hosting

That's it !

### Installation instructions
#### PHP Client
1. Download the source code and copy the content of the directory to your prefered location
2. Edit the ```config.inc.sample.php``` file to setup access to your database and rename to ```config.inc.php``` :
```php
	$_config['sql_host']          // sql server hostname
	$_config['sql_user']          // sql server username
	$_config['sql_pass']          // sql server username password
	$_config['sql_db']            // database name
	
	$_config['sql_prefix']        // table prefix
	
	$_config['default_accuracy']  // default maxymum accuracy for location record to be displayed on the map
```
3. Create datatable using schema.sql (in the 'sql' directory)

#### Owntracks app
Follow [Owntracks Booklet](http://owntracks.org/booklet/features/settings/) to setup your Owntracks app :

1. Setup your Owntracks app :
  1. Mode : HTTP
  2. URL : http://your_host/your_dir/record.php
  
## Usage
### First time access
Access map of today's recorded locations at : http://your_host/your_dir/

### Navigate through your recorded locations
* Use the "Previous" and "Next" buttons
* Manually change the From / To dates (next to the "Previous" button)

### Adjust map settings
* Use the "Config" button to :
  * Display or hide the individual markers (first and last markers for the period will always be displayed)
  * Change maximum accuracy for displayed location records

## Contributing
So far my team is small - just 1 person, but I'm willing to work with you!

I'd really like for you to bring a few more people along to join in.

## Credits
* [jQuery](https://jquery.com/) : the fast, small, and feature-rich JavaScript library
* [Bootstrap](http://getbootstrap.com/) : the sleek, intuitive, and powerful mobile first front-end framework for faster and easier web development
* [Bootstrap-Datepicker](https://eonasdan.github.io/bootstrap-datetimepicker/) : 
* [MomentJS](https://momentjs.com/) : Full featured date library for parsing, validating, manipulating, and formatting dates
* [LeafletJS](http://leafletjs.com/) : an open-source JavaScript library for mobile-friendly interactive maps
* [Leaflet Hotline](https://iosphere.github.io/Leaflet.hotline/) : A Leaflet plugin for drawing colored gradients along polylines.
* [js-cookie](https://github.com/js-cookie/js-cookie) : A simple, lightweight JavaScript API for handling browser cookies

## License
This project is published under the [GNU General Public License v3.0](https://choosealicense.com/licenses/gpl-3.0/)
