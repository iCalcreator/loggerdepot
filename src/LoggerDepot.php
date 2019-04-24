<?php
/**
 * LoggerDepot is a depot for PHP application/software loggers, making loggers available on demand.
 *
 * Copyright (c) 2019 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * Link      https://kigkonsult.se
 * Package   loggerDepot
 * Version   1.0
 * License   Subject matter of licence is the software loggerDepot.
 *           The above copyright, link, package and version notices and
 *           this licence notice shall be included in all copies or
 *           substantial portions of the loggerDepot.
 *
 *           loggerDepot is free software: you can redistribute it and/or modify
 *           it under the terms of the GNU Lesser General Public License as published
 *           by the Free Software Foundation, either version 3 of the License,
 *           or (at your option) any later version.
 *
 *           loggerDepot is distributed in the hope that it will be useful,
 *           but WITHOUT ANY WARRANTY; without even the implied warranty of
 *           MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *           GNU Lesser General Public License for more details.
 *
 *           You should have received a copy of the GNU Lesser General Public License
 *           along with loggerDepot. If not, see <https://www.gnu.org/licenses/>.
 *
 * This file is part of loggerDepot.
 */
namespace Kigkonsult\LoggerDepot;

use function array_keys;
use function array_map;
use function count;
use function end;
use function explode;
use function implode;
use function rtrim;
use function serialize;
use function unserialize;

class LoggerDepot
{

    /**
     * @var array   Logger depot
     * @access private
     * @static
     */
    private static $depot    = [];

    /**
     * @var string  Key for fallback logger
     * @access private
     * @static
     */
    private static $fallbackKey = null;

    /**
     * @var string
     * @access private
     * @static
     */
    private static $BS = '\\';

    /**
     * Return marshalled key
     *
     * @param string $key
     * @return string
     * @access private
     * @static
     */
    private static function marshallKey( $key ) {
        return serialize( rtrim( $key, LoggerDepot::$BS ));
    }

    /**
     * Set key for (fallback) logger
     *
     * @param string $key
     * @return bool
     * @static
     */
    public static function setFallbackLoggerKey( $key ) {
        if( LoggerDepot::isLoggerSet( $key )) {
            LoggerDepot::$fallbackKey = LoggerDepot::marshallKey( $key );
            return true;
        }
        return false;
    }

    /**
     * Return key for (fallback) logger
     *
     * @return string
     * @static
     */
     public static function getFallbackLoggerKey()
     {
         return unserialize( LoggerDepot::$fallbackKey  );
     }

    /**
     * Register a new logger with (unique) access key
     *
     * If not already set, logger (key) is set as fallback (logger)
     *
     * @param string $key
     * @param mixed  $logger
     * @param bool   $isFallback
     * @static
     */
    public static function registerLogger( $key, $logger, $isFallback = false ) {
        $mKey = LoggerDepot::marshallKey( $key );
        LoggerDepot::$depot[$mKey] = $logger;
        if( empty( LoggerDepot::$fallbackKey ) || $isFallback ) {
            LoggerDepot::$fallbackKey = $mKey ;
        }
    }

    /**
     * Unregister logger for key
     *
     * @param string $key
     * @static
     */
    public static function unregisterLogger( $key ) {
        $mKey = LoggerDepot::marshallKey( $key );
        if( ! isset( LoggerDepot::$depot[$mKey] )) {
            return;
        }
        $allKeys = array_keys( LoggerDepot::$depot );
        switch( true ) {
            case ( 1 == count( $allKeys )) :
                // remove last ?
                LoggerDepot::$fallbackKey = null;
                break;
            case ( LoggerDepot::$fallbackKey != $mKey ) :
                // more loggers exists
                break;
            default :
                // replace fallback, next found
                $thisIxs = array_keys( $allKeys, $mKey );
                $nextIx  = end( $thisIxs ) + 1;
                $nextKey = ( isset( $allKeys[$nextIx] )) ? $allKeys[$nextIx] : $allKeys[0];
                LoggerDepot::$fallbackKey = $nextKey;
                break;
        }
        unset( LoggerDepot::$depot[$mKey] );
    }

    /**
     * Return Logger keys
     *
     * @return array
     * @static
     */
    public static function getLoggerKeys() {
        return array_map( function( $k ) { return unserialize( $k ); }, array_keys( LoggerDepot::$depot ));
    }

    /**
     * Return bool true if logger (key) is set
     *
     * @param string $key
     * @return bool
     * @static
     */
    public static function isLoggerSet( $key ) {
        $mKey = LoggerDepot::marshallKey( $key );
        return isset( LoggerDepot::$depot[$mKey] );
    }

    /**
     * Return logger for (traversed) search key
     *
     * @param string $key
     * @return mixed object|null
     * @static
     */
    public static function getLogger( $key ) {
        $mKey = LoggerDepot::marshallKey( $key );
        if( isset( LoggerDepot::$depot[$mKey] )) {
            return LoggerDepot::$depot[$mKey];
        }
        $foundKey = LoggerDepot::traverseKey( $key );
        if( false !== $foundKey ) {
            $mKey = LoggerDepot::marshallKey( $foundKey );
            return LoggerDepot::$depot[$mKey];
        }
        if( ! empty( LoggerDepot::$fallbackKey )) {
            return LoggerDepot::$depot[LoggerDepot::$fallbackKey];
        }
        return null;
    }

    /**
     * Return logger key for (traversed) search key
     *
     * @param string $key
     * @return string|bool
     * @access private
     * @static
     */
    private static function traverseKey( $key ) {
        $keyChain = explode( LoggerDepot::$BS, rtrim( $key, LoggerDepot::$BS ));
        $x        = count( $keyChain ) - 1;
        do {
            $sKey = implode( LoggerDepot::$BS, $keyChain );
            if( LoggerDepot::isLoggerSet( $sKey )) {
                return $sKey;
                break;
            }
            unset( $keyChain[$x] );
            $x -= 1;
        } while( $x >= 0 );
        return false;
    }

}