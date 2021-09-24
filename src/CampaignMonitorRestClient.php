<?php

namespace CampaignMonitor;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Middleware;
use Psr\Http\Message\ResponseInterface;
use CampaignMonitor\Psr7\DataAwareResponse;

/**
 * CampaignMonitor Rest Client.
 */
class CampaignMonitorRestClient extends GuzzleHttpClient {

  /**
   * CampaignMonitor constructor.
   *
   * @param array $config
   *   Guzzle client configuration settings plus required `api_key` option for
   *   Campaign Monitor.
   */
  public function __construct(array $config = []) {
    if (empty($config['api_key'])) {
      throw new \Exception('Missing api_key config option.');
    }
    else {
      // Set auth headers in config.
      $config['headers']['Authorization'] = 'Basic ' . base64_encode($config['api_key'] . ':x');
    }

    if (empty($config['base_uri'])) {
      $config['base_uri'] = 'https://api.createsend.com/api/v3.2/';
    }
    elseif (strpos($config['base_uri'], 'https://api.createsend.com/api') !== 0) {
      throw new \Exception('base_uri config option must start with `https://api.createsend.com/api`.');
    }

    parent::__construct($config);

    // Add a data-aware middleware to automatically decode data format (e.g.
    // JSON) responses from the API.
    $handler = $this->getConfig('handler');
    $handler->push(Middleware::mapResponse(function (ResponseInterface $response) {
      return new DataAwareResponse(
        $response->getStatusCode(),
        $response->getHeaders(),
        $response->getBody(),
        $response->getProtocolVersion(),
        $response->getReasonPhrase()
      );
    }), 'data_decode_middleware');
  }

}
