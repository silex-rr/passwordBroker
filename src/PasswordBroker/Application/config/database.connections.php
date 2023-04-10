<?php

$config = config('database.connections.' . config('database.default'));
$config['prefix'] = 'password_broker_';
return ['password_broker' => $config];
