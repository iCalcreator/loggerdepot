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

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class PsrLogAggregate implements LoggerInterface
{
    /**
     * @var LoggerInterface[]
     */
    private array $loggers = [];

    /**
     * @param null|LoggerInterface[] $loggers
     */
    public function __construct( ? array $loggers )
    {
        if( ! empty( $loggers )) {
            $this->setLoggers( $loggers );
        }
    }

    /**
     * @param null|LoggerInterface[] $loggers
     * @return PsrLogAggregate
     */
    public static function factory( ? array $loggers ) : PsrLogAggregate
    {
        return new self( $loggers );
    }

    /**
     * @inheritDoc
     */
    public function emergency( $message, array $context = array())
    {
        $this->log( LogLevel::EMERGENCY, $message, $context );
    }

    /**
     * @inheritDoc
     */
    public function alert( $message, array $context = array())
    {
        $this->log( LogLevel::ALERT, $message, $context );
    }

    /**
     * @inheritDoc
     */
    public function critical( $message, array $context = array())
    {
        $this->log( LogLevel::CRITICAL, $message, $context );
    }

    /**
     * @inheritDoc
     */
    public function error( $message, array $context = array())
    {
        $this->log( LogLevel::ERROR, $message, $context );
    }

    /**
     * @inheritDoc
     */
    public function warning( $message, array $context = array())
    {
        $this->log( LogLevel::WARNING, $message, $context );
    }

    /**
     * @inheritDoc
     */
    public function notice( $message, array $context = array())
    {
        $this->log( LogLevel::NOTICE, $message, $context );
    }

    /**
     * @inheritDoc
     */
    public function info( $message, array $context = array())
    {
        $this->log( LogLevel::INFO, $message, $context );
    }

    /**
     * @inheritDoc
     */
    public function debug( $message, array $context = array())
    {
        $this->log( LogLevel::DEBUG, $message, $context );
    }

    /**
     * @inheritDoc
     */
    public function log( $level, $message, array $context = array())
    {
        foreach( $this->loggers as $logger ) {
            $logger->log( $level, $message, $context );
        }
    }

    /**
     * @return LoggerInterface[]
     */
    public function getLoggers() : array
    {
        return $this->loggers;
    }

    /**
     * Add single LoggerInterface logger
     *
     * @param LoggerInterface $logger
     * @return PsrLogAggregate
     */
    public function addLogger( LoggerInterface $logger ) : PsrLogAggregate
    {
        $this->loggers[] = $logger;
        return $this;
    }

    /**
     * Set array LoggerInterface[] loggers
     *
     * @param LoggerInterface[] $loggers
     * @return PsrLogAggregate
     */
    public function setLoggers( array $loggers ) : PsrLogAggregate
    {
        foreach( $loggers as $logger ) {
            $this->addLogger( $logger );
        }
        return $this;
    }
}
