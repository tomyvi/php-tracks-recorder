<?php

class SQLStructure {
  public $accuracy;
  public $altitude;
  public $battery_level;
  public $heading;
  public $description;
  public $event;
  public $latitude;
  public $longitude;
  public $radius;
  public $trig;
  public $tracker_id;
  public $epoch;
  public $vertical_accuracy;
  public $velocity;
  public $pressure;
  public $connection;
  public $topic;
  public $place_id;
  public $osm_id;
}

class AbstractRecordStructure {

};



abstract class AbstractRecorder
{

  function __construct() {

  }

  //return tracker ID from structure
  abstract public function getTrackerID(object $record_struct): int;

  //parse payload content & générate array of structures
  abstract public function parsePayload(string $payload): array;

  //migrate from initial structure to sql table compatible structure
  abstract public function formatRecordToSQLStructure(object $record_struct): object;

  //return latest location where trackerID is different from record provided
  abstract public function getFriendsLocation(object $record_struct): array;

  //build response sent back as json array
  abstract public function buildResponseArray(string $response_msg, int $response_code): array;

  //save record to DB
  public function saveRecord(object $record_struct, object $sql): bool
  {
      // Add unit record to SQL

      //record only if same data not found at same epoch / tracker_id or with already better accuracy
    	if (!$this->isBetterRecordExisting($record_struct, $accuracy)) {

    		$result = $sql->addRecord($this->formatRecordToSQLStructure($record_struct));

    		return $result;

    	} else {
        throw new \Exception("Duplicate location found for epoch. Ignoring.", 200);

    		_log("Duplicate location found for epoc ".$record_struct->t." / tid '".$record_struct->tid."' - no insert");
    		$response_msg = 'Duplicate location found for epoch. Ignoring.';
    	}
  }

  //check if existing & better record doesnt already exist in DB
  public function isBetterRecordExisting(object $record_struct, object $sql): bool
  {
    $sql_struct = $this->formatRecordToSQLStructure($record_struct);
    return $sql->isBetterRecordExisting($sql_struct->tracker_id, $sql_struct->epoch, $sql_struct->accuracy);
  }

}
