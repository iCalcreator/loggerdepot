## LoggerDepot

is a depot for PHP application/software loggers
>making loggers available on demand.

Each logger is identified by a unique and fixed (type case-sensitive _string_) key and set and retrieved using the key.

You can use namespace as key (ex `__NAMESPACE__`) setting up a logger and
invoke the logger using (qualified namespaced) class names 
(ex `get_class()` or `__CLASS__`) in the namespace tree.

It is possible to combine fixed key and 'namespaced' loggers in the depot.

You may also use different keys for the same logger as well as set a logger as a fallback logger.
 
Invoking of a logger is as easy as `LoggerDepot::getLogger( <key> )`. <br>
If no logger is set, a `Psr\Log\NullLogger` is returned.

The construction  makes it possible to supervise loggers for separate parts (functions, modules, components etc) of your software.
It is also possible to aggregate multiple (Psr\Log) loggers into one.


###### Usage

For LoggerDepot usage please review the [details].


###### Sponsorship

Donation using <a href="https://paypal.me/kigkonsult" rel="nofollow">paypal.me/kigkonsult</a> are appreciated.
For invoice, <a href="mailto:ical@kigkonsult.se">please e-mail</a>.


###### Installation

For LoggerDepot installation please review the [install].

Version 1.4+ supports PHP 8.0, 1.2 7.4, 1.0.4 7.0.


###### License

This project is licensed under the LGPLv3 License


[Composer]:https://getcomposer.org/
[details]:docs/usage.txt
[install]:docs/install.txt
