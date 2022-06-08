<?php

namespace GoCache\CDN\Model;

use GoCache\CDN\Helper\Data;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;


class Api
{
    const GOCACHE_TOKEN_HEADER = "GoCache-Token";

    /**
     * @var Config
     */
    private $config;
    /**
     * @var Data
     */
    private $helper;
    /**
     * @var \Zend_Http_Client
     */
    private $client;

    /**
     * Api constructor.
     * @param Config $config
     * @param Data $helper
     * @param \Zend_Http_Client $client
     * @param Logger $logger
     */
    public function __construct(
        Config $config,
        Data $helper,
        \Zend_Http_Client $client
    )
    {
        $this->config = $config;
        $this->helper = $helper;
        $this->client = $client;
    }

    /**
     * @return string
     */
    private function getPurgeEndpoint()
    {
        $domain = $this->config->getMainDomain();
        return $this->getPurgeUri($domain);
    }

    /**
     * @param string $domain
     * @return string
     */
    private function getPurgeUri($domain, $cache = 'cache/')
    {
        $endpoint = $this->config->getEndpoint();
        return sprintf("%s/%s%s", $endpoint, $cache, $domain);
    }

    public function info($key, $array = []){
        if(!$this->config->isLogEnable())
            return false;
        
        $fp = fopen('../var/log/gocache_log.txt', 'a+');

        fwrite($fp, "====================================== " . date("d/m/Y H:i:s") . "======================================\n");
        fwrite($fp, $key . "\n");

        foreach($array as $key => $value){
            fwrite($fp, "Key: {$key} Value: \n" . print_r($value, TRUE) . "\n");
        }

        fwrite($fp, "====================================== END ======================================\n");

        fclose($fp);

        return true;
    }

    /**
     * @return string
     */
    private function getConfigEndpoint()
    {
        $domain = $this->config->getMainDomain();
        return $this->getPurgeUri($domain, 'domain/');
    }

    /**
     * @param $domain
     * @return string
     */
    private function getConfigUri($domain)
    {
        $endpoint = $this->config->getEndpoint();
        return sprintf("%s/domain/%s", $endpoint, $domain);
    }

    /**
     * @param $token
     * @return array
     */
    private function getTokenHeader($token)
    {
        return [self::GOCACHE_TOKEN_HEADER => $token];
    }

    public function purgeByURL($urls, $single = false){

        $body = "";
        $desc = "GoCache Purge URLS";
        if(!$single){
            $urls_ = preg_split('/\r\n|\r|\n/', $urls);

            $out = [];
            foreach ($urls_ as $key=>$value) {
                if(empty($value))
                    continue;
                $out[sprintf("urls[%s]", $key)] = $value;
            }
            $body = $out;
        } else {
            $out = [];
            $out[sprintf("urls[0]")] = $urls;
            $body = $out;
            $desc = "GoCache Purge Product URL";
        }
        
        $domain = $this->helper->getCurrentDomain();
        $endpoint = $this->getPurgeEndpoint();
        $this->client->setUri($endpoint);
        $header = $this->getTokenHeader($this->config->getToken());
        $this->client->setHeaders($header);
        //$body = $out;
        $this->client->setParameterPost($body);
        $response = $this->client->request('DELETE');

        $this->info($desc, [
            'url' => $endpoint,
            'header' => $header,
            'body' => $body,
            'request' => $this->client->getLastRequest(),
            'response' => $response->getBody()
        ]);
        return $response;
    }

    /**
     * @param array $tags
     * @return \Zend_Http_Response
     * @throws NoSuchEntityException
     * @throws \Zend_Http_Client_Exception
     */
    public function purgeByTags($tags = [])
    {
        $domain = $this->helper->getCurrentDomain();
        $endpoint = $this->getPurgeEndpoint();
        $this->client->setUri($endpoint);
        $header = $this->getTokenHeader($this->config->getToken());
        $this->client->setHeaders($header);


        $body = $this->prepareTagsArray($domain, $tags);
        $this->client->setParameterPost($body);
        $response = $this->client->request('DELETE');

        $this->info("GOCACHE PURGE TAGS", [
            'url' => $endpoint,
            'header' => $header,
            'body' => $body,
            'request' => $this->client->getLastRequest(),
            'response' => $response->getBody()
        ]);
        return $response;
    }

    /**
     * @return \Zend_Http_Response
     * @throws \Zend_Http_Client_Exception
     */
    public function purgeAll()
    {
        $endpoint = $this->getPurgeEndpoint();
        $fullEndpoint = sprintf("%s/all", $endpoint);
        $this->client->setUri($fullEndpoint);
        $header = $this->getTokenHeader($this->config->getToken());
        $this->client->setHeaders($header);
        $response = $this->client->request('DELETE');
        $this->info("GOCACHE FLUSH ALL", [
            'url' => $fullEndpoint,
            'header' => $header,
            'request' => $this->client->getLastRequest(),
            'response' => $response->getBody()
        ]);
        return $response;
    }

    /**
     * @param string $contentType
     * @return \Zend_Http_Response
     * @throws \Zend_Http_Client_Exception
     */
    public function purgeByContentType($contentType)
    {
        $endpoint = $this->getPurgeEndpoint();
        $this->client->setUri($endpoint);
        $header = $this->getTokenHeader($this->config->getToken());
        $this->client->setHeaders($header);
        $body = [
            'content-type'  =>  $contentType,
        ];
        $this->client->setParameterPost($body);
        $response = $this->client->request('DELETE');
        $this->info("GOCACHE PURGE CONTENT TYPE", [
            'header' => $header,
            'request' => $this->client->getLastRequest(),
            'response' => $response->getBody()
        ]);
        return $response;
    }

    /**
     * @param string $token
     * @param string $mainDomain
     * @return bool
     * @throws \Zend_Http_Client_Exception
     */
    public function testToken($token, $mainDomain)
    {
        $endpoint = $this->getPurgeUri($mainDomain);
        $fullEndpoint = sprintf("%s", $endpoint);
        $this->client->setUri($fullEndpoint);
        $header = $this->getTokenHeader($token);
        $this->client->setHeaders($header);
        $response = $this->client->request('GET');
        $this->info("GOCACHE TEST TOKEN", [
            'url' => $fullEndpoint,
            'header' => $header,
            'request' => $this->client->getLastRequest(),
            'response' => $response->getBody()
        ]);
        return $response->isSuccessful();
    }

    /**
     * @param array $param
     * @return bool
     * @throws \Zend_Http_Client_Exception
     */
    public function updateConfig($param)
    {
        $endpoint = $this->getConfigEndpoint();
        $fullEndpoint = sprintf("%s", $endpoint);
        $this->client->setUri($fullEndpoint);
        $header = $this->getTokenHeader($this->config->getToken());
        $this->client->setHeaders($header);
        $this->client->setParameterPost($param);
        $response = $this->client->request('PUT');
        $this->info("GOCACHE UPDATE CONFIG", [
            'url' => $fullEndpoint,
            'header' => $header,
            'request' => $this->client->getLastRequest(),
            'response' => $response->getBody()
        ]);
        return $response->isSuccessful();
    }

    /**
     * @param string $domain
     * @param array $tags
     * @return array
     */
    private function prepareTagsArray($domain, array $tags = [])
    {
        $out = [];
//        $out[sprintf("urls[%s]", 1)] = $domain;
        foreach ($tags as $key=>$tag) {
            $out[sprintf("tag[%s]", $key)] = $tag;
        }
        return $out;
    }
}
