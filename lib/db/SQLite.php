<?php

require_once(__DIR__ . '/AbstractDb.php');

class SQLite extends AbstractDb
{
    public function __construct($db, $hostname = null, $username = null, $password = null, $prefix = '')
    {
        $this->db = new \PDO('sqlite:' . $db);
        $this->prefix = '';
    }

    public function isEpochExisting($trackerId, $epoch)
    {
        $sql = 'SELECT epoch FROM ' . $this->prefix . 'locations WHERE tracker_id = ? AND epoch = ?';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array($trackerId, $epoch));

        return (count($stmt->fetchColumn(0)) > 0);
    }

    public function addLocation(
        $accuracy,
        $altitude,
        $battery_level,
        $heading,
        $description,
        $event,
        $latitude,
        $longitude,
        $radius,
        $trig,
        $tracker_id,
        $epoch,
        $vertical_accuracy,
        $velocity,
        $pressure,
        $connection,
        $place_id,
        $osm_id
    ) {
        $sql = 'INSERT INTO ' . $this->prefix . 'locations (accuracy, altitude, battery_level, heading, description, event, latitude, longitude, radius, trig, tracker_id, epoch, vertical_accuracy, velocity, pressure, connection, place_id, osm_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute(array($accuracy, $altitude, $battery_level, $heading, $description, $event, $latitude, $longitude, $radius, $trig, $tracker_id, $epoch, $vertical_accuracy, $velocity, $pressure, $connection, $place_id, $osm_id));
        $stmt->closeCursor();

        return $result;
    }

    public function getMarkers($time_from, $time_to, $min_accuracy = 1000)
    {
        $sql = 'SELECT * FROM ' . $this->prefix . 'locations WHERE epoch >= ? AND epoch <= ? AND accuracy < ? AND altitude >=0 ORDER BY tracker_id, epoch ASC';
        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->execute(array($time_from, $time_to, $min_accuracy));
        
        $markers = array();
        while ($data = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            // Loop through results here $data[]
            $markers[$data['tracker_id']][] = $data;
        }

        $stmt->closeCursor();
        return $markers;
    }

    public function getMarkerLatLon($epoch)
    {
        $sql = 'SELECT latitude, longitude FROM ' . $this->prefix . 'locations WHERE epoch = ?';
        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            return false;
        }

        if (!$stmt->execute(array($epoch))) {
            return false;
        }

        while ($data = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            // Loop through results here $data[]
            $marker = $data;
        }

        $stmt->closeCursor();
        return $marker;
    }

    public function deleteMarker($epoch)
    {
        $sql = 'DELETE FROM ' . $this->prefix . 'locations WHERE epoch = ?';
        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $result = $stmt->execute(array($epoch));
        $stmt->closeCursor();

        return $result;
    }

    public function updateLocationData($epoch, $latitude, $longitude, $location_name, $place_id, $osm_id)
    {
        $sql = 'UPDATE ' . $this->prefix . 'locations SET display_name = ?, place_id = ?, osm_id = ? WHERE epoch = ? AND latitude = ? AND longitude = ?';
        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $result = $stmt->execute(array($location_name, $place_id, $osm_id, $epoch, $latitude, $longitude));
        $stmt->closeCursor();

        return $result;
    }
}
