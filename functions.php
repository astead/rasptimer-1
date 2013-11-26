<?php
function logEvent( $pin, $event ) {
    global $logFile;

    if( isset( $logFile )) {
        $fh = @fopen( $logFile, "a" );
        if( isset( $fh )) {
            fprintf( $fh, "%s\t%s\t%s\n", strftime( "%Y-%m-%d %H:%M:%S" ), $pin, $event );
            fclose( $fh );
        }
    }
}

function runGpio( $cmd, $pin, $args = '' ) {
    if( $cmd == 'write' ) {
        logEvent( $pin, $args );
    }
    exec( "/usr/local/bin/gpio mode $pin out", $out, $status );
    $status = NULL;
    $out    = NULL;
    exec( "/usr/local/bin/gpio $cmd $pin $args", $out, $status );
    if( $status ) {
        print( "<p class='error'>Failed to execute /usr/local/bin/gpio $cmd $pin $args: Status $status</p>\n" );
    }
    if( is_array( $out ) && count( $out ) > 0  ) {
        return $out[0];
    } else {
        return NULL;
    }
}

function issueAt( $deviceName, $minFromNow, $onOff ) {
    global $devices;

    $script = $_SERVER['SCRIPT_FILENAME'];
    $script = substr( $script, 0, strrpos( $script, "/" )) . "/at-run.php";

    $devicePin = $devices[$deviceName];

    exec( "echo /usr/bin/php $script $devicePin[0] $onOff | /usr/bin/at 'now + $minFromNow min'" );
}

function readCrontab() {
    global $devices;

    exec( "/usr/bin/crontab -l", $out, $status );
    # ignore status; it returns 1 if no crontab has been set yet

    $ret = array();
    foreach( $out as $line ) {
        if( preg_match( '!^(\d+) (\d+) .*/cron-run\.php (\d+) ([01])$!', $line, $matches )) {
            foreach( $devices as $deviceName => $devicePin ) {
                if( $devicePin[0] != $matches[3] ) {
                    continue;
                }
                if( $matches[4] == 1 ) {
                    $ret[$deviceName]['timeOn']['hour'] = $matches[2];
                    $ret[$deviceName]['timeOn']['min']  = $matches[1];
                } else {
                    # we write the on's before the off's, so it's here
                    $ret[$deviceName]['duration']['hour'] = $matches[2] - $ret[$deviceName]['timeOn']['hour'];
                    $ret[$deviceName]['duration']['min']  = $matches[1] - $ret[$deviceName]['timeOn']['min'];
                    while( $ret[$deviceName]['duration']['min'] < 0 ) {
                        $ret[$deviceName]['duration']['min'] += 60;
                        $ret[$deviceName]['duration']['hour']--;
                    }
                }
            }
        }
    }
    return $ret;
}

function writeCrontab( $data ) {
    global $devices;

    $script = $_SERVER['SCRIPT_FILENAME'];
    $script = substr( $script, 0, strrpos( $script, "/" )) . "/cron-run.php";

    $file = <<<END
# Crontab automatically generated by rasptimer.
# Do not make manual changes, they will be overwritten.
# See https://github.com/jernst/rasptimer

END;
    foreach( $devices as $deviceName => $devicePin ) {
        if( !isset( $data[$deviceName] )) {
            continue;
        }
        $p = $data[$deviceName];
        if( isset( $p['timeOn'] )) {
            if( isset( $p['timeOn']['hour'] )) {
                $hourOn = $p['timeOn']['hour'];
            } else {
                $hourOn = 0;
            }
            $minOn  = $p['timeOn']['min'];
            if( isset( $p['duration'] )) {
                if( isset( $p['duration']['hour'] )) {
                    $hourOff = $hourOn + $p['duration']['hour'];
                } else {
                    $hourOff = $hourOn + 1; # 1hr default
                }
                if( isset( $p['duration']['min'] )) {
                    $minOff = $minOn + $p['duration']['min'];
                } else {
                    $minOff = $minOn;
                }
            }
            while( $minOff > 59 ) {
                $minOff = $minOff - 60;
                $hourOff++;
            }
            $hourOff = $hourOff % 24; # runs daily

            $file .= "$minOn $hourOn * * * /usr/bin/php $script $devicePin[0] 1\n";
            $file .= "$minOff $hourOff * * * /usr/bin/php $script $devicePin[0] 0\n";
        }
    } 
    $tmp       = tempnam( '/tmp', 'rasptimer' );
    $tmpHandle = fopen( $tmp, "w" );
    fwrite( $tmpHandle, $file );
    fclose( $tmpHandle );

    exec( "/usr/bin/crontab $tmp" );

    unlink( $tmp );
}

function parseLogLine( $line ) {
    // 2013-01-13 07:00:01     11      1
    if( preg_match( '!^(\d+)-(\d+)-(\d+) (\d+):(\d+):(\d+).*\t([^\t]*)\t([^\t]*)$!', $line, $matches )) {
        return $matches;
    } else {
        return NULL;
    }
}

function printLogFileLines( $url, $page ) {
    global $logFile;
    global $logFilesGlob;
    global $oldLogFilesPattern;

    $logFiles = glob( $logFilesGlob );
    if( count( $logFiles ) > 1  ) {
        print "<ul class=\"log-files\">\n";

        for( $i=0 ; $i<count( $logFiles ) ; ++$i ) {
            if( $logFile == $logFiles[$i] ) {
                $selected = !isset( $page ) ? " class=\"selected\"" : "";
                print "<li$selected><a href=\"$url\">Current</a></li>\n";

            } elseif( preg_match( "#$oldLogFilesPattern#", $logFiles[$i], $matches )) {
                $selected = ( $page == $matches[1] ) ? " class=\"selected\"" : "";
                print "<li$selected><a href=\"$url?page=$matches[1]\">$matches[1]</a></li>\n";
            }
        }
        print "</ul>\n";
    }
}
