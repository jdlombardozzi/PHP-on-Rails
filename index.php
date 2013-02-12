<?php
define('BASEDIR', str_replace( "\\", "/", dirname( __FILE__ ) ) . '/' );

// Set error reporting
ini_set('display_errors', TRUE);
error_reporting(E_ALL);

if(!session_id()) session_start();

// Load routes
require BASEDIR.'config/routes.php';

// Load application
require BASEDIR.'lib/application.php';
$application = new Application($routes);

$application->run();