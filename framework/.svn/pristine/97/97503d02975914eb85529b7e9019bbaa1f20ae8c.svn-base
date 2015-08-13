<?php
/**
 * This is the entry point for the RESTful services.
 * You should place this file to the proper place of your server.
 *
 */
define('APP_ROOT',realpath(dirname(__FILE__)));
define('ACTION_ROOT', APP_ROOT . '/actions/');
define('FRAMEWORK',APP_ROOT . '/framework');

define('API_SIG_KEY','03feddaa3434c48cd4b6a6238f002e77');

require_once FRAMEWORK . '/elex.php';

import('elex.rest.RestService');

$service = new RestService();

// set the action root
$service->setActionPath(ACTION_ROOT);
// set the app conmunicate secret key
$service->setAppkey(API_SIG_KEY);
// if you want to run at debug mode
//$service->setDebug(true);

$service->service();
