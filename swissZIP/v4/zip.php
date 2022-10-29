<?php
error_reporting(E_ALL);

$api = new swissZIP();
$api->start();
$api->sendResult();


class swissZIP { 
    function __construct() {
        $this->zip_json_file = './data/zip.json';

        $this->result = array();
        $this->result['status']['count'] = 0;
        $this->result['status']['distinct'] = 0;
        $this->zip_data = '';

        $this->format = 'json';
        if(isset($_GET['format'])){
            if ( in_array(strtolower($_GET['format']), array('json','xml','debug') ) ){
                $this->format = strtolower($_GET['format']);
            }
        }
    }

    function start(){
        $result = array();
        if(!isset($_GET['zip'])){
            $zip = '';
            $this->result['status']['status'] = 'error';
            $this->result['status']['error']['name'] = 'no input';
            $this->result['status']['error']['description'] = '/zip.php?zip=0000&format=(json|xml|debug) more at https://github.com/ganti/swissZIP';
        }else{
            $zip = (!empty($_GET['zip']) ? urlencode($_GET['zip']) : 0);
            
            if ( $zip != 0 AND (strlen($zip) != 4) OR (!is_numeric($zip))){ 
                $this->result['status']['status'] = 'error';
                $this->result['status']['error']['name'] = 'invalid input';
                $this->result['status']['error']['description'] = 'invalid input, must be 4 digit number';
            }else{
                $this->result['status']['status']  = 'ok';
                
                if( ($zip != 0) ){
                    $this->getZIPdata($zip);
                }
            }
        }
    }

    function getZIPdata($zip){
        //Load json
        $json = file_get_contents($this->zip_json_file);
        $this->zip_data = json_decode($json, True);
        
        //Filter
        $res = array();
        foreach($this->zip_data as $d){
            if($d['zip'] == $zip){
                $res[] = $d;
            }
        }
        
        //Order by biggest share
        //usort($res, fn($a, $b) => $a['zip-share'] <=> $b['zip-share']);

        $this->result['status']['count'] = count($res);

        if($this->result['status']['count'] == 0){
            $this->result['status']['status'] = 'error';
            $this->result['status']['error']['name'] = 'not found';
            $this->result['status']['error']['description'] = 'ZIP not found';
        }else{
            $this->result['status']['status'] = 'ok';
            $this->result['data'] = $res;

            if( (bool)strtolower($_GET['showDetails']) ){
                $this->getBFSdata();
            }

            $this->result['status']['distinct'] = (($this->result['status']['count'] == 1) ? 1 : 0) ;
        }

    }

    function sendResult(){
        
        if($this->format == 'debug'){
            header("Content-type: text/text");
            print_r($this->result);
            
        }else if($this->format == 'json'){
            header('Content-type: application/json;');
            header('Access-Control-Allow-Origin: *');
            if (version_compare(phpversion(), '7.1', '>=')) {
                ini_set( 'serialize_precision', -1 );
            }

            echo json_encode($this->result, JSON_PRETTY_PRINT);

        }else if($this->format == 'xml'){
            header("Content-type: text/xml");
            $xml_data = new SimpleXMLElement('<?xml version="1.0"?><data></data>');
            $this->array_to_xml($this->result, $xml_data);
            print $xml_data->asXML();
        }

    }

    function array_to_xml( $data, &$xml_data ) {
        foreach( $data as $key => $value ) {
            if( is_numeric($key) ){
                $key = 'town';//.$key; //dealing with <0/>..<n/> issues
            }
            if( is_array($value) ) {
                $subnode = $xml_data->addChild($key);
                $this->array_to_xml($value, $subnode);
            } else {
                $xml_data->addChild("$key",htmlspecialchars("$value"));
            }
         }
    }



}




?>