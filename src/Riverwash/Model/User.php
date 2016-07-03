<?php

namespace Riverwash\Model;


class User {
    public $handle;
    public $group;
    public $country;
    public $image;
    public $email;
    public $id;
    public $nominationCasted;

    public function fromLunoUser($lunoUser) {
        $profile = $lunoUser['profile'];
        
        $this->id = $lunoUser['id'];
        $this->email = $lunoUser['email'];

        $this->country = $profile['country'];
        $this->handle = $profile['handle'];
        $this->group = $profile['group'];
        $this->nominationCasted = isset($profile['nominationCasted']) && $profile['nominationCasted'] === true;

        $this->image = $this->getGravatarUrlFromEmail($lunoUser['email']);
    }

    private function getGravatarUrlFromEmail($email, $size = 0) {
        $default = 'wavatar';
        $sizeLimiter = ($size > 0 ? '&s=' . $size : '');

        return 'http://www.gravatar.com/avatar/' . md5(strtolower(trim($email))) . '?d=' . urlencode($default) . $sizeLimiter;

    }

    public function getNicknameAndGroupAsString() {
        return $this->handle . ($this->group != '' ? ' / ' . $this->group : '');
    }

    public function getEmailAndCountryAsString() {
        return sprintf("%s (%s)", $this->email, $this->country);
    }

}