<?php
/*
engine			| port
----------------------------------------
redis 				| 6379
memcache	| 11211
apc					| -

*/

return array (
	'engine'	=> 'memcache', //redis, memcache, apc
	'port'		=> '11211', //redis = 6379; memcache 11211
	'host'		=> 'localhost',
	'password'	=> '',
	'database'		=> '',
	'options'	=> [
		'connect_timeout' 	=> 1,
		'timeout' 					=> 5
	],
);