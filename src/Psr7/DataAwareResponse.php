<?php

namespace CampaignMonitor\Psr7;

use GuzzleHttp\Psr7\Response;

class DataAwareResponse extends Response {

  /**
   * JSON cache for performance.
   *
   * @var array
   */
  private $json;

  /**
   * A non-standard PSR-7 response method that decodes raw response contents.
   */
  public function getData() {
    if ($this->json) {
      return $this->json;
    }

    // If JSON HTTP header detected, decode and return it.
    if (false !== strpos($this->getHeaderLine('Content-Type'), 'application/json')) {
      $body = (string) parent::getBody();
      return $this->json = \json_decode($body, TRUE);
    }

    return $body;
  }

}
