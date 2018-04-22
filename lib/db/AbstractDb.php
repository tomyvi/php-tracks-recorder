<?php

class AbstractDb
{
    protected $db;
    protected $prefix;

    public function isEpochExisting($trackerId, $epoch)
    {
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
    }
 
    public function getMarkers($time_from, $time_to, $min_accuracy = 1000)
    {
    }

    public function getMarkerLatLon($epoch)
    {
    }

    public function deleteMarker($epoch)
    {
    }

    public function updateLocationData($epoch, $latitude, $longitude, $location_name, $place_id, $osm_id)
    {
    }
}
