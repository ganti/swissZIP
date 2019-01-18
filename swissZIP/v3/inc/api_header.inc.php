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

    function invokeAction($RACL, $data){
        $sendOutput = new sendOutputClass();
        $return_data = "";
        if(array_key_exists($data['request_action'], $RACL) == False){
            $sendOutput->set('warning', 'unknown request_action');
        }else{
            $request_action = (isset($data['request_action']) ? $data['request_action'] : null);
            $role_required = $RACL[$request_action];
            if( UserHasRolePremission($role_required) ){
                if(is_callable($request_action)) {
                    $return_data = call_user_func($request_action, $data);
                }else{
                    $sendOutput->set('error', $request_action.' is not callable');
                }
            }else{
                $sendOutput->set('warning', 'no permission for '.$role_required);
            }

            if($sendOutput->isError() == True){
                $return_data = $sendOutput->Output();
            }
        }
        return $return_data;
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
