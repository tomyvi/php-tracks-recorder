<?php

class SQLStructure {
  public $accuracy = 0;
  public $altitude = 0;
  public $battery_level = 0;
  public $heading = 0;
  public $description = "";
  public $event = "";
  public $latitude = 0;
  public $longitude = 0;
  public $radius = 0;
  public $trig = "";
  public $tracker_id = "";
  public $epoch = 0;
  public $vertical_accuracy = 0;
  public $velocity = 0;
  public $pressure = 0;
  public $connection = "";
  public $topic = "";
  public $place_id = 0;
  public $osm_id = 0;
}

class AbstractRecordStructure {

};



abstract class AbstractRecorder
{

  function __construct() {

  }

  //return tracker ID from structure
  abstract public function getTrackerID(AbstractRecordStructure $record_struct): int;

  //parse payload content & générate array of structures
  abstract public function parsePayload(string $payload): array;

  //migrate from initial structure to sql table compatible structure
  abstract public function formatRecordToSQLStructure(AbstractRecordStructure $record_struct): SQLStructure;

  //return latest location where trackerID is different from record provided
  abstract public function getFriendsLocation(AbstractRecordStructure $record_struct): array;

  //build response sent back as json array
  abstract public function buildResponseArray(string $response_msg, int $response_code): array;

  //save record to DB
  public function saveRecord(AbstractRecordStructure $record_struct, AbstractDb $sql): bool
  {
      // Add unit record to SQL

      //record only if same data not found at same epoch / tracker_id or with already better accuracy
    	if (!$this->isBetterRecordExisting($record_struct, $sql)) {
        try {
          $result = $sql->addRecord($this->formatRecordToSQLStructure($record_struct));

        } catch (\Exception $e) {
          _log("Error adding record : " . $e->getMessage());
          throw $e;

        }


    		return $result;

    	} else {
        throw new \Exception("Duplicate location found for epoch. Ignoring.", 200);

    		_log("Duplicate location found for epoc ".$record_struct->t." / tid '".$record_struct->tid."' - no insert");
    		$response_msg = 'Duplicate location found for epoch. Ignoring.';
    	}
  }

  //check if existing & better record doesnt already exist in DB
  public function isBetterRecordExisting(AbstractRecordStructure $record_struct, AbstractDb $sql): bool
  {
    $sql_struct = $this->formatRecordToSQLStructure($record_struct);
    return $sql->isBetterRecordExisting($sql_struct->tracker_id, $sql_struct->epoch, $sql_struct->accuracy);
  }

}
