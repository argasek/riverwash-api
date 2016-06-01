<?php

namespace Riverwash\Model;


class User {
    public $handle;
    public $group;
    public $country;
    public $image;

    public function fromLunoUser($lunoUser) {
        $this->country = $lunoUser['profile']['country'];
        $this->handle = $lunoUser['profile']['handle'];
        $this->group = $lunoUser['profile']['group'];
        $this->image = $this->getGravatarUrlFromEmail($lunoUser['email']);
    }

    private function getGravatarUrlFromEmail($email, $size = 0) {
        $default = 'wavatar';
        $sizeLimiter = ($size > 0 ? '&s=' . $size : '');

        return 'http://www.gravatar.com/avatar/' . md5(strtolower(trim($email))) . '?d=' . urlencode($default) . $sizeLimiter;

    }

}