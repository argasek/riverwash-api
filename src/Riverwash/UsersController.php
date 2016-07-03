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
            array_push($users, [
                'handle' => $user->handle,
                'group' => $user->group,
                'country' => $user->country,
                'image' => $user->image
            ]);
        };
        return $users;
    }

    /**
     * lol...
     * @return $this
     */
    public function userController() {
        return new UserController();
    }
}
