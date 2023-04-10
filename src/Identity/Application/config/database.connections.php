<?php

$config = config('database.connections.' . config('database.default'));
$config['prefix'] = 'identity_';
return ['identity' => $config];
