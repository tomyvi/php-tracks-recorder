<?php
require_once(__DIR__ . '/AbstractRecorder.php');

class OverlandRecordStructure extends AbstractRecordStructure {

  //https://github.com/aaronpk/Overland-iOS#api
  public $timestamp;
  public $altitude;
  public $speed;
  public $horizontal_accuracy;
  public $vertical_accuracy;
  public $motion;
  public $battery_state;
  public $battery_level;
  public $wifi;
  public $device_id;
  public $latitude;
  public $longitude;

};

class OverlandRecorder extends AbstractRecorder
{

  public function getTrackerID(AbstractRecordStructure $rec): int
  {
    return strval($rec->device_id);
  }

  public function parsePayload(string $payload): array
  {
    global $fpp, $_config;

    $data =  @json_decode($payload, true);

    $response_msg = null;
    $records = array();

    if($data != null && json_last_error() == JSON_ERROR_NONE){
      if (count($data['locations']) > 0) {

        foreach($data['locations'] as $loc){

          $rec = new OverlandRecordStructure();

          $rec->timestamp = strval($loc['properties']['timestamp']);
          $rec->altitude = max(0, intval($loc['properties']['altitude']));
          $rec->speed = max(0, intval($loc['properties']['speed']));
          $rec->horizontal_accuracy = max(0, intval($loc['properties']['horizontal_accuracy']));
          $rec->vertical_accuracy = max(0, intval($loc['properties']['vertical_accuracy']));
          $rec->battery_state = strval($loc['properties']['battery_state']);
          $rec->battery_level = floatval($loc['properties']['battery_level']);
          $rec->wifi = strval($loc['properties']['wifi']);
          $rec->device_id = strval($loc['properties']['device_id']);
          $rec->latitude = floatval($loc['geometry']['coordinates'][1]);
          $rec->longitude = floatval($loc['geometry']['coordinates'][0]);

          //if(is_array($data['properties']['motion']){
          //  $rec->motion = $data['properties']['motion'];
          //}

          $records[] = $rec;
        }



        return $records;

      } else {
        _log("No locations in payload : " . $data['locations']);
        throw new \Exception("No locations in payload", 200);

      }
    }else {
      _log("No data to read : " . $data);
      throw new \Exception("No data to read", 200);

    }


  }

  public function formatRecordToSQLStructure(AbstractRecordStructure $record_struct): SQLStructure
  {
    $sqlRecord = new SQLStructure();

    $sqlRecord->accuracy = intval($record_struct->horizontal_accuracy);
    $sqlRecord->altitude = intval($record_struct->altitude);
    $sqlRecord->battery_level = intval($record_struct->battery_level * 100);
    $sqlRecord->latitude = floatval($record_struct->latitude);
    $sqlRecord->longitude = floatval($record_struct->longitude);
    $sqlRecord->tracker_id = strval($record_struct->device_id);
    $sqlRecord->vertical_accuracy = intval($record_struct->vertical_accuracy);
    $sqlRecord->velocity = ($record_struct->speed)*3.6; // m/s to km/h
    if($record_struct->wifi != '') $sqlRecord->connection = 'w';

    //if(is_array($record_struct->motion)){
    //  $sqlRecord->description = implode(", ", $record_struct->motion);
    //}



    $d = new DateTime($record_struct->timestamp);
    $sqlRecord->epoch = $d->format('U');

    return $sqlRecord;
  }

  public function getFriendsLocation(AbstractRecordStructure $record_struct): array
  {
    // friends feature not implemented in Overland iOS app
    $friends = array();
    return $friends;
  }

  public function buildResponseArray(string $response_msg, int $response_code): array
  {
    $response = array();
    if($response_code != 500) $response['result'] = 'ok';
    return $response;
  }

}
