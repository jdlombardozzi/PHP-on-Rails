<?php
require_once(BASEDIR.'lib/router.php');
$router = new Router();

//$router->map('', 'home#index', array('as' => 'root'));
//$router->map('index.php', 'home#index', array('as' => 'home'));

// Default
$router->map(':controller/:action');