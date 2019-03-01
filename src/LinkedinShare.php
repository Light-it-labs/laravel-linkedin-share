<?php

namespace Lightit\LinkedinShare;

use Exception;
use GuzzleHttp\Client;

class LinkedinShare
{
    private $redirect_uri;
    private $client_id;
    private $client_secret;

    public function __construct()
    {
        $i = func_num_args();
        if ($i != 3) {
            throw new LinkedinShareException('Invalid arguments. Use REDIRECT_URL, CLIENT_ID and CLIENT_SECRET'.$i);
        }

        $this->redirect_uri = func_get_arg(0);
        $this->client_id = func_get_arg(1);
        $this->client_secret = func_get_arg(2);
    }

    public function getAccessToken($code)
    {
        $client = new Client();
        $response = $client->request('POST', 'https://www.linkedin.com/oauth/v2/accessToken', [
            'form_params' => [
                'grant_type'    => 'authorization_code',
                'code'          => $code,
                'redirect_uri'  => $this->redirect_uri,
                'client_id'     => $this->client_id,
                'client_secret' => $this->client_secret,
            ],
        ]);

        $object = json_decode($response->getBody()->getContents(), true);
        $access_token = $object['access_token'];

        return $access_token;
    }

    public function getProfile($access_token)
    {
        $client = new Client();
        $response = $client->request('GET', 'https://api.linkedin.com/v2/me', [
            'headers' => [
                'Authorization' => 'Bearer '.$access_token,
                'Connection'    => 'Keep-Alive',
            ],
        ]);
        $object = json_decode($response->getBody()->getContents(), true);

        return $object;
    }

    private function registerUpload($access_token, $personURN)
    {
        $client = new Client();

        $response = $client->request('POST', 'https://api.linkedin.com/v2/assets?action=registerUpload', [
            'headers' => [
                'Authorization' => 'Bearer '.$access_token,
                'Connection'    => 'Keep-Alive',
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'registerUploadRequest' => [
                    'recipes' => [
                        'urn:li:digitalmediaRecipe:feedshare-image',
                    ],
                    'owner'                => 'urn:li:person:'.$personURN,
                    'serviceRelationships' => [
                        [
                            'relationshipType' => 'OWNER',
                            'identifier'       => 'urn:li:userGeneratedContent',
                        ],
                    ],
                ],
            ],
        ]);
        $object = json_decode($response->getBody()->getContents(), true);

        return $object;
    }

    private function uploadImage($url, $access_token, $image)
    {
        $client = new Client();
        $client->request('PUT', $url, [
                'headers' => [
                    'Authorization' => 'Bearer '.$access_token,
                ],
                'body' => fopen($image, 'r'),

            ]
        );
    }

    public function shareImage($code, $image, $text, $access_type = 'code')
    {
        $client = new Client();
        $access_token = ($access_type === 'code') ? $this->getAccessToken($code) : $code;
        $personURN = $this->getProfile($access_token)['id'];
        $uploadObject = $this->registerUpload($access_token, $personURN);
        $asset = $uploadObject['value']['asset'];
        $uploadUrl = $uploadObject['value']['uploadMechanism']['com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest']['uploadUrl'];
        $this->uploadImage($uploadUrl, $access_token, $image);

        $client->request('POST', 'https://api.linkedin.com/v2/ugcPosts', [
            'headers' => [
                'Authorization'             => 'Bearer '.$access_token,
                'Connection'                => 'Keep-Alive',
                'Content-Type'              => 'application/json',
                'X-Restli-Protocol-Version' => '2.0.0',
            ],
            'json' => [
                'author'          => 'urn:li:person:'.$personURN,
                'lifecycleState'  => 'PUBLISHED',
                'specificContent' => [
                    'com.linkedin.ugc.ShareContent' => [
                        'shareCommentary' => [
                            'text' => $text,
                        ],
                        'shareMediaCategory' => 'IMAGE',
                        'media'              => [
                            [
                                'status' => 'READY',
                                //"originalUrl" => "https://linkedin.com/",
                                'media' => $asset,

                            ],
                        ],
                    ],
                ],
                'visibility' => [
                    'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC',
                ],
            ],
        ]);
    }

    public function shareArticle($code, $url, $text, $access_type = 'code')
    {
        $client = new Client();
        $access_token = ($access_type === 'code') ? $this->getAccessToken($code) : $code;
        $personURN = $this->getProfile($access_token)['id'];

        $client->request('POST', 'https://api.linkedin.com/v2/ugcPosts', [
            'headers' => [
                'Authorization'             => 'Bearer '.$access_token,
                'Connection'                => 'Keep-Alive',
                'Content-Type'              => 'application/json',
                'X-Restli-Protocol-Version' => '2.0.0',
            ],
            'json' => [
                'author'          => 'urn:li:person:'.$personURN,
                'lifecycleState'  => 'PUBLISHED',
                'specificContent' => [
                    'com.linkedin.ugc.ShareContent' => [
                        'shareCommentary' => [
                            'text' => $text,
                        ],
                        'shareMediaCategory' => 'ARTICLE',
                        'media'              => [
                            [
                                'status'      => 'READY',
                                'originalUrl' => $url,

                            ],
                        ],
                    ],
                ],
                'visibility' => [
                    'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC',
                ],
            ],
        ]);
    }

    public function shareNone($code, $text, $access_type = 'code')
    {
        $client = new Client();
        $access_token = ($access_type === 'code') ? $this->getAccessToken($code) : $code;
        $personURN = $this->getProfile($access_token)['id'];

        $client->request('POST', 'https://api.linkedin.com/v2/ugcPosts', [
            'headers' => [
                'Authorization'             => 'Bearer '.$access_token,
                'Connection'                => 'Keep-Alive',
                'Content-Type'              => 'application/json',
                'X-Restli-Protocol-Version' => '2.0.0',
            ],
            'json' => [
                'author'          => 'urn:li:person:'.$personURN,
                'lifecycleState'  => 'PUBLISHED',
                'specificContent' => [
                    'com.linkedin.ugc.ShareContent' => [
                        'shareCommentary' => [
                            'text' => $text,
                        ],
                        'shareMediaCategory' => 'NONE',
                    ],
                ],
                'visibility' => [
                    'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC',
                ],
            ],
        ]);
    }
}

class LinkedinShareException extends Exception
{
    public function __construct($message, $code = 500, Exception $previous = null)
    {
        // Default code 500
        parent::__construct($message, $code, $previous);
    }
}
