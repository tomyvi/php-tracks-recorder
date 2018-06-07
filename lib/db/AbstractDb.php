<?php

class AbstractDb
{
    protected $db;
    protected $prefix;

    protected function execute(string $sql, array $params): bool
    {
        // Run query without result
    }

    protected function query(string $sql, array $params): array
    {
        // Run query and fetch results
    }

    public function isEpochExisting(string $trackerId, int $epoch): bool
    {
        $sql = 'SELECT epoch FROM ' . $this->prefix . 'locations WHERE tracker_id = ? AND epoch = ?';
        $result = $this->query($sql, array($trackerId, $epoch));
        return (count($result) > 0);
    }

    public function addLocation(
        int $accuracy = null,
        int $altitude = null,
        int $battery_level = null,
        int $heading = null,
        string $description = null,
        string $event = null,
        float $latitude,
        float $longitude,
        int $radius = null,
        string $trig = null,
        string $tracker_id = null,
        int $epoch,
        int $vertical_accuracy = null,
        int $velocity = null,
        float $pressure = null,
        string $connection = null,
        int $place_id = null,
        int $osm_id = null
    ): bool {
        $sql = 'INSERT INTO ' . $this->prefix . 'locations (accuracy, altitude, battery_level, heading, description, event, latitude, longitude, radius, trig, tracker_id, epoch, vertical_accuracy, velocity, pressure, connection, place_id, osm_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $params = array($accuracy, $altitude, $battery_level, $heading, $description, $event, $latitude, $longitude, $radius, $trig, $tracker_id, $epoch, $vertical_accuracy, $velocity, $pressure, $connection, $place_id, $osm_id);
        $result = $this->execute($sql, $params);
        return $result;
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
