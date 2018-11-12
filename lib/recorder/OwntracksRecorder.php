<?php
require_once(__DIR__ . '/AbstractRecorder.php');

class OwntracksRecordStructure extends AbstractRecordStructure {

  //http://owntracks.org/booklet/tech/json/

  public $acc;
  public $alt;
  public $batt;
  public $cog;
  public $desc;
  public $event;
  public $lat;
  public $lon;
  public $rad;
  public $t;
  public $tid;
  public $tst;
  public $vac;
  public $vel;
  public $p;
  public $con;
  public $topic;

};

class OwntracksRecorder extends AbstractRecorder
{

  public function getTrackerID(AbstractRecordStructure $rec): int
  {
    return strval($rec->tid);
  }

  public function parsePayload(string $payload): array
  {
    global $fpp, $_config;

    $data =  @json_decode($payload, true);

    $response_msg = null;
    $records = array();

    if($data != null && json_last_error() == JSON_ERROR_NONE){
      if ($data['_type'] == 'location') {

        $rec = new OwntracksRecordStructure();
        //http://owntracks.org/booklet/tech/json/
        //iiiissddissiiidsiis
        if (array_key_exists('acc', $data)) $rec->acc = intval($data['acc']);
        if (array_key_exists('alt', $data)) $rec->alt = intval($data['alt']);
        if (array_key_exists('batt', $data)) $rec->batt = intval($data['batt']);
        if (array_key_exists('cog', $data)) $rec->cog = intval($data['cog']);
        if (array_key_exists('desc', $data)) $rec->desc = strval($data['desc']);
        if (array_key_exists('event', $data)) $rec->event = strval($data['event']);
        if (array_key_exists('lat', $data)) $rec->lat = floatval($data['lat']);
        if (array_key_exists('lon', $data)) $rec->lon = floatval($data['lon']);
        if (array_key_exists('rad', $data)) $rec->rad = intval($data['rad']);
        if (array_key_exists('t', $data)) $rec->t = strval($data['t']);
        if (array_key_exists('tid', $data)) $rec->tid = strval($data['tid']);
        if (array_key_exists('tst', $data)) $rec->tst = intval($data['tst']);
        if (array_key_exists('vac', $data)) $rec->vac = intval($data['vac']);
        if (array_key_exists('vel', $data)) $rec->vel = intval($data['vel']);
        if (array_key_exists('p', $data)) $rec->p = floatval($data['p']);
        if (array_key_exists('conn', $data)) $rec->conn = strval($data['conn']);
        if (array_key_exists('topic', $data)) $rec->topic = strval($data['topic']);

        $records[] = $rec;

        return $records;

      } else {
        _log("OK type is not location : " . $data['_type']);
        throw new \Exception("Data Type is not Location", 200);

      }
    }else {
      _log("No data to read : " . $data);
      throw new \Exception("No data to read", 200);

    }


  }

  public function formatRecordToSQLStructure(AbstractRecordStructure $record_struct): SQLStructure
  {
    $sqlRecord = new SQLStructure();

    $sqlRecord->accuracy = intval($record_struct->acc);
    $sqlRecord->altitude = intval($record_struct->alt);
    $sqlRecord->battery_level = intval($record_struct->batt);
    $sqlRecord->heading = intval($record_struct->cog);
    $sqlRecord->description = strval($record_struct->desc);
    $sqlRecord->event = strval($record_struct->event);
    $sqlRecord->latitude = floatval($record_struct->lat);
    $sqlRecord->longitude = floatval($record_struct->lon);
    $sqlRecord->radius = intval($record_struct->rad);
    $sqlRecord->trig = strval($record_struct->t);
    $sqlRecord->tracker_id = strval($record_struct->tid);
    $sqlRecord->epoch = intval($record_struct->tst);
    $sqlRecord->vertical_accuracy = intval($record_struct->vac);
    $sqlRecord->velocity = intval($record_struct->vel);
    $sqlRecord->pressure = floatval($record_struct->p);
    $sqlRecord->connection = strval($record_struct->conn);
    $sqlRecord->topic = strval($record_struct->topic);

    return $sqlRecord;
  }

  public function getFriendsLocation(AbstractRecordStructure $record_struct): array
  {
    return $sql->getFriends($record_struct->getTrackerID());
  }

  public function buildResponseArray(string $response_msg, int $response_code): array
  {
    $response = array();

    if (!is_null($response_msg)) {
        // Add status message to return object (to be shown in app)
        $response[] = array(
            '_type' => 'cmd',
            'action' => 'action',
            'content' => $response_msg,
        );
    }

    return $response;
  }


}
