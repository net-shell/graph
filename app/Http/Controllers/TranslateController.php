<?php namespace App\Http\Controllers;

use Exception;
use Guzzle\Http\Client;
use Guzzle\Http\Request;
use Illuminate\Http\Request as Input;

class TranslateController extends Controller {

	private $apiUrl = 'https://api.datamarket.azure.com/Bing/MicrosoftTranslator/Translate';

	public function getIndex(Client $http, Input $input) {
		$apiKey = env('MS_TRANSLATE_KEY');

		if(!$apiKey) {
			throw new Exception('API key is missing', 1);
		}

		$apiKey = base64_encode("accountKey:$apiKey");

		$params = $input->only('Text', 'To', 'From');

		if(!$params['Text']) {
			throw new Exception('Missing "Text" parameter', 1);
		}
		if(!$params['To']) {
			throw new Exception('Missing "To" parameter', 1);
		}
		$params = array_map(function($p) { if($p) return "'$p'"; }, $params);

		$url = $this->apiUrl . '?' . http_build_query($params);
		
		$request = $http->get($url, [
			'Authorization' => 'Basic '. $apiKey,
			'Content-type' => 'application/json',
			'Accept' => 'application/json'
		]);

		$response = $request->send();

		$body = (string)$response->getBody();

		$data = json_decode($body);
		
		if(!isset($data->d->results) || !count($data->d->results)) {
			throw new Exception('Bad results received from the remote API', 1);
		}

		$results = array_map(function($t) { if(isset($t->Text)) return $t->Text; }, $data->d->results);

		return $results;
	}
}