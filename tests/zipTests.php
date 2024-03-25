<?php declare(strict_types=1);

use donatj\MockWebServer\MockWebServer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use GuzzleHttp\Client;

final class zipTests extends TestCase
{
    private static $process;
    private static $client;
    private string $testServer = 'localhost:9318';

    public function testNoParams(): void
    {
        $client = $this->getClient();

        $response = $client->request("GET", 'v4/zip.php');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeader('Content-type')[0], 'not json contenttype');
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals('error', $data['status']['status'], 'status not error');
        $this->assertEquals(0, $data['status']['count'], 'count not 0');
        $this->assertEquals(0, $data['status']['distinct'], 'distinct not 0');
        $this->assertFalse(isset($data['data']), 'data should not exist');
        $this->assertTrue(isset($data['error']), 'error should exist');

        $this->assertEquals('no input', $data['error']['name'], 'wrong error::name');
        $this->assertEquals('/zip.php?zip=0000&format=(json|xml|debug) more at https://github.com/ganti/swissZIP', $data['error']['description'], 'wrong error::description');
    }

    private function getClient(): Client
    {
        return new Client([
            'http_errors' => false,
            'base_uri' => 'http://localhost:13121',
            'timeout' => 2.0,
        ]);
    }

    public function provideInvalidZipValues(): iterable
    {
        yield ['1'];
        yield ['12'];
        yield ['123'];
        yield ['12345'];
        yield ['foobar'];
        yield ['12.77'];
    }

    /**
     * @dataProvider provideInvalidZipValues
     */
    public function testInvalidZip(string $zip): void
    {
        $client = $this->getClient();

        $uri = sprintf("v4/zip.php?zip=%s", (string)$zip);
        $response = $client->request("GET", $uri);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeader('Content-type')[0], 'not json contenttype');
        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertEquals('error', $data['status']['status'], 'status not error');
        $this->assertEquals(0, $data['status']['count'], 'count not 0');
        $this->assertEquals(0, $data['status']['distinct'], 'distinct not 0');
        $this->assertFalse(isset($data['data']), 'data should not exist');
        $this->assertTrue(isset($data['error']), 'error should exist');

        $this->assertEquals('invalid input', $data['error']['name'], 'wrong error::name');
        $this->assertEquals('invalid input, must be 4 digit number', $data['error']['description'], 'wrong error::description');
    }

    public function provideValidZipValuesDistinct(): iterable
    {
        yield ['7306'];
        yield ['7314'];
        yield ['7208'];
    }

    /**
     * @dataProvider provideValidZipValuesDistinct
     */
    public function testValidZipDistinct(string $zip): void
    {
        $client = $this->getClient();

        $uri = sprintf("v4/zip.php?zip=%s", (string)$zip);
        $response = $client->request("GET", $uri);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeader('Content-type')[0], 'not json contenttype');
        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertEquals('ok', $data['status']['status'], 'status not error');
        $this->assertEquals(1, $data['status']['count'], 'count not 1');
        $this->assertEquals(1, $data['status']['distinct'], 'distinct not 1');
        $this->assertTrue(isset($data['data']), 'data should exist');
        $this->assertFalse(isset($data['error']), 'error should not exist');
        $this->assertEquals($zip, $data['data'][0]['zip'], 'response not same ZIP');
    }

    public function provideValidZipValuesNotDistinct(): iterable
    {
        yield ['3053', 6];
        yield ['3097', 2];
        yield ['7000', 2];
    }

    /**
     * @dataProvider provideValidZipValuesNotDistinct
     */
    public function testValidZipNotDistinct(string $zip, int $count): void
    {
        $client = $this->getClient();

        $uri = sprintf("v4/zip.php?zip=%s", (string)$zip);
        $response = $client->request("GET", $uri);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeader('Content-type')[0], 'not json contenttype');
        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertEquals('ok', $data['status']['status'], 'status not error');
        $this->assertEquals($count, $data['status']['count'], 'count not what expected');
        $this->assertEquals(0, $data['status']['distinct'], 'distinct not 0');
        $this->assertTrue(isset($data['data']), 'data should exist');
        $this->assertFalse(isset($data['error']), 'error should not exist');
    }


    public function testValidFormats(): void
    {
        $client = $this->getClient();

        foreach (['json', 'xml', 'debug'] as $format) {
            $uri = sprintf("v4/zip.php?zip=3053&format=%s", $format);
            $response = $client->request("GET", $uri);

            $this->assertEquals(200, $response->getStatusCode());

            if ($format == 'json') {
                $this->assertEquals('application/json', $response->getHeader('Content-type')[0], 'not json contenttype');
            }
            if ($format == 'xml') {
                $this->assertEquals('text/xml;charset=UTF-8', $response->getHeader('Content-type')[0], 'not xml contenttype');
            }
        }

    }

    public function testInvalidFormats(): void
    {
        $client = $this->getClient();

        foreach (['foo', 'bar', 'foobar'] as $format) {
            $uri = sprintf("v4/zip.php?zip=3053&format=%s", $format);
            $response = $client->request("GET", $uri);

            $this->assertEquals(200, $response->getStatusCode());
            $data = json_decode($response->getBody()->getContents(), true);
            $this->assertEquals('error', $data['status']['status'], 'status not error');
            $this->assertEquals(0, $data['status']['count'], 'count not 0');
            $this->assertEquals(0, $data['status']['distinct'], 'distinct not 0');
            $this->assertFalse(isset($data['data']), 'data should not exist');
            $this->assertTrue(isset($data['error']), 'error should exist');

            $this->assertEquals('format invalid', $data['error']['name'], 'wrong error::name');
            $this->assertEquals('format must be json, xml, debug', $data['error']['description'], 'wrong error::description');
        }

    }


    public function provideValidCantonFilterValues(): iterable
    {
        yield ['3053', 'BE', 6];
        yield ['3097', 'BE', 2];
        yield ['7000', 'GR', 2];
        yield ['8866', 'GL', 1];
        yield ['8866', 'SG', 1];
    }

    /**
     * @dataProvider provideValidCantonFilterValues
     */
    public function testValidCantonFilter(string $zip, string $canton, int $count): void
    {
        $client = $this->getClient();

        $uri = sprintf("v4/zip.php?zip=%s&canton=%s", (string)$zip, $canton);
        $response = $client->request("GET", $uri);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeader('Content-type')[0], 'not json contenttype');
        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertEquals('ok', $data['status']['status'], 'status not error');
        $this->assertEquals($count, $data['status']['count'], 'count not what expected');
        $this->assertTrue(isset($data['data']), 'data should exist');
        $this->assertFalse(isset($data['error']), 'error should not exist');
    }


}