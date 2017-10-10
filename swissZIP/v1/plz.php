<?php


$api = new swissZIP;
$api->start();
$api->sendResult();

class swissZIP { 
    function __construct() {
        $this->json_file = './data/plz.json';
        $this->result = array();
        $this->result['status']['count'] = 0;
        $this->result['status']['distinct'] = 0;
        $this->data = '';

        $this->format = 'json';
        if(isset($_GET['format'])){
            if ( in_array(strtolower($_GET['format']), array('json','xml','debug') ) ){
                $this->format = strtolower($_GET['format']);
            }
        }
        $this->start();
    }

    function start(){
        $result = array();
        if(!isset($_GET['plz'])){
            $plz = '';
            $this->result['status']['status'] = 'error';
            $this->result['status']['error']['name'] = 'no input';
            $this->result['status']['error']['description'] = '/plz.php?plz=0000&format=(json|xml|debug)';
        }else{
            $plz = $_GET['plz'];
            if ( (strlen($plz) != 4) OR (!is_numeric($plz)) ){ 
                $this->result['status']['status'] = 'error';
                $this->result['status']['error']['name'] = 'invalid input';
                $this->result['status']['error']['description'] = 'invalid input, must be 4 digit number';
            }else{
                $this->result['status']['status']  = 'ok';
                $this->getData($plz);
            }
        }
    }


    function getData($plz){
        //Load json
        $json = file_get_contents($this->json_file);
        $this->data = json_decode($json, True);

        //Filter
        $res = array();
        foreach($this->data as $d){
            if($d['plz'] == $plz){
                $res[] = $d;
            }
        }
        //Order by biggest share
        usort($res, function($a, $b) {
          return $b['share'] <=> $a['share'];
        });

        $this->result['status']['count'] = count($res);

        if($this->result['status']['count'] == 0){
            $this->result['status']['status'] = 'error';
            $this->result['status']['error']['name'] = 'not found';
            $this->result['status']['error']['description'] = 'ZIP not found';
        }else{
            $this->result['data'] = $res;
            $this->result['status']['distinct'] = (($this->result['count'] == 1) ? 1 : 0) ;
        }

    }


    function sendResult(){
        
        if($this->format == 'debug'){
            header("Content-type: text/text");
            print_r($this->result);
            
        }else if($this->format == 'json'){
            header('Content-type: application/json;');
            if (version_compare(phpversion(), '7.1', '>=')) {
                ini_set( 'serialize_precision', -1 );
            }
            echo json_encode($this->result);

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
                $key = 'village';//.$key; //dealing with <0/>..<n/> issues
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