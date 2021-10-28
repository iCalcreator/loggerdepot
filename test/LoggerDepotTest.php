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

use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

class TestLogger extends NullLogger
{
    /**
     * @var string
     */
    private string $message;

    /**
     * Class contructor
     *
     * @param string $message
     */
    public function __construct(
        string $message = ''
    ) {
        $this->message = $message;
    }

    /**
     * Magic __toString function
     */
    public function __toString()
    {
        return $this->message;
    }
}
/**
 * class LoggerDepotTest
 *
 * @author      Kjell-Inge Gustafsson <ical@kigkonsult.se>
 */
class LoggerDepotTest extends TestCase
{
    /**
     *
     */
    protected function tearDown() : void
    {
        foreach( LoggerDepot::getLoggerKeys() as $key ) {
            LoggerDepot::unregisterLogger( $key );
        }
    }

    /**
     * loggerDepoTest1 Provider
     */
    public function loggerDepoTest1Provider() : array
    {
        $data   = [];

        $data[] = [ // test data set #0
            [
                'key1'  => new TestLogger( 'logger 11 key1' ),
                'key2'  => new TestLogger( 'logger 12 key2' ),
            ]
        ];

        return $data;
    }

    /**
     * Test common methods
     *
     * @test
     * @dataProvider loggerDepoTest1Provider
     * @param array $loggerCfg
     */
    public function loggerDepoTest1(
        array $loggerCfg
    ) : void
    {
        foreach( $loggerCfg as $k => $l ) {
            $this->assertFalse( LoggerDepot::isLoggerSet( $k ));
            $this->assertNotNull( LoggerDepot::getLogger( $k )); // NullLogger is set
            $this->assertFalse( LoggerDepot::setFallbackLoggerKey( $k ));

            LoggerDepot::registerLogger( $k, $l );
            $this->assertTrue( LoggerDepot::isLoggerSet( $k ));
            $this->assertEquals( $l, (string) LoggerDepot::getLogger( $k ));

            LoggerDepot::unregisterLogger( $k );
            $this->assertFalse( LoggerDepot::isLoggerSet( $k ));
            LoggerDepot::registerLogger( $k, $l, true );
            $this->assertEquals( $k, LoggerDepot::getFallbackLoggerKey());

            LoggerDepot::unregisterLogger( $k );
            $this->assertFalse( LoggerDepot::isLoggerSet( $k ));
            $this->assertNotNull( LoggerDepot::getLogger( $k )); // NullLogger is set
        }

    }

    /**
     * loggerDepotTest2 Provider
     */
    public function loggerDepotTest2Provider() : array
    {
        $loggers = [
            'Foo'           => new TestLogger( 'Foo namespace logger' ),
            'Foo\\Zoo'      => new TestLogger( 'Foo\\Zoo namespace logger' ),
            'Foo\\Bar\\Baz' => new TestLogger( 'Foo\\Bar\\Baz namespace logger' ),
            'Bar'           => new TestLogger( 'Bar namespace logger' ),
            'Bar\\Foo'      => new TestLogger( 'Bar\\Foo namespace logger' ),
            'Bar\\Foo\\Baz' => new TestLogger( 'Bar\\Foo\\Baz namespace logger' ),
            'error'         => new TestLogger( 'error logger' ),
        ];
        $data    = [];

        $data[] = [ // test data set #0
            $loggers,
            'Foo\\Bar\\Baz namespace logger',
            'Foo\\Bar\\Baz\\Frz'
        ];

        $data[] = [ // test data set #1
            $loggers,
            'Bar namespace logger',
            'Bar\\Baz\\Foo'
        ];

        $data[] = [ // test data set #2
            $loggers,
            'Foo namespace logger',
            'Frz\\Bar\\Foo\\Baz'
        ];

        return $data;
    }

    /**
     * Test name search
     *
     * @test
     * @dataProvider loggerDepotTest2Provider
     * @param array  $loggerCfg,
     * @param string $expected
     * @param string $class
     */
    public function loggerDepotTest2(
        array $loggerCfg,
        string $expected,
        string $class
    ) : void
    {
        foreach( $loggerCfg as $k => $l ) {
            LoggerDepot::registerLogger( $k, $l );
        }
        $this->assertEquals( $expected, (string) LoggerDepot::getLogger( $class ));

    }

