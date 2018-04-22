PRAGMA journal_mode=WAL;

CREATE TABLE "locations" (
  "dt" INTEGER NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "accuracy" INTEGER,
  "altitude" INTEGER,
  "battery_level" INTEGER,
  "heading" INTEGER,
  "description" TEXT,
  "event" TEXT,
  "latitude" REAL,
  "longitude" REAL,
  "radius" INTEGER,
  "trig" INTEGER,
  "tracker_id" TEXT,
  "epoch" INTEGER,
  "vertical_accuracy" INTEGER,
  "velocity" INTEGER,
  "pressure" REAL,
  "connection" TEXT,
  "place_id" INTEGER,
  "osm_id" INTEGER,
  "display_name" TEXT
);
