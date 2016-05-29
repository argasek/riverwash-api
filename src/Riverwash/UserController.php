<?php

namespace Riverwash;

use Riverwash\Model\User;
use AyeAye\Api\Exception;
use Duffleman\Luno\Exceptions\LunoApiException;

class UserController extends RiverwashController {
    /**
     * Register new user
     *
     * @param string $handle Nickname
     * @param string $group Group(s)
     * @param string $country Country
     * @param string $email E-mail
     * @param string $password Password
     *
     * @return array
     *
     * @throws Exception
     */
    public function postRegisterEndpoint($handle, $group, $country, $email, $password) {
        $handle = filter_var(trim($handle), FILTER_SANITIZE_STRING);
        $group = filter_var(trim($group), FILTER_SANITIZE_STRING);
        $country = filter_var(trim($country), FILTER_SANITIZE_STRING);
        $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);

        if ($handle == '') {
            return new Exception('Handle field cannot be empty!', 500);
        }
        if ($email == '') {
            return new Exception('E-mail field cannot be empty!', 500);
        }
        if ($password == '') {
            return new Exception('Password field cannot be empty!', 500);
        }

        try {
            $lunoUser = $this->lunoRequester->users->create([
                'username' => $email,
                'email'    => $email,
                'password' => $password,
                'profile'  => [
                    'handle' => $handle,
                    'group' => $group,
                    'country' => $country
                ]
            ]);

            $user = new User();
            $user->fromLunoUser($lunoUser);

        } catch (LunoApiException $exception) {
            $e = new Exception('User API request failed', 500);
            return $e->jsonSerialize();
        }

        return $user;
    }

}
