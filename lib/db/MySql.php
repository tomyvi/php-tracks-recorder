<?php

require_once(__DIR__ . '/AbstractDb.php');

class MySql extends AbstractDb
{
    public function __construct($db, $hostname = null, $username = null, $password = null, $prefix = '')
    {
        $this->db = new \mysqli($hostname, $username, $password, $db);
        $this->prefix = $prefix;
    }

    public function isEpochExisting($trackerId, $epoch)
    {
        $sql = 'SELECT epoch FROM ' . $this->prefix . 'locations WHERE tracker_id = ? AND epoch = ?';
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('si', $trackerId, $epoch);
        $stmt->execute();
        $stmt->store_result();

        return ($stmt->num_rows > 0);
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
        $stmt->bind_param('iiiissddissiiidsii', $accuracy, $altitude, $battery_level, $heading, $description, $event, $latitude, $longitude, $radius, $trig, $tracker_id, $epoch, $vertical_accuracy, $velocity, $pressure, $connection, $place_id, $osm_id);

        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    public function getMarkers($time_from, $time_to, $min_accuracy = 1000)
    {
        $sql = 'SELECT * FROM ' . $this->prefix . 'locations WHERE epoch >= ? AND epoch <= ? AND accuracy < ? AND altitude >=0 ORDER BY tracker_id, epoch ASC';
        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('iii', $time_from, $time_to, $min_accuracy);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->store_result();
        
        $markers = array();
        while ($data = $result->fetch_assoc()) {
            // Loop through results here $data[]
            $markers[$data['tracker_id']][] = $data;
        }

        $stmt->close();
        return $markers;
    }

    public function getMarkerLatLon($epoch)
    {
        $sql = 'SELECT latitude, longitude FROM ' . $this->prefix . 'locations WHERE epoch = ?';
        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('i', $epoch);

        if (!$stmt->execute()) {
            return false;
        }

        $result = $stmt->get_result();
        $stmt->store_result();
        
        while ($data = $result->fetch_assoc()) {
            // Loop through results here $data[]
            $marker = $data;
        }

        return $marker;
    }

    public function deleteMarker($epoch)
    {
        $sql = 'DELETE FROM ' . $this->prefix . 'locations WHERE epoch = ?';
        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('i', $epoch);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    public function updateLocationData($epoch, $latitude, $longitude, $location_name, $place_id, $osm_id)
    {
        $sql = 'UPDATE ' . $this->prefix . 'locations SET display_name = ?, place_id = ?, osm_id = ? WHERE epoch = ? AND latitude = ? AND longitude = ?';
        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('siiidd', $location_name, $place_id, $osm_id, $epoch, $latitude, $longitude);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }
}
