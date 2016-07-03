<?php

namespace Riverwash\Model;


class Session {
    private $type;
    private $id;
    private $url;
    private $key;
    private $created;
    private $expires;
    private $lastAccess;
    private $accessCount;
    private $ip;
    private $userAgent;
    private $details;
    public $user;

    public function fromResponse($response) {
        $session = $response['session'];
        $user = $response['user'];

        $this->type = $session['type'];
        $this->id = $session['id'];
        $this->url = $session['url'];
        $this->key = $session['key'];
        $this->created = $session['created'];
        $this->expires = $session['expires'];
        $this->lastAccess = $session['last_access'];
        $this->accessCount = $session['access_count'];
        $this->ip = $session['ip'];
        $this->userAgent = $session['user_agent'];
        $this->details = $session['details'];
        $this->user = $user;
    }

}