<?php
require_once('vendor/autoload.php');

use Assert\Assertion;

use Swoole\Timer;

Assertion::nullOrMax(null, 42); // success
Assertion::nullOrMax(1, 42);    // success
//Assertion::nullOrMax(1337, 42); // exception

/*Co\run(function() {
	$key = 'abc';
	$curl = new \Swoole\Client\CURL();
	$redis = new Swoole\Coroutine\Redis();
	$redis->connect('127.0.0.1', 6379);
	// $redis->set($key, 126, null);
	$val = $redis->get($key);
	var_dump($val);
});
*/

function toRun() {
	$a = rand();
	echo $a . ' ' . date('H:i:s') . PHP_EOL;
	co::sleep(2.5);
	echo $a . ' ' . date('H:i:s') . PHP_EOL;
}

//Co\run(function() {
	Timer::tick(1000, "toRun");

//});

/*Co\run(function() {
	$key = 'abc';
	$curl = new \Swoole\Client\CURL();
	$redis = new Swoole\Coroutine\Redis();
	$redis->connect('127.0.0.1', 6379);
	// $redis->set($key, 126, null);
	$val = $redis->get($key);
	var_dump($val);
});
*/

/*
$s = microtime(true);
Co\run(function() {
    for ($c = 100; $c--;) {
        go(function () {
            $mysql = new Swoole\Coroutine\MySQL;
            $mysql->connect([
                'host' => '127.0.0.1',
                'user' => 'rx',
                'password' => 'a',
                'database' => 'event'
            ]);
            $statement = $mysql->prepare('SELECT  SQL_CALC_FOUND_ROWS * FROM master_shoes WHERE  1  LIMIT :_usedLimit0, :_usedLimit1');
			$statement->bindValue(':_usedLimit0', (int) $limit[0], PDO::PARAM_INT);
			if (isset($limit[1])) $statement->bindValue(':_usedLimit1', (int) $limit[1], PDO::PARAM_INT);
            for ($n = 1; $n--;) {
                $result = $statement->execute();
				print_r($result);
                assert(count($result) > 0);
            }
        });
    }
});
echo 'use ' . (microtime(true) - $s) . ' s';
exit;


use Swoole\Coroutine as co;

use Swlib\SwPDO;

$swH = new \Swoole\Coroutine\Channel(2);
$pdoH = new \Swoole\Coroutine\Channel(2);
	$options = [
		'mysql:host=127.0.0.1;dbname=event;charset=UTF8',
		'rx',
		'a'
	];

co::create(function() use ($swH, $pdoH, $options) {

	$sql = 'select * from `user` LIMIT 1';

	$pdo = new \PDO(...$options);
	$pdoH->push($pdo);
	// $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); //strong type
	// $pdo_both = $pdo->query($sql)->fetch();
	// $pdo_assoc = $pdo->query($sql)->fetch(\PDO::FETCH_ASSOC);
	// $pdo_object = $pdo->query($sql)->fetch(\PDO::FETCH_OBJ);
	// $pdo_number = $pdo->query($sql)->fetch(\PDO::FETCH_NUM);


	$swpdo = \Swlib\SwPDO::construct(...$options); //default is strong type
	$swH->push($swpdo);
	// var_dump($swH->isEmpty());echo 'bbb';
	// $swpdo_both = $swpdo->query($sql)->fetch();
	// $swpdo_assoc = $swpdo->query($sql)->fetch(\PDO::FETCH_ASSOC);
	// $swpdo_object = $swpdo->query($sql)->fetch(\PDO::FETCH_OBJ);
	// $swpdo_number = $swpdo->query($sql)->fetch(\PDO::FETCH_NUM);

	// var_dump($swpdo_assoc);
	// var_dump($pdo_both === $swpdo_both);
	// var_dump($pdo_assoc === $swpdo_assoc);
	// var_dump($pdo_object == $swpdo_object);
	// var_dump($pdo_number === $swpdo_number);
});

co::create(function() use ($swH, $pdoH, $options) {

	$sql = 'select * from `user` LIMIT 1';

	$pdo = $pdoH->pop();
	$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); //strong type
	$pdo_both = $pdo->query($sql)->fetch();
	$pdo_assoc = $pdo->query($sql)->fetch(\PDO::FETCH_ASSOC);
	$pdo_object = $pdo->query($sql)->fetch(\PDO::FETCH_OBJ);
	$pdo_number = $pdo->query($sql)->fetch(\PDO::FETCH_NUM);

	// co::sleep(1);
// $swpdo = \Swlib\SwPDO::construct(...$options); //default is strong type
	// var_dump($swH->isEmpty());
	var_dump($swH);
	echo '='.$swH->isFull()."=\n";
	// $swH->push($swpdo);
	$swpdo = $swH->pop();//var_dump($swpdo);
	$swH->push($swpdo);
	var_dump($swH->length());
	$swpdo_both = $swpdo->query($sql)->fetch();
	$swpdo_assoc = $swpdo->query($sql)->fetch(\PDO::FETCH_ASSOC);
	$swpdo_object = $swpdo->query($sql)->fetch(\PDO::FETCH_OBJ);
	$swpdo_number = $swpdo->query($sql)->fetch(\PDO::FETCH_NUM);

	// var_dump($swpdo_assoc);
	var_dump($pdo_both === $swpdo_both);
	var_dump($pdo_assoc === $swpdo_assoc);
	var_dump($pdo_object == $swpdo_object);
	var_dump($pdo_number === $swpdo_number);
});


exit;

co::create(function() {
    // $db = new co\MySQL();
    $server = array(
        'host' => 'localhost',
        'user' => 'rx',
        'password' => 'a',
        'database' => 'event',
    );

    // $ret1 = $db->connect($server);
	$db = new Swoole\Coroutine\MySQL;
	// print_r($db);
$db->connect($server);
$data = $db->begin();
// $data = $db->escape("abc'efg\r\n");
	// $data = $db->escape("abc'efg\r\n");
	// var_dump($data);
    $stmt = $db->prepare('SELECT * FROM user WHERE userId=? AND userName=? ');
	// print_r($db);
	// print_r($stmt);
    if ($stmt == false) {
        print_r($db->errno, $db->error);
    } else {
        $ret2 = $stmt->execute(array(2, 'Erik Lukiman'));
        // print_r($ret2);

        $ret3 = $stmt->execute(array(13, 'alvin'));
        // print_r($ret3);
		
		$s1 = $db->prepare('SELECT userName FROM user limit 2');
		$r1 = $s1->execute();
		print_r($r1);
		$s2 = $db->prepare('SELECT evnhName FROM event_header limit 2');
		$r2 = $s2->execute();
		print_r($r2);
    }
});
*/
