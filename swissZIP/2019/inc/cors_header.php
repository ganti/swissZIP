<?php

    if(!isset($_GET['debug']) and strcasecmp($_SERVER['REQUEST_METHOD'], 'OPTIONS') === 0) {
        header('Content-Type: text/plain');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers: Origin, Authorization, Content-Type, Accept');
        exit;
    }
