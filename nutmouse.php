<?php

include_once(__DIR__ . "/classes/includes.php");
session_start();

try {
	// Try creating output from request
	$controller = new Controller($_REQUEST['url']);
	$model = new Model($controller);
	$view = new View($controller, $model);
	echo $view->createOutput();
	
} catch(Exception $e){
	// Catch possible exceptions
	$exception = new View();
	$exception->setTemplate("error/exception");
	$exception->assign("e", $e);
	echo $exception->createOutput();
}