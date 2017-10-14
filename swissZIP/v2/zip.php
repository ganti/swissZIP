<?php


$api = new swissZIP();
$api->start();
$api->sendResult();



class swissZIP { 
    function __construct() {
        $this->zip_json_file = './data/zip.json';
        $this->bfs_json_file = './data/bfs.json';

        $this->result = array();
        $this->result['status']['count'] = 0;
        $this->result['status']['distinct'] = 0;
        $this->zip_data = '';
        $this->bfs_data = '';

        $this->format = 'json';
        if(isset($_GET['format'])){
            if ( in_array(strtolower($_GET['format']), array('json','xml','debug') ) ){
                $this->format = strtolower($_GET['format']);
            }
        }

        //Commit ID to status/version
        exec('cd '.__DIR__);
        $qry = 'git log -n 1 --pretty=format:%H -- '.__FILE__;
        exec($qry, $o);
        $version = $o[0];
        if ($version != '') {
            $this->result['status']['version'] = substr($version, 0, 7);
        }else{
            $this->result['status']['version'] = date ("Ymd-His", filemtime(__FILE__));
        }
        $this->start();
    }

    function start(){
        $result = array();
        if(!isset($_GET['zip'])){
            $zip = '';
            $this->result['status']['status'] = 'error';
            $this->result['status']['error']['name'] = 'no input';
            $this->result['status']['error']['description'] = '/zip.php?zip=0000&format=(json|xml|debug)';
        }else{
            $zip = $_GET['zip'];
            if ( (strlen($zip) != 4) OR (!is_numeric($zip)) ){ 
                $this->result['status']['status'] = 'error';
                $this->result['status']['error']['name'] = 'invalid input';
                $this->result['status']['error']['description'] = 'invalid input, must be 4 digit number';
            }else{
                $this->result['status']['status']  = 'ok';
                $this->getZIPdata($zip);
            }
        }
    }


    function getZIPdata($zip){
        //Load json
        $json = file_get_contents($this->zip_json_file);
        $this->zip_data = json_decode($json, True);
        $this->result['status']['data_modification_date']= date("Ymd-His", filemtime($this->zip_json_file));

        //Filter
        $res = array();
        foreach($this->zip_data as $d){
            if($d['zip'] == $zip){
                $res[] = $d;
            }
        }
        //Order by biggest share
        usort($res, function($a, $b) {
          return $b['zip-share'] <=> $a['zip-share'];
        });

        $this->result['status']['count'] = count($res);

        if($this->result['status']['count'] == 0){
            $this->result['status']['status'] = 'error';
            $this->result['status']['error']['name'] = 'not found';
            $this->result['status']['error']['description'] = 'ZIP not found';
        }else{
            $this->result['data'] = $res;

            if( (bool)strtolower($_GET['showDetails']) ){
                $this->getBFSdata($res['bfs']);
            }

            $this->result['status']['distinct'] = (($this->result['status']['count'] == 1) ? 1 : 0) ;
        }

    }

    function getBFSdata(){
        //Load json
        $json = file_get_contents($this->bfs_json_file);
        $this->bfs_data = json_decode($json, True);
        
        //Filter
        $res = array();
        $i = 0;
        foreach($this->result['data'] as $zd){
            $res = array();
            foreach($this->bfs_data as $d){
                if($d['bfs'] == $zd['bfs']){
                    $res[] = $d;
                }
            }
            $this->result['data'][$i] = array_merge($this->result['data'][$i], $res[0]);
            $i += 1;
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