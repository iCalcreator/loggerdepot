<?php
/**
 * loggerDepot is a depot for PHP application/software loggers, making loggers available on demand.
 *
 * Copyright (c) 2019 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * Link      https://kigkonsult.se
 * Package   loggerDepot
 * Version   1.02
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
    /**
     * LoggerDepot autoloader
     */
spl_autoload_register(
    function( $class ) {
        static $PREFIX = 'Kigkonsult\\LoggerDepot\\';
        static $BS     = '\\';
        static $FMT    = '%1$s%2$ssrc%2$s%3$s.php';
        if ( 0 != strncmp( $PREFIX, $class, 23 )) {
            return;
        }
        $class = substr( $class, 24 );
        if ( false !== strpos( $class, $BS )) {
            $class = str_replace( $BS, DIRECTORY_SEPARATOR, $class );
        }
        $file = sprintf( $FMT, __DIR__, DIRECTORY_SEPARATOR, $class );
        if ( file_exists( $file )) {
            include $file;
        }
    }
);
