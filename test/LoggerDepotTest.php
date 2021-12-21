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
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

/**
 * Logger class (#1)
 */
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
 * Logger class (#2)
 */
class TestLogger2 implements LoggerInterface
{
    /**
     * The logger file
     *
     * @var string
     */
    private $loggerFile;

    /**
     * @param string $loggerFile
     * @return TestLogger2
     */
    public static function factory( string $loggerFile ) : TestLogger2
    {
        $instance = new self();
        $instance->setLoggerFile( $loggerFile );
        return $instance;
    }

    /**
     * Sets a logger file
     *
     * @param string $loggerFile
     * @return TestLogger2
     */
    public function setLoggerFile( string $loggerFile ) : TestLogger2
    {
        $this->loggerFile = $loggerFile;
        return $this;
    }

    use LoggerTrait;

    /**
     * @inheritDoc
     */
    public function log( $level, $message, array $context = array())
    {
        file_put_contents( $this->loggerFile, $message . ' with logLevel : ' . $level . PHP_EOL, FILE_APPEND );
    }
}

/**
 * class LoggerDepotTest
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
     *
     * @return array
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
    public function loggerDepoTest1( array $loggerCfg ) : void
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

            /* test removal of unknown logger, affecs nothing but the test covers code */
            $cntLoggers = count( LoggerDepot::getLoggerKeys());
            LoggerDepot::unregisterLogger( 'unknown' );
            $this->assertCount( $cntLoggers, LoggerDepot::getLoggerKeys());

            LoggerDepot::unregisterLogger( $k );
            $this->assertFalse( LoggerDepot::isLoggerSet( $k ));
            $this->assertNotNull( LoggerDepot::getLogger( $k )); // NullLogger is set
        }

    }

    /**
     * loggerDepotTest2 Provider
     *
     * @return array
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
    public function loggerDepotTest2( array $loggerCfg, string $expected, string $class ) : void
    {
        foreach( $loggerCfg as $k => $l ) {
            LoggerDepot::registerLogger( $k, $l );
        }
        $this->assertEquals( $expected, (string) LoggerDepot::getLogger( $class ));

    }

    /**
     * loggerDepotTest3 Provider
     *
     * @return array
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
    public function loggerDepotTest3( array $loggerCfg, string $expected1, string $expected2, string $class ) : void
    {
        $k = $k1 = '';
        foreach( $loggerCfg as $k => $l ) {
            if( empty( $k1 )) {
                $k1 = (string) $k;
            }
            LoggerDepot::registerLogger( $k, $l );
        }
        $this->asserttrue( LoggerDepot::setFallbackLoggerKey((string) $k ));
        $this->assertEquals( $expected1, LoggerDepot::getLogger( $class )); // last

        LoggerDepot::unregisterLogger( $k1 ); // remove first

        LoggerDepot::unregisterLogger( $k ); // remove last
        $this->assertEquals( $expected2, LoggerDepot::getLogger( $class )); // #2

    }

    /**
     * loggerDepotTest4 Provider
     *
     * @return array
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
     * Test multiple (single) loggers, aggregated loggers in loggerDepotTest6, below
     *
     * @test
     * @dataProvider loggerDepotTest4Provider
     * @param array $loggerCfg
     * @param string $key
     * @param string $expected
     */
    public function loggerDepotTest4( array  $loggerCfg, string $key, string $expected ) : void
    {
        foreach( $loggerCfg as $k => $l ) {
            LoggerDepot::registerLogger( $k, $l );
        }
        $this->assertEquals( $expected, LoggerDepot::getLogger( $key ));
    }

    /**
     * loggerDepotTest5 Provider
     *
     * @return array
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
    public function loggerDepotTest5( array  $loggerCfg, string $key, string $expected ) : void
    {
        foreach( $loggerCfg as $k => $l ) {
            LoggerDepot::registerLogger( $k, $l );
        }
        $this->assertEquals( $expected, get_class( LoggerDepot::getLogger( $key )));
    }

    /**
     * Test PsrLogAggregate, aggregated loggers, log to one but affect multiple
     *
     * @test
     */
    public function loggerDepotTest6() : void
    {
        $loggerFile1 = tempnam( sys_get_temp_dir(), 'im1' );
        $logger1     = TestLogger2::factory( $loggerFile1 );
        $loggerFile2 = tempnam( sys_get_temp_dir(), 'im2' );
        $logger2     = TestLogger2::factory( $loggerFile2 );

        $psrLogAggregate = PsrLogAggregate::factory( [ $logger1, $logger2 ] );

        $logArr      = $psrLogAggregate->getLoggers();
        $this->assertCount(
            2,
            $logArr
        );
        $this->assertNotSame(
            $logArr[0],
            $logArr[1]
        );

        LoggerDepot::registerLogger( __NAMESPACE__, $psrLogAggregate );

        $testLogger = LoggerDepot::getLogger( __NAMESPACE__ );
        $logTestMsg = 'This is a %s message (#';
        foreach(
            [
                LogLevel::EMERGENCY,
                LogLevel::ALERT,
                LogLevel::CRITICAL,
                LogLevel::ERROR,
                LogLevel::WARNING,
                LogLevel::NOTICE,
                LogLevel::INFO,
                LogLevel::DEBUG
            ]
            as $logLevel ) {
            $actMsg = sprintf( $logTestMsg, strtoupper( $logLevel ));
            $testLogger->{$logLevel}( $actMsg . '1, using method ' . $logLevel . ')' );
            $testLogger->log( $logLevel, $actMsg . '2, using method log)' );
        }

//      echo 'loggerFile1 contents : ' . PHP_EOL . file_get_contents( $loggerFile1 ) . PHP_EOL;// test ###
//      echo 'loggerFile2 contents : ' . PHP_EOL . file_get_contents( $loggerFile2 ) . PHP_EOL;// test ###

        $this->assertFileEquals(
            $loggerFile1,
            $loggerFile1
        );

        unlink( $loggerFile1 );
        unlink( $loggerFile2 );
    }
}