    /**
     * loggerDepotTest3 Provider
     */
    public function loggerDepotTest3Provider() : array
    {
        $loggers = [
            'Foo'           => new TestLogger( 'Foo namespace logger' ),
            'Foo\\Zoo'      => new TestLogger( 'Foo\\Zoo namespace logger' ),
            'Foo\\Bar\\Baz' => new TestLogger( 'Foo\\Bar\\Baz namespace logger' ),
            'Bar'           => new TestLogger( 'Bar namespace logger' ),
            'Bar\\Foo'      => new TestLogger( 'Bar\\Foo namespace logger' ),
            'Bar\\Foo\\Baz' => new TestLogger( 'Bar\\Foo\\Baz namespace logger' ),
            'error'         => new TestLogger( 'error logger' ),
        ];
        $data    = [];

        $data[] = [ // test data set #0
            $loggers,
            'error logger',
            'Foo\\Zoo namespace logger',
            'Frz\\Foo\\Bar\\Baz'
        ];

        return $data;
    }

    /**
     * Test search and get fallback
     *
     * @test
     * @dataProvider loggerDepotTest3Provider
     * @param array  $loggerCfg,
     * @param string $expected1
     * @param string $expected2
     * @param string $class
     */
    public function loggerDepotTest3(
        array $loggerCfg,
        string $expected1,
        string $expected2,
        string $class
    ) : void
    {
        $k = $k1 = null;
        foreach( $loggerCfg as $k => $l ) {
            if( empty( $k1 )) {
                $k1 = $k;
            }
            LoggerDepot::registerLogger( $k, $l );
        }
        $this->asserttrue( LoggerDepot::setFallbackLoggerKey( $k ));
        $this->assertEquals( $expected1, LoggerDepot::getLogger( $class )); // last

        LoggerDepot::unregisterLogger( $k1 ); // remove first

        LoggerDepot::unregisterLogger( $k ); // remove last
        $this->assertEquals( $expected2, LoggerDepot::getLogger( $class )); // #2

    }

    /**
     * loggerDepotTest4 Provider
     */
    public function loggerDepotTest4Provider() : array
    {
        $data   = [];
        $loggers = [
            'Foo'           => new TestLogger( 'Foo namespace logger' ),
            'Foo\\Zoo'      => new TestLogger( 'Foo\\Zoo namespace logger' ),
            'Foo\\Bar\\Baz' => new TestLogger( 'Foo\\Bar\\Baz namespace logger' ),
            'Kigkonsult'    => new TestLogger( 'Kigkonsult namespace logger' ),
            'Bar'           => new TestLogger( 'Bar namespace logger' ),
            'Bar\\Foo'      => new TestLogger( 'Bar\\Foo namespace logger' ),
            'Bar\\Foo\\Baz' => new TestLogger( 'Bar\\Foo\\Baz namespace logger' ),
            'error'         => new TestLogger( 'error logger' ),
        ];

        $data[] = [ // test data set #0
            $loggers,
            LoggerDepot::class,
            'Kigkonsult namespace logger'
        ];

        $data[] = [ // test data set #1
            $loggers,
            'error',
            'error logger'
        ];

        $data[] = [ // test data set #2
            $loggers,
            'Baz\\Baz\\Baz',
            'Foo namespace logger'
        ];

        return $data;
    }

    /**
     * Test multiple loggers
     *
     * @test
     * @dataProvider loggerDepotTest4Provider
     * @param array $loggerCfg
     * @param string $key
     * @param string $expected
     */
    public function loggerDepotTest4(
        array  $loggerCfg,
        string $key,
        string $expected
    ) : void
    {
        foreach( $loggerCfg as $k => $l ) {
            LoggerDepot::registerLogger( $k, $l );
        }
        $this->assertEquals( $expected, LoggerDepot::getLogger( $key ));
    }

    /**
     * loggerDepotTest5 Provider
     */
    public function loggerDepotTest5Provider() : array
    {
        $NullLogger = new NullLogger();
        $TestLogger = new TestLogger();
        $data       = [];

        $loggers = [
            LogLevel::DEBUG  => $NullLogger,
            LogLevel::ERROR  => $TestLogger,
        ];

        $data[] = [ // test data set #0
            $loggers,
            LogLevel::DEBUG,
            get_class( $NullLogger )
        ];

        $data[] = [ // test data set #1
            $loggers,
            LogLevel::ERROR,
            get_class( $TestLogger )
        ];

        $data[] = [ // test data set #2
            [],
            LogLevel::DEBUG,
            get_class( $NullLogger )
        ];

        $data[] = [ // test data set #3
            [],
            LogLevel::ERROR,
            get_class( $NullLogger )
        ];

        return $data;
    }

    /**
     * Test Psr\Log
     *
     * @test
     * @dataProvider loggerDepotTest5Provider
     * @param array $loggerCfg
     * @param string $key
     * @param string $expected
     */
    public function loggerDepotTest5(
        array  $loggerCfg,
        string $key,
        string $expected
    ) : void
    {
        foreach( $loggerCfg as $k => $l ) {
            LoggerDepot::registerLogger( $k, $l );
        }
        $this->assertEquals( $expected, get_class( LoggerDepot::getLogger( $key )));
    }

}
