# Campaign Monitor REST API Client for PHP

A simple PHP http client for the Campaign Monitor REST API. Optimized for JSON and authentication via API key. Based on [Guzzle][].

`CampaignMonitorRestClient` is an extended `GuzzleHttp\Client`, so it can do all the things that Guzzle can and a bit more.

Be sure to refer to the [Campaign Monitor API documentation][] for details.

## Installation

Via composer:

```
composer require ilrwebservices/campaign-monitor-rest-api-client
```

## Usage

```php
<?php

require __DIR__ . '/../vendor/autoload.php';

use CampaignMonitor\CampaignMonitorRestClient;

$client = new CampaignMonitorRestClient([
  'api_key' => $_ENV['CAMPAIGN_MONITOR_API_KEY'],
]);
```

Note how the API key is passed into the client constructor options along with other Guzzle-compatible options. In this example, the API key is stored in an environment variable since it is sensitive information that should not be stored in code.

The `CampaignMonitorRestClient` will set the `base_uri` option to `https://api.createsend.com/api/v3.2/` automatically. To use a different base uri (for example for a different API version), you can specify it explicitly:

```php
$client = new CampaignMonitorRestClient([
  'api_key' => $_ENV['CAMPAIGN_MONITOR_API_KEY'],
  'base_uri' => 'https://api.createsend.com/api/v3.1/',
]);
```

You can now make API calls just as you would in Guzzle:

```php
$client_response = $client->request('GET', 'https://api.createsend.com/api/v3.2/clients.json');
```

Since the `base_uri` option is pre-configured, you can use a relative URL (be sure not to add a leading `/`, though):

```php
$client_response = $client->request('GET', 'clients.json');
```

And because it's Guzzle, there are shorthand methods:

```php
$client_response = $client->get('clients.json');
```

Responses are [PSR-7] response messages, just like Guzzle, so you can get returned data via `getBody()`:

```php
$client_data_raw = (string) $client_response->getBody();

// Returns:
// [{"ClientID":"86753099713df60ae6545c9b338e3210","Name":"BungeeMan Enterprises"}]
```

For `application/json` responses, `CampaignMonitorRestClient` adds a non-standard `getData()` method that decodes the JSON response for you:

```php
$client_data = $client_response->getData();

// Returns:
// Array
// (
//     [0] => Array
//         (
//             [ClientID] => 86753099713df60ae6545c9b338e3210
//             [Name] => BungeeMan Enterprises
//         )
// )
```

Be careful with large responses, however, as they can consume too much memory.

Sending JSON data to API endpoints can be done using the `json` request option from Guzzle:

```php
$new_client_response = $client->post('clients.json', [
  'json' => [
    'CompanyName': 'Cyberdyne Systems',
    'Country': "Australia",
    'TimeZone': "(GMT+10:00) Canberra, Melbourne, Sydney"
  ],
]);
```

### Error handling

You can use [Guzzle exceptions][] for error handling. Campaign Montior endpoints generally use 4xx codes for errors, so `GuzzleHttp\Exception\ClientException` is a good match for catching those errors.

```php
try {
  $new_client_response = $client->post('clients.json', [
    'json' => [
      'CompanyName' => 'Cyberdyne Systems',
      'Country' => 'Australia',
      'TimeZone' => '(GMT+10:00) Canberra, Melbourne, Sydney',
    ],
  ]);
}
// Could not connect to server or other network issue.
catch (\GuzzleHttp\Exception\ConnectException $e) {
  print_r($e->getMessage());
}
// 4xx error. This is the bulk of Campaign Monitor API errors, and details about
// the error can be found in the error message and response data.
catch (\GuzzleHttp\Exception\ClientException $e) {
  print_r($e->getResponse()->getData());
  print_r($e->getMessage());
}
// 5xx error. This is an 'unhandled API error' in Campaign Monitor.
catch (\GuzzleHttp\Exception\ServerException $e) {
  print_r($e->getResponse()->getData());
  print_r($e->getMessage());
}
```

If the above request to create a new client failed, a `GuzzleHttp\Exception\ClientException` would be thrown and the following output displayed:

```
Array
(
    [Code] => 51
    [Message] => Not allowed for a Non-agency Customer.
)
Client error: `POST https://api.createsend.com/api/v3.2/clients.json` resulted in a `403 Forbidden` response:
{"Code":51,"Message":"Not allowed for a Non-agency Customer."}
```

Inspired by [drewm/mailchimp-api][].


[Guzzle]: https://github.com/guzzle/guzzle
[Campaign Monitor API documentation]: https://www.campaignmonitor.com/api/
[PSR-7]: https://www.php-fig.org/psr/psr-7/
[Guzzle exceptions]: https://docs.guzzlephp.org/en/stable/quickstart.html#exceptions
[drewm/mailchimp-api]: https://github.com/drewm/mailchimp-api
