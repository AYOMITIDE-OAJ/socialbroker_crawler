<?php

namespace App\Http\Controllers;

use DOMDocument;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class SearchUserController extends Controller
{
    private array $accounts_url;


    public function __construct()
    {
        $this->accounts_url = [
            'Tiktok' => ['url' => 'https://www.tiktok.com/@'],
            'Threads' => ['url' => 'https://www.threads.net/@'],
        ];
    }

    /**
     * @throws Throwable
     */
    public function test($username): JsonResponse
    {
        return $this->find_user($username);
    }

    /**
     * @throws Throwable
     */
    public function index(Request $request): JsonResponse
    {
        $username = $request->get('username');
        return $this->find_user($username);
    }

    /**
     * @throws Throwable
     */
    public function find_user($username): JsonResponse
    {
        $url_path = 'https://check-username.p.rapidapi.com/check/';
        $found = [];
        $errors = [];
        $client = new Client();
//        $headers = [
//            'X-RapidAPI-Host' => 'check-username.p.rapidapi.com',
//            'X-RapidAPI-Key' => '8284cb5b7amshcd09f2e9cca0928p1fa537jsn4413954fae5c',
//        ];
//        'X-RapidAPI-Key' => '79ab8a074cmshbb3f96c1977787ap17bc71jsn2ce1c5f2ce03',

//        echo $response->getBody();
        $promises = [];

//        try {
//            $prom = $client->get($url_path.$username, ['headers' => $headers]);
//            $body = json_decode($prom->getBody(), true);
//            dd($body);
//            if ($body){
//                foreach ($body as $key => $item){
//                    if ($key != 'success' && $key != 'username' && $key != 'github' && !$item){
//                        $found[] = $key;
//                    }
//                }
//            }
//        } catch (Exception $exception){
//            dd($exception);
//        }

        foreach ($this->accounts_url as $key => $url) {
            $promises[$key] = $client->getAsync($url['url'].$username);
        }
        $results = Promise\Utils::unwrap($promises);
        foreach ($results as $url => $result) {
            if ($result->getStatusCode() === 200) {
                $response = $result->getBody()->getContents();
                $dom = new DOMDocument();
                libxml_use_internal_errors(true);
                $dom->loadHTML($response);
                $titleTags = $dom->getElementsByTagName('title');

                if ($url == 'Tiktok') {
                    $scriptTag = $dom->getElementById('__UNIVERSAL_DATA_FOR_REHYDRATION__');
                    if ($scriptTag) {
                        $status = json_decode($scriptTag->nodeValue, true)['__DEFAULT_SCOPE__']['webapp.user-detail']['statusMsg'];
                        if ($status == 'ok') {
                            $found[] = $url;
                        }
                    }
                } else {
                    if ($url == 'Reddit') {
                        $scriptTags = $dom->getElementsByTagName('span');
                        $not_found = false;
                        foreach ($scriptTags as $scriptTag) {
                            if ($scriptTag->nodeValue === 'Sorry, nobody on Reddit goes by that name.') {
                                $not_found = true;
                            }
                        }
                        if (!$not_found) {
                            $found[] = $url;
                        }

                    } else {
                        if ($titleTags->length > 0) {
                            $title = $titleTags->item(0)->textContent;
                            if (!str_starts_with($title, $url)) {
                                $found[] = $url;
                            }
                        }
                    }
                }
            } else {
                $reason = $result['reason'];
                return response()->json(['error' => "Request to $url failed with error: $reason\n"]);
            }
        }

        if ($found) {
            return response()->json(['status' => 0, 'social' => $found, 'errors'=> $errors]);
        }
        return response()->json(['status' => 1]);
    }
}