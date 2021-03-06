<?php

namespace RemoteSystems;

class GoogleApps
{
    static public $apiToken;
    static public $domain;

    static public function executeRequest($path, $requestMethod = 'GET', $params = array(), $headers = array())
    {
        $url = 'https://www.googleapis.com' . $path;

        $params['domain'] = static::$domain;

        // confugre cURL
        $ch = curl_init();

        if ($requestMethod == 'GET') {
            $url .= '?' . (is_string($params) ? $params : http_build_query($params));
        } else {
            if ($requestMethod == 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
            } else {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $requestMethod);
            }

            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        }

        if (!empty($headers)) {
            $requestHeaders = array_merge($requestHeaders, $headers);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge(array(
            sprintf('Authorization: Bearer %s', static::$apiToken)
            ,'Content-Type: application/json'
        ), $headers));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $response = json_decode(curl_exec($ch), true);

        curl_close($ch);

        return $response;
    }

    static public function getAllResults($path, $resultsKey, $params = array())
    {
        if (!isset($params['maxResults'])) {
            $params['maxResults'] = 500;
        }

        if (isset($params['fields'])) {
            $params['fields'] .= ',nextPageToken';
        } else {
            $params['fields'] = 'nextPageToken';
        }

        $page = static::executeRequest($path, 'GET', $params);
        $results = $page[$resultsKey];

        while (!empty($page['nextPageToken'])) {
            $page = static::executeRequest($path, 'GET', array_merge($params, array(
                'pageToken' => $page['nextPageToken']
            )));

            $results = array_merge($results, $page[$resultsKey]);
        }

        return $results;
    }

    static public function getAllUsers($params = array())
    {
        return static::getAllResults('/admin/directory/v1/users', 'users', $params);
    }

    // Patch user: https://developers.google.com/admin-sdk/directory/v1/reference/users/patch
    static public function patchUser($userKey, $data)
    {
        return static::executeRequest("/admin/directory/v1/users/$userKey", 'PATCH', $data);
    }

    // Create user: https://developers.google.com/admin-sdk/directory/v1/reference/users/insert
    static public function createUser($data)
    {
        return static::executeRequest("/admin/directory/v1/users", 'POST', $data);
    }
}