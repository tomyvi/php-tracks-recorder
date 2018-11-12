<?php

abstract class AbstractDb
{
    protected $db;
    protected $prefix;

    // Run query without result
    abstract protected function execute(string $sql, array $params): bool;

    //// Run query and fetch results
    abstract protected function query(string $sql, array $params): array;

    public function isBetterRecordExisting(string $trackerId, int $epoch, int $accuracy): bool
    {
        $sql = 'SELECT accuracy FROM ' . $this->prefix . 'locations WHERE tracker_id = ? AND epoch = ?';
        $result = $this->query($sql, array($trackerId, $epoch));

        $already_better_accuracy = false;

        foreach ($result as $data) {
            if($data['accuracy'] < $accuracy) $already_better_accuracy = true;
        }

        return ((count($result) > 0) || $already_better_accuracy);
    }

    public function addRecord(SQLStructure $sql_record): bool {
        $sql = 'INSERT INTO ' . $this->prefix . 'locations (accuracy, altitude, battery_level, heading, description, event, latitude, longitude, radius, trig, tracker_id, epoch, vertical_accuracy, velocity, pressure, connection, topic, place_id, osm_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $params = array(
            $sql_record->accuracy,
            $sql_record->altitude,
            $sql_record->battery_level,
            $sql_record->heading,
            $sql_record->description,
            $sql_record->event,
            $sql_record->latitude,
            $sql_record->longitude,
            $sql_record->radius,
            $sql_record->trig,
            $sql_record->tracker_id,
            $sql_record->epoch,
            $sql_record->vertical_accuracy,
            $sql_record->velocity,
            $sql_record->pressure,
            $sql_record->connection,
            $sql_record->topic,
            $sql_record->place_id,
            $sql_record->osm_id);

        $result = $this->execute($sql, $params);

        if(!$result){
          throw new \Exception("Error adding Record " . $this->db->error, 500);

        }

        return $result;
    }

    public function getFriends(string $trackerId): array
    {
        $sql = "select * from ".$_config['sql_prefix']."locations a JOIN (SELECT MAX(epoch) AS epoch, tracker_id FROM ' . $this->prefix . 'locations WHERE tracker_id != ? GROUP BY tracker_id) b ON a.epoch = b.epoch AND a.tracker_id = b.tracker_id";
        $sql = 'SELECT * FROM ' . $this->prefix . 'locations WHERE epoch >= ? AND epoch <= ? AND accuracy < ? AND altitude >=0 ORDER BY tracker_id, epoch ASC';
        $result = $this->query($sql, array($trackerId));


        $friends = array();

        foreach ($result as $data) {
            $friend = array();

            $friend['_type'] = 'location';
            $friend['tst'] = $data['epoch'];
            $friend['tid'] = $data['tracker_id'];
            $friend['lat'] = floatval($data['latitude']);
            $friend['lon'] = floatval($data['longitude']);
            $friend['acc'] = $data['accuracy'];
            $friend['alt'] = $data['altitude'];
            $friend['vac'] = $data['vertical_accuracy'];
            $friend['cog'] = $data['heading'];
            $friend['vel'] = $data['velocity'];
            $friend['rad'] = $data['radius'];
            $friend['topic'] = $data['topic'];

            $friends[] = $friend;
        }

        return $friends;
    }

    public function getMarkers(int $time_from, int $time_to, int $min_accuracy = 1000): array
    {
        $sql = 'SELECT * FROM ' . $this->prefix . 'locations WHERE epoch >= ? AND epoch <= ? AND accuracy < ? AND altitude >=0 ORDER BY tracker_id, epoch ASC';
        $result = $this->query($sql, array($time_from, $time_to, $min_accuracy));

        $markers = array();
        foreach ($result as $pr) {
            $markers[$pr['tracker_id']][] = $pr;
        }

        return $markers;
    }

    public function getMarkerLatLon(int $epoch)
    {
        $sql = 'SELECT latitude, longitude FROM ' . $this->prefix . 'locations WHERE epoch = ?';
        $result = $this->query($sql, array($epoch));
        return $result[0];
    }

    public function deleteMarker(int $epoch)
    {
        $sql = 'DELETE FROM ' . $this->prefix . 'locations WHERE epoch = ?';
        $result = $this->execute($sql, array($epoch));
        return $result;
    }

    public function updateLocationData(int $epoch, float $latitude, float $longitude, string $location_name, int $place_id, int $osm_id)
    {
        $sql = 'UPDATE ' . $this->prefix . 'locations SET display_name = ?, place_id = ?, osm_id = ? WHERE epoch = ?';
        $params = array($location_name, $place_id, $osm_id, $epoch);
        $result = $this->execute($sql, $params);
        return $result;
    }
}
