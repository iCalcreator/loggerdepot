LoggerDepot

is a depot for PHP application/software loggers
    making loggers available on demand.

Each logger is identified by a unique and fixed (type case-sensitive string )
key and set and retrieved using the key.

You can use namespace as key (ex `__NAMESPACE__`) setting up a logger and
invoke the logger using (qualified namespaced) class names (ex `get_class()`)
in the namespace tree.

It is possible to combine fixed key and 'namespace' loggers in the depot.

You may also use different keys for the same logger as well as set a logger as
a fallback logger.

Invoking of a logger is as easy as `LoggerDepot::getLogger( <key> )`.
A NullLogger is return is no logger is set.

The construction makes it possible to supervise loggers for separate parts
(functions, modules, components etc) of your software.


USAGE

With <logger> (below) means any PHP logger and corresponding config.

- logger set up

~~~~~~
<?php
namespace Kigkonsult\Example;
use Kigkonsult\LoggerDepot\LoggerDepot;

LoggerDepot::registerLogger(
    __NAMESPACE__, // key
    new <logger>( <logConfig> )
);
~~~~~~

- usage in class

~~~~~~
<?php
namespace Kigkonsult\Example;
use Kigkonsult\LoggerDepot\LoggerDepot;

class LoggerUserClass1
{
    private $logger = null;

    public function __construct() {
        $this->logger = LoggerDepot::getLogger( __CLASS__ );
        $this->logger->debug( 'Start ' . __CLASS__ );
    }

    public function aMethod( $argument ) {
        $this->logger->debug( 'Start ' . __METHOD__ );
        $this->logger->info( 'aMethod argument IN : ' . $argument );
    }
}

class LoggerUserClass2
{
    public static function bMethod( $argument ) {
        LoggerDepot::getLogger( __CLASS__ )->debug( 'Start ' . __METHOD__ );
    }
}
~~~~~~

###### Register loggers

- Set up a (string) keyed logger:

~~~~~~
<?php
use Kigkonsult\LoggerDepot\LoggerDepot;

LoggerDepot::registerLogger( <key>, new <logger>( <logConfig> ));
~~~~~~

Note, register using the same key again will replace existing logger.

- Or check first if logger is set:

~~~~~~
<?php
use Kigkonsult\LoggerDepot\LoggerDepot;

if( ! LoggerDepot::isLoggerSet( <key> )) {
    LoggerDepot::registerLogger( <key>, new <logger>( <logConfig> ));
}
~~~~~~

###### getLogger usage 1

- Get a (string) keyed logger, on demand:

~~~~~~
<?php
use Kigkonsult\LoggerDepot\LoggerDepot;

$logger = LoggerDepot::getLogger( <key> );
~~~~~~

- or a one-liner (ex. for a Psr\Log logger):

~~~~~~
<?php
use Kigkonsult\LoggerDepot\LoggerDepot;

LoggerDepot::getLogger( <key> )->error( 'Error message' );
~~~~~~

###### getLogger usage 2

The search of requested logger is performed in logger (set-)order.

- Set up a 'namespace' logger in top of a 'namespace' tree:

~~~~~~
<?php
namespace Kigkonsult\Example;
use Kigkonsult\LoggerDepot\LoggerDepot;

LoggerDepot::registerLogger( __NAMESPACE__, new <logger>( <logConfig> ));
~~~~~~

- Get a 'namespace' logger in a class in the same/sub-level 'namespace' tree:

~~~~~~
<?php
namespace Kigkonsult\Example\Impl;
use Kigkonsult\LoggerDepot\LoggerDepot;

class LoggerUserClass3
{
    public function aMethod( $argument ) {
        $logger = LoggerDepot::getLogger( get_class());
        ...
        $logger->info( 'aMethod argument IN : ' . $argument );
    }
}
~~~~~~

- or a one-liner (ex. for a Psr\Log logger):

~~~~~~
<?php
namespace Kigkonsult\Example\Impl;
use Kigkonsult\LoggerDepot\LoggerDepot;

class LoggerUserClass4
{
    public function aMethod( $argument ) {
        LoggerDepot::getLogger( get_class())->error( 'Error message' );
    }
}
~~~~~~

###### fallback

- Set up a fallback logger to use in case requested logger is not found:

~~~~~~
<?php
use Kigkonsult\LoggerDepot\LoggerDepot;

LoggerDepot::registerLogger( <key>, new <logger>( <logConfig> ));
LoggerDepot::setFallbackLoggerKey( <key> );
~~~~~~

Note, `LoggerDepot::setFallbackLoggerKey()` return `false` if key (for logger) is not set.

- or shorter

~~~~~~
<?php
use Kigkonsult\LoggerDepot\LoggerDepot;

LoggerDepot::registerLogger( <key>, new <logger>( <logConfig> ), true );
~~~~~~

The first logger is always set as fallback until specific logger is set.
Hence, a single logger will also serve as fallback.

- Fetch key for the fallback logger:

~~~~~~
<?php
use Kigkonsult\LoggerDepot\LoggerDepot;

$key = LoggerDepot::getFallbackLoggerKey();
~~~~~~


###### Misc

- Fetch (array) all keys for all loggers:

~~~~~~
<?php
use Kigkonsult\LoggerDepot\LoggerDepot;

$keys = LoggerDepot::getLoggerKeys();
~~~~~~

- Remove a specific logger:

~~~~~~
<?php
use Kigkonsult\LoggerDepot\LoggerDepot;

LoggerDepot::unregisterLogger( <key> );
~~~~~~

Caveat, removing the fallback logger will force 'the next' (in order) to take over.

- And (in the end?) remove all:

~~~~~~
<?php
use Kigkonsult\LoggerDepot\LoggerDepot;

foreach( LoggerDepot::getLoggerKeys() as $key ) {
    LoggerDepot::unregisterLogger( $key );
}
~~~~~~


INSTALL

Composer (https://getcomposer.org/), from the Command Line:

composer require kigkonsult/loggerdepot:dev-master

Composer, in your `composer.json`:

{
    "require": {
        "kigkonsult/loggerdepot": "dev-master"
    }
}


Composer, acquire access

<?php
use Kigkonsult\LoggerDepot\LoggerDepot;
...
include 'vendor/autoload.php';



Otherwise , download and acquire..

<?php
use Kigkonsult\LoggerDepot\LoggerDepot;
...
include 'pathToSource/loggerdepot/autoload.php';



Copyright (c) 2019-2020 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
Link      https://kigkonsult.se
Package   LoggerDepot
Version   1.03
License   Subject matter of licence is the software LoggerDepot.
          The above copyright, link, package and version notices and
          this licence notice shall be included in all copies or
          substantial portions of the LoggerDepot.

          LoggerDepot is free software: you can redistribute it and/or modify
          it under the terms of the GNU Lesser General Public License as published
          by the Free Software Foundation, either version 3 of the License,
          or (at your option) any later version.

          LoggerDepot is distributed in the hope that it will be useful,
          but WITHOUT ANY WARRANTY; without even the implied warranty of
          MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
          GNU Lesser General Public License for more details.

          You should have received a copy of the GNU Lesser General Public License
          along with LoggerDepot. If not, see <https://www.gnu.org/licenses/>.
