<?php
/**
 * LoggerDepot is a depot for PHP application/software loggers, making loggers available on demand.
 *
 * This file is part of loggerDepot.
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @copyright 2019-2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * @link      https://kigkonsult.se
 * @license   Subject matter of licence is the software loggerDepot.
 *            The above copyright, link, package and version notices,
 *            this licence notice shall be included in all copies or substantial
 *            portions of the loggerDepot.
 *
 *            loggerDepot is free software: you can redistribute it and/or modify
 *            it under the terms of the GNU Lesser General Public License as
 *            published by the Free Software Foundation, either version 3 of
 *            the License, or (at your option) any later version.
 *
 *            loggerDepot is distributed in the hope that it will be useful,
 *            but WITHOUT ANY WARRANTY; without even the implied warranty of
 *            MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *            GNU Lesser General Public License for more details.
 *
 *            You should have received a copy of the GNU Lesser General Public License
 *            along with loggerDepot. If not, see <https://www.gnu.org/licenses/>.
 */
declare( strict_types = 1 );
namespace Kigkonsult\LoggerDepot;

use Psr\Log\NullLogger;

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
    private static array $depot    = [];

    /**
     * @var null|string  Key for fallback logger
     * @access private
     * @static
     */
    private static ?string $fallbackKey = null;

    /**
     * @var string
     * @access private
     * @static
     */
    private static string $BS = '\\';

    /**
     * Return marshalled key
     *
     * @param string $key
     * @return string
     * @access private
     * @static
     */
    private static function marshallKey( string $key ) : string
    {
        return serialize( rtrim( $key, self::$BS ));
    }

    /**
     * Set key for (fallback) logger
     *
     * @param string $key
     * @return bool
     * @static
     */
    public static function setFallbackLoggerKey( string $key ) : bool
    {
        if( self::isLoggerSet( $key )) {
            self::$fallbackKey = self::marshallKey( $key );
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
     public static function getFallbackLoggerKey() : string
     {
         return unserialize( self::$fallbackKey  );
     }

    /**
     * Register a new logger with (unique) access key
     *
     * If not already set, logger (key) is set as fallback (logger)
     *
     * @param string $key
     * @param mixed  $logger
     * @param null|bool $isFallback
     * @static
     */
    public static function registerLogger( string $key, $logger, ? bool $isFallback = false ) : void
    {
        $mKey = self::marshallKey( $key );
        self::$depot[$mKey] = $logger;
        if( empty( self::$fallbackKey ) || $isFallback ) {
            self::$fallbackKey = $mKey ;
        }
    }

    /**
     * Unregister logger for key
     *
     * @param string $key
     * @static
     */
    public static function unregisterLogger( string $key ) : void
    {
        $mKey = self::marshallKey( $key );
        if( ! isset( self::$depot[$mKey] )) {
            return;
        }
        $allKeys = array_keys( self::$depot );
        switch( true ) {
            case ( 1 === count( $allKeys )) :
                // remove last ?
                self::$fallbackKey = null;
                break;
            case ( self::$fallbackKey !== $mKey ) :
                // more loggers exists
                break;
            default :
                // replace fallback, next found
                $thisIxs = array_keys( $allKeys, $mKey );
                $nextIx  = end( $thisIxs ) + 1;
                $nextKey = $allKeys[$nextIx] ?? $allKeys[0];
                self::$fallbackKey = $nextKey;
                break;
        }
        unset( self::$depot[$mKey] );
    }

    /**
     * Return Logger keys
     *
     * @return array
     * @static
     */
    public static function getLoggerKeys() : array
    {
        return array_map(
            static function( $k ) { return unserialize( $k ); },
            array_keys( self::$depot )
        );
    }

    /**
     * Return bool true if logger (key) is set
     *
     * @param string $key
     * @return bool
     * @static
     */
    public static function isLoggerSet( string $key ) : bool
    {
        $mKey = self::marshallKey( $key );
        return isset( self::$depot[$mKey] );
    }

    /**
     * Return logger for (traversed) search key, NullLogger if no loggers set
     *
     * @param string $key
     * @return mixed object|null
     * @static
     */
    public static function getLogger( string $key )
    {
        $mKey = self::marshallKey( $key );
        if( isset( self::$depot[$mKey] )) {
            return self::$depot[$mKey];
        }
        $foundKey = self::traverseKey( $key );
        if( false !== $foundKey ) {
            $mKey = self::marshallKey( $foundKey );
            return self::$depot[$mKey];
        }
        if( ! empty( self::$fallbackKey )) {
            return self::$depot[self::$fallbackKey];
        }
        return new NullLogger();
    }

    /**
     * Return logger key for (traversed) search key
     *
     * @param string $key
     * @return string|bool   bool false on error
     * @access private
     * @static
     */
    private static function traverseKey( string $key )
    {
        $keyChain = explode( self::$BS, rtrim( $key, self::$BS ));
        $x        = count( $keyChain ) - 1;
        do {
            $sKey = implode( self::$BS, $keyChain );
            if( self::isLoggerSet( $sKey )) {
                return $sKey;
            }
            unset( $keyChain[$x] );
            --$x;
        } while( $x >= 0 );
        return false;
    }
}
