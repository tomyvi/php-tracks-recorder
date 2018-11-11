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

  public function getTrackerID(object $rec): int
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

          $rec->timestamp = strval($data['properties']['timestamp']);
          $rec->altitude = intval($data['properties']['altitude']);
          $rec->speed = intval($data['properties']['speed']);
          $rec->horizontal_accuracy = intval($data['properties']['horizontal_accuracy']);
          $rec->vertical_accuracy = intval($data['properties']['vertical_accuracy']);
          $rec->motion = $data['properties']['motion'];
          $rec->battery_state = strval($data['properties']['battery_state']);
          $rec->battery_level = floatval($data['properties']['battery_level']);
          $rec->wifi = strval($data['properties']['wifi']);
          $rec->device_id = strval($data['properties']['device_id']);
          $rec->latitude = floatval($data['geometry']['coordinates'][0]);
          $rec->longitude = floatval($data['geometry']['coordinates'][1]);

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

  public function formatRecordToSQLStructure(object $record_struct): object
  {
    $sqlRecord = new SQLStructure();

    $sqlRecord->accuracy = intval($record_struct->horizontal_accuracy);
    $sqlRecord->altitude = intval($record_struct->altitude);
    $sqlRecord->battery_level = intval($record_struct->battery_level * 100);
    $sqlRecord->description = implode(", ", $record_struct->motion);
    $sqlRecord->latitude = floatval($record_struct->latitude);
    $sqlRecord->longitude = floatval($record_struct->longitude);
    $sqlRecord->tracker_id = strval($record_struct->device_id);
    $sqlRecord->vertical_accuracy = intval($record_struct->vertical_accuracy);
    $sqlRecord->velocity = ($record_struct->speed)*3.6; // m/s to km/h
    if($record_struct->wifi != '') $sqlRecord->connection = 'w';

    $d = new DateTime($record_struct->timestamp);
    $sqlRecord->epoch = $d->format('U');

    return $sqlRecord;
  }

  public function getFriendsLocation(object $record_struct): array
  {
    // friends feature not implemented in Overland iOS app
    return array();
  }

  public function buildResponseArray(string $response_msg, int $response_code): array
  {
    if($response_code != 500) $response['result'] = 'ok';
    return $response;
  }

}
