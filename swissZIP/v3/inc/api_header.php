<?php

error_reporting( E_ALL & ~E_NOTICE );
if (version_compare(phpversion(), '7.1', '>=')) {
    ini_set( 'serialize_precision', -1 );
}

require_once(__DIR__.'/cors_header.php');
require_once(__DIR__.'/api_header.inc.php');

require_once(__DIR__.'/api_output.class.php');
