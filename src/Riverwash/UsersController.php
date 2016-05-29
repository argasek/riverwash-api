<?php

namespace Riverwash;

use Riverwash\Model\User;

class UsersController extends RiverwashController {
    /**
     * List registered users
     * @return array
     */
    public function getUsersEndpoint() {
        $users = [];
        $lunoUsersIterator = $this->lunoRequester->users->all();
        foreach ($lunoUsersIterator as $lunoUser) {
            $user = new User();
            $user->fromLunoUser($lunoUser);
            array_push($users, $user);
        };
        return $users;
    }

    /**
     * lol...
     * @return $this
     */
    public function riverwashController() {
        return $this;
    }
}
