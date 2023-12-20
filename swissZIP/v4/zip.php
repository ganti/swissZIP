<?php
error_reporting(E_ALL);

$api = new swissZIP();
$api->start();
$api->sendResult();

class swissZIP
{


    private const ALLOWED_FORMATS = ['json', 'xml', 'debug'];
    private const ALLOWED_CANTONS = ['AG', 'AI', 'AR', 'BE', 'BL', 'BS', 'FR', 'GE', 'GL', 'GR', 'JU', 'LU', 'NE', 'NW', 'OW', 'SG', 'SH', 'SO', 'SZ', 'TG', 'TI', 'UR', 'VD', 'VS', 'ZG', 'ZH'];
    private string $zip_json_file = './data/zip.json';
    private styledOutput $output;
    private string $format = 'json';
    private ?string $filterCanton = null;

    function __construct()
    {
        $this->output = new styledOutput();
    }

    public function start(): void
    {
        if (isset($_GET['format'])) {
            if (!in_array(strtolower($_GET['format']), $this::ALLOWED_FORMATS)) {
                $this->output->setError('format invalid', 'format must be ' . join(', ', $this::ALLOWED_FORMATS));
                return;
            }
            $this->format = strtolower($_GET['format']);
        }
        if (isset($_GET['canton'])) {
            $canton = (!empty($_GET['canton']) ? strtoupper(urlencode($_GET['canton'])) : null);
            if (!empty($canton) and !in_array($canton, $this::ALLOWED_CANTONS)) {
                $this->output->setError('canton invalid', 'if you want to filter by canton, only one of the following ' . join(', ', $this::ALLOWED_CANTONS));
                return;
            }
            $this->filterCanton = $canton;
        }
        if (isset($_GET['zip'])) {
            $zip = (!empty($_GET['zip']) ? urlencode($_GET['zip']) : 0);

            if ($zip != 0 and (strlen($zip) != 4) or (!is_numeric($zip))) {
                $this->output->setError('invalid input', 'invalid input, must be 4 digit number');
                return;
            }
            if (($zip != 0)) {
                $this->getZIPdata($zip);
            }
        }
    }

    private function getZIPdata($zip): void
    {
        $json = file_get_contents($this->zip_json_file);
        $zip_data = json_decode($json, True);

        //Filter
        $zipResult = [];
        foreach ($zip_data as $d) {
            if ($d['zip'] == $zip) {
                $zipResult[] = $d;
            }
        }

        if($this->filterCanton !== null){
            $zipCantonResult = [];
            foreach ($zipResult as $d) {
                if ($d['canton'] == $this->filterCanton) {
                    $zipCantonResult[] = $d;
                }
            }
            $zipResult = $zipCantonResult;
        }

        //Order by biggest share
        //usort($res, fn($b, $a) => $a['zip-share'] <=> $b['zip-share']);

        $this->output->setData($zipResult);

        if ($this->output->getCount() == 0) {
            $this->output->setError('not found', 'ZIP not found');
        }
    }

    public function sendResult(): void
    {

        if ($this->format == 'debug') {
            header("Content-type: text/text");
            print_r($this->output->getOutput());

        } else if ($this->format == 'json') {
            header('Content-type: application/json');
            header('Access-Control-Allow-Origin: *');
            if (version_compare(phpversion(), '7.1', '>=')) {
                ini_set('serialize_precision', -1);
            }
            echo json_encode($this->output->getOutput());

        } else if ($this->format == 'xml') {
            header("Content-type: text/xml");
            $xml_data = new SimpleXMLElement('<?xml version="1.0"?><data></data>');
            $this->array_to_xml($this->output->getOutput(), $xml_data);
            echo $xml_data->asXML();
        }

    }

    private function array_to_xml($data, &$xml_data)
    {
        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                $key = 'town';//.$key; //dealing with <0/>..<n/> issues
            }
            if (is_array($value)) {
                $subnode = $xml_data->addChild($key);
                $this->array_to_xml($value, $subnode);
            } else {
                $xml_data->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }
}

class styledOutput
{

    private array $output = [
        'status' => [
            'status' => 'error',
            'count' => 0,
            'distinct' => 0,
        ],
        'error' => [
            'name' => 'no input',
            'description' => '/zip.php?zip=0000&format=(json|xml|debug) more at https://github.com/ganti/swissZIP'
        ],
        'data' => null
    ];

    public function getOutput(): array
    {
        if ($this->getCount() == 0) {
            $this->setData(null);
        }
        $this->output['status']['distinct'] = (($this->output['status']['count'] == 1) ? 1 : 0);
        return $this->output;
    }

    public function getCount(): int
    {
        return $this->output['status']['count'];
    }

    public function setData(?array $data): void
    {
        if ($data == null) {
            unset($this->output['data']);
        } else {
            $this->output['data'] = $data;
            $this->output['status']['count'] = count($data);
            $this->output['status']['distinct'] = ($this->output['status']['count'] == 1) ? 1 : 0;
            $this->setError(null, null);
        }
    }

    public function setError(?string $name, ?string $description): void
    {
        if ($name == null && $description == null) {
            $this->output['status']['status'] = 'ok';
            unset($this->output['error']);
        } else {
            $this->output['status']['status'] = 'error';
            $this->output['error']['name'] = $name;
            $this->output['error']['description'] = $description;
            $this->setData(null);
        }
    }

}
