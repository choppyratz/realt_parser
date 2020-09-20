<?php

namespace App\Parsers;

use App\Parsers\iParser;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use App\Models\Poster;
use App\Models\Image;


class RealtParser implements iParser
{
    private $baseURI = 'https://realt.by/rent/flat-for-day/?page=';
    private $startPage = 0;
    private $maxPage = 2;

    public function run($startPage) {
        if (!is_null($startPage)) {
            $this->startPage = $startPage - 1;
        }

        while (true) {
            if ($this->startPage > $this->maxPage && $this->maxPage != -1) {
                break;
            }

            $result = $this->getPageContent($this->baseURI . $this->startPage);

            if ($result['success']) {
                $content = $result['result'];
                foreach ($this->getPostersLinksFromPage($content) as $posterUrl) {
                    $posterResult = $this->getPageContent($posterUrl);
                    $pageInfo = $this->getPosterInfoFromPage($posterResult['result'], $posterUrl);

                    $poster = Poster::updateOrCreate(['code' => $pageInfo['poster']['code']], $pageInfo['poster']);

                    $pageInfo['images'] = array_map(function($item) use($poster){
                        return [
                            'link' => $item,
                            'poster_id' => $poster->id
                        ];
                    }, $pageInfo['images']);

                    foreach ($pageInfo['images'] as $image) {
                        Image::updateOrCreate([
                            'link' => $image['link'],
                            'poster_id' => $image['poster_id']
                        ]);
                    }
                    var_dump($poster->id);
                }
            }else {
                break;
            }
            $this->startPage++;
        }
    }

    private function getPageContent($url) {
        $result = [];
        $result['success'] = true;

        $client = new Client();

        $response = $client->request(
            'GET',
            $url,
            [
                'http_errors' => false,
                'connect_timeout' => 10,
                'verify' => false,
                'curl' => [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1'
                ]
            ]
        );

        if ($response->getStatusCode() == 404) {
            $result['success'] = false;
        }
        $result['result'] = $response->getBody()->getContents();
        return $result;
    }

    private function getPostersLinksFromPage($content) {
        preg_match_all("/https:\/\/realt.by\/rent\/flat-for-day\/object\/\d+\//i", $content, $result);
        return array_unique($result[0]);
    }

    private function getPosterInfoFromPage($content, $url) {
        $poster = [];

        preg_match("/(?<=<h1 class=\"f24\">).*(?=<\/h1>)/i", $content, $result);
        if (!empty($result)) {
            $poster['name'] = $result[0];
        }else {
            $poster['name'] = "";
        }

        preg_match("/(?<=<div class=\"description\">).*(?=<div class=\"table-zebra\">)/i", $content, $result);
        if (!empty($result)) {
            $poster['description'] = trim(strip_tags(html_entity_decode($result[0])));
        }else {
            $poster['description'] = "";
        }

        $poster['link'] = $url;

        preg_match("/<p class=\"f14\">.*<\/span>/i", $content, $result);
        if (!empty($result)) {
            $poster['price'] = intval(strip_tags($result[0]));
        }else {
            $poster['price'] = "";
        }

        preg_match("/\d+/i", $url, $result);
        if (!empty($result)) {
            $poster['code'] = $result[0];
        }else {
            $poster['code'] = "";
        } 

        preg_match(
            "/(?<=<tr class=\"table-row odd\"> <td class=\"table-row-left\" > Дата обновления <\/td> <td class=\"table-row-right\"> )\d\d\d\d-\d\d-\d\d(?= <\/td> <\/tr>)/i", 
            $content, $result
        );
        if (!empty($result)) {
            $poster['update_date'] = $result[0];
        }else {
            $poster['update_date'] = "";
        }

        preg_match(
            "/(?<=<tr class=\"table-row odd\"> <td class=\"table-row-left\" > Контактное лицо <\/td> <td class=\"table-row-right\"> ).*(?= <\/td> <\/tr>)/i", 
            $content, $result
        );
        if (!empty($result)) {
            $temp_arr = explode(' ', strip_tags($result[0]));
            $temp_arr = array_filter($temp_arr, function($element) {
                return !empty($element);
            });
            $temp_arr = array_values($temp_arr);

            $poster['contact_face'] = $temp_arr[0];

            if (isset($temp_arr[2]))
                $poster['email'] = str_replace('(собачка)', '@', $temp_arr[2]);
            else {
                $poster['email'] = "";
            }
        }else {
            $poster['contact_face'] = "";
            $poster['email'] = "";
        }

        preg_match(
            "/<td class=\"table-row-left\" > Адрес <\/td> <td class=\"table-row-right\">.*?<\/td>/i", 
            $content, $result
        );
        if (!empty($result)) {
            $temp_arr = explode(' ', strip_tags($result[0]));
            $temp_arr = array_filter($temp_arr, function($element) {
                return !empty($element);
            });
            $temp_arr = array_values($temp_arr);
            if (isset($temp_arr[1]) && isset($temp_arr[2]) && isset($temp_arr[3])) {
                $poster['adress'] = $temp_arr[1] . ' ' .  $temp_arr[2] . $temp_arr[3];
            }
            else {
                $poster['adress'] = "";
            }
        }else {
            $poster['adress'] = "";
        }

        preg_match(
            "/<td class=\"table-row-left\" > Населенный пункт <\/td> <td class=\"table-row-right\">.*?<\/td>/i", 
            $content, $result
        );
        if (!empty($result)) {
            $poster['city'] = trim(str_replace('Населенный пункт', '', strip_tags($result[0])));
        }else {
            $poster['city'] = "";
        }

        preg_match(
            "/<td class=\"table-row-left\" > Область <\/td> <td class=\"table-row-right\">.*?<\/td>/i", 
            $content, $result
        );
        if (!empty($result)) {
            $poster['region'] = trim(str_replace('Область', '', strip_tags($result[0])));
        }else {
            $poster['region'] = "";
        }

        preg_match_all(
            "/(?<=<div class=\"photo-item\"> <a href=\").*?\"(?=.*<\/a> <\/div>)/i", 
            $content, $result
        );

        $images = array_map(function($item){
            return str_replace('"', '', $item);
        }, $result[0]);


        $poster['price_id'] = 1;

        return [
            'poster' => $poster,
            'images' => $images
        ];
    }
}