#!/usr/bin/env php
<?php
require 'vendor/autoload.php';
ini_set('memory_limit', '1000M'); // fix errors
if (file_exists('session.madeline')) {
    try {
        $MadelineProto = \danog\MadelineProto\Serialization::deserialize('session.madeline');
    } catch (Exception $e) {}
}
if (file_exists('.env')) {
    $dotenv = new Dotenv\Dotenv(__DIR__);
    $dotenv->load();
}
$settings = json_decode(getenv("MTPROTO_SETTINGS"), true);

if (!isset($MadelineProto)) {
    $MadelineProto = new \danog\MadelineProto\API($settings);
    $checkedPhone = $MadelineProto->auth->checkPhone(
        [
            'phone_number'     => getenv('MTPROTO_NUMBER'),
        ]
    );
    \danog\MadelineProto\Logger::log($checkedPhone);
    $sentCode = $MadelineProto->phone_login(getenv('MTPROTO_NUMBER'));
    \danog\MadelineProto\Logger::log($sentCode);
    echo 'Enter the code you received: ';
    $code = fgets(
        STDIN, (isset($sentCode['type']['length']) ? $sentCode['type']
        ['length'] : 5) + 1
    );
    $authorization = $MadelineProto->complete_phone_login($code);

        \danog\MadelineProto\Logger::log([$authorization], \danog\MadelineProto\Logger::NOTICE);
        if ($authorization['_'] === 'account.noPassword') {
            throw new \danog\MadelineProto\Exception('2FA is enabled but no password is set!');
        }
        if ($authorization['_'] === 'account.password') {
            \danog\MadelineProto\Logger::log(['2FA is enabled'], \danog\MadelineProto\Logger::NOTICE);
            $authorization = $MadelineProto->complete_2fa_login(readline('Please enter your password (hint '.$authorization['hint'].'): '));
        }
        if ($authorization['_'] === 'account.needSignup') {
            \danog\MadelineProto\Logger::log(['Registering new user'], \danog\MadelineProto\Logger::NOTICE);
            $authorization = $MadelineProto->complete_signup($code, readline('Please enter your first name: '), readline('Please enter your last name (can be empty): '));
        }

    \danog\MadelineProto\Logger::log([$authorization]);
    echo 'Serializing MadelineProto to session.madeline...'.PHP_EOL;
    echo 'Wrote '.\danog\MadelineProto\Serialization::serialize(
        'session.madeline',
        $MadelineProto
    ).' bytes'.PHP_EOL;

    echo 'Deserializing MadelineProto from session.madeline...'.PHP_EOL;
    $uMadelineProto = \danog\MadelineProto\Serialization::deserialize(
        'session.madeline'
    );
}
<?
//Include Predis library. See https://github.com/nrk/predis for more info
require "Predis/Autoloader.php";
//Connect to Redis
Predis\Autoloader::register();
try {
	$redis = new Predis\Client();
	$redis = new Predis\Client(array(
		"scheme" => "tcp",
		"host" => "127.0.0.1"))
	;
}
catch (Exception $e) {
	echo "Couldn't connect to Redis";
	echo $e->getMessage();
}
//Get list of all keys. This creates an array of keys from the redis-cli output of "KEYS *"
$list = $redis->keys("*");
//Optional: Sort Keys alphabetically
sort($list);
//Loop through list of keys
foreach ($list as $key)
{
	//Get Value of Key from Redis
	$value = $redis->get($key);
	
	//Print Key/value Pairs
	echo "<b>Key:</b> $key <br /><b>Value:</b> $value <br /><br />";
}
//Disconnect from Redis
$redis->disconnect();
?>
}
