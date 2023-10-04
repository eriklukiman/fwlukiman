<?php
return array (
	'engine'	=> 'redis', //redis, apc
	'host'		=> 'localhost',
	'port'		=> '6379',
	'password'	=> '',
	'database'		=> '',
	'options'	=> [
		'connect_timeout' 	=> 1,
		'timeout' 					=> 5
	],
);