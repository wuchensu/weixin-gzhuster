<?php
require 'plog/classes/plog.php';

Plog::set_config(include 'plog/config.php');
$log = Plog::factory(__FILE__);

$log->T('hello world');
$log->R('aaaaa');

echo "done!";
echo "aaaa";






































?>