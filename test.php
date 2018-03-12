<?php
include __DIR__ . '/autoload.php';

if (!isset($argv[1])) {
    throw new \Exception ('Email is not set');
}

$email = $argv[1];
// validate email
$valid = \EmailChecker\Validator::validateEmail ($email);
if (!$valid) {
    throw new \Exception ('Invalid email.');
}

// get host from email
$host = \EmailChecker\Validator::getHostFromEmail ($email);
// dig it
$lookup = new \EmailChecker\Lookup ($host);
// get mx records
$mx = $lookup->getMxRecords();
// telnet
$telnet = new \EmailChecker\Telnet($mx[0]);
// say helo
$helo = $telnet->sayHelo();
// set mail from
$telnet->mailFrom('cgalgo@openovate.com');
// set receive to
$telnet->rcptTo($email);
// check
echo $telnet->check();




