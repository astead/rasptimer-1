<?php
// Configuration file. Adjust as needed.

// Title of the web page
$title = "Rasptimer";

// The devices being controlled.
// key:   name of the device
// value: wiringPi PIN number, see https://projects.drogon.net/raspberry-pi/wiringpi/pins/
// value2: mutually exclusive big, this is either a 1 or a 0.  If its a 1 it means only one
//         of these gpio pins can be turned on at a time, so all the other mutually exclusive
//         ones will get turned off before this one gets turned on. This is meant to provide
//         some safe guards against accidentally programming the timing incorrectly.

$devices = array(
    "Main pump"    => array(11,1),
    "Sweeper pump" =>  array(6,1)
);

// Where to log events. This file must be writeable by the webserver user, e.g. "chown www-data /var/log/rasptimer.log"
$logFile      = "/var/log/rasptimer.log";

// "Glob" expression that finds all log files, including old ones. The syntax is the same as in a shell:
// * and ? are wildcards.
$logFilesGlob = "/var/log/rasptimer.log*";

// Regular expression that parses file names of log files other than the current one.
$oldLogFilesPattern = "/var/log/rasptimer\.log\.(\d+)(\.gz)?";

// Printf expression that creates candidate file names of log files other than the current one
$oldLogFilesPrintf = array( "/var/log/rasptimer.log.%d", "/var/log/rasptimer.log.%d.gz" );
