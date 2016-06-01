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
     * @param string $language Language
     *
     * @return array
     *
     * @throws Exception
     */
    public function postRegisterEndpoint($handle, $group, $country, $email, $password, $language) {
        $language = filter_var(trim($language), FILTER_SANITIZE_STRING);
        $handle = filter_var(trim($handle), FILTER_SANITIZE_STRING);
        $group = filter_var(trim($group), FILTER_SANITIZE_STRING);
        $country = filter_var(trim($country), FILTER_SANITIZE_STRING);
        $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);

        if ($handle == '') {
            $text = ($language == 'pl' ? 'Pole Ksywa nie może być puste!' : 'Handle field cannot be empty!');
            return new Exception($text, 500);
        }
        if ($email == '') {
            $text = ($language == 'pl' ? 'Musisz podać adres e-mail!' : 'E-mail field cannot be empty!');
            return new Exception($text, 500);
        }
        if ($password == '') {
            $text = ($language == 'pl' ? 'Hasło nie może być puste!' : 'Password field cannot be empty!');
            return new Exception($text, 500);
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
            $text = ($language == 'pl' ? 'Nieudana komunikacja z API' : $exception->getMessage());
            $e = new Exception($text, 500);
            return $e->jsonSerialize();
        }

        return $user;
    }

}
