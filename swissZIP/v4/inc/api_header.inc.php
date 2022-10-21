<?php

    function requestBodyAsJson(){
        if(strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0){
            throw new Exception('Invalid request method!');
        }
        $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
        if(strcasecmp($contentType, 'application/json') != 0){
            throw new Exception('Invalid content type!');
        }
        //Receive the RAW post data.
        $content = trim(file_get_contents("php://input"));
        $decoded = json_decode($content, true);
        if(!is_array($decoded)){
            throw new Exception('Received content contained invalid JSON!');
        }
        return $decoded;
    }

    function sendResponse($json){
        require_once('./inc/cors_footer.inc.php');
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    function getRequestData(){
        $data = null;
        if(isset($_GET['debug']) and $_GET['debug']==1){
            $data = $_GET;
        }else if(strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') == 0){
            $data = requestBodyAsJson();
        }else if(strcasecmp($_SERVER['REQUEST_METHOD'], 'GET') == 0){
            $data = $_GET;
        }
        return $data;
    }
?>
