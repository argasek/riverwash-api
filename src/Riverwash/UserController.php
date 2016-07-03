<?php

namespace Riverwash;

use Maknz\Slack\Attachment;
use Maknz\Slack\Message;
use MHlavac\DiacriticsRemover\DiacriticsRemover;
use Riverwash\Model\User;
use Riverwash\Model\Session;
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

        $remover = new DiacriticsRemover();
        $handle = $remover->parse($handle);
        $group = $remover->parse($group);

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
            $text = ($language == 'pl' ? 'Nieudana komunikacja z API:' . print_r($exception->getAll(), true) : $exception->getMessage());
            $e = new Exception($text, 500);
            return $e->jsonSerialize();
        }

        try {
            $this->sendSlackRegistration($user);
        } catch (Exception $exception) {
            $text = $exception->getMessage();
            $e = new Exception($text, 500);
            return $e->jsonSerialize();
        }

        return $user;
    }

    /**
     * Sign-in existing user
     *
     * @param string $email E-mail
     * @param string $password Password
     * @param string $language Language
     *
     * @return array
     *
     * @throws Exception
     */
    public function postSignInEndpoint($email, $password, $language) {
        $language = filter_var(trim($language), FILTER_SANITIZE_STRING);
        $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);

        if ($email == '') {
            $text = ($language == 'pl' ? 'Musisz podać adres e-mail!' : 'E-mail field cannot be empty!');
            return new Exception($text, 500);
        }
        if ($password == '') {
            $text = ($language == 'pl' ? 'Hasło nie może być puste!' : 'Password field cannot be empty!');
            return new Exception($text, 500);
        }

        try {
            $response = $this->lunoRequester->users->login($email, $password);

            $session = new Session();
            $session->fromResponse($response);

        } catch (LunoApiException $exception) {
            $status = 500;
            if ($exception->getLunoCode() === 'incorrect_password') {
                $text = ($language == 'pl' ? 'Nieprawidłowe hasło' : $exception->getLunoDescription());
                $status = $exception->getLunoStatus();
            } else {
                $text = ($language == 'pl' ? 'Nieudana komunikacja z API' . print_r($exception, true) : $exception->getMessage());
            }
            $e = new Exception($text, $status);
            return $e->jsonSerialize();
        }

        $_SESSION['session'] = $session;

        $user = new User();
        $user->fromLunoUser($session->user);

        return [
            'success' => true,
            'nominationCasted' => $user->nominationCasted
        ];
    }

    /**
     * Nominate demoscener
     *
     * @param string $nickname Nickname
     * @param string $description Description
     * @param string $language Language
     *
     * @return array
     *
     * @throws Exception
     */
    public function postNominateEndpoint($nickname, $description, $language) {
        $language = filter_var(trim($language), FILTER_SANITIZE_STRING);

        $session = null;
        $sessionError = $this->getSessionError($language);

        if ($sessionError !== false) {
            return $sessionError;
        } else {
            $session = $this->getSession();
        }

        $nickname = filter_var(trim($nickname), FILTER_SANITIZE_STRING);
        $description = filter_var(trim($description), FILTER_SANITIZE_STRING);

        if ($nickname == '') {
            $text = ($language == 'pl' ? 'Musisz podać nickname!' : 'Nickname field cannot be empty!');
            return new Exception($text, 500);
        }
        if ($description == '') {
            $text = ($language == 'pl' ? 'Uzasadnienie nie może być puste!' : 'Reason field cannot be empty!');
            return new Exception($text, 500);
        }

        $user = new User();
        $user->fromLunoUser($session->user);

        if ($this->getUserNominationCasted($user)) {
            $text = ($language == 'pl' ? 'Można tylko raz nominować kandydata!' : 'You can nominate a candidate only once!');
            return new Exception($text, 500);
        }

        try {
            $this->setUserNominationCasted($user);
        } catch (LunoApiException $exception) {
            $status = 500;
            if ($exception->getLunoCode() === 'incorrect_password') {
                $text = ($language == 'pl' ? 'Nieprawidłowe hasło' : $exception->getLunoDescription());
                $status = $exception->getLunoStatus();
            } else {
                $text = $exception->getMessage();
            }
            $e = new Exception($text, $status);
            return $e->jsonSerialize();
        }

        try {
            $this->sendSlackNomination($user, $nickname, $description);
        } catch (Exception $exception) {
            $text = $exception->getMessage();
            $e = new Exception($text, 500);
            return $e->jsonSerialize();
        }

        return [ 'success' => true ];
    }

    private function sendSlackRegistration(User $user) {
        $message = $this->slack->createMessage();
        $message->setText('Na stronie zarejestrował się nowy użytkownik:');

        $this->slackAttachUserDataToMessage($message, $user);
        
        $this->slack->sendMessage($message);
    }

    private function sendSlackNomination(User $user, $nickname, $description) {
        $message = $this->slack->createMessage();
        $message->setText('Nowa nominacja:');

        $this->slackAttachNomineeDataToMessage($nickname, $description, $message);
        $this->slackAttachUserDataToMessage($message, $user);

        $this->slack->sendMessage($message);
    }

    /**
     * Check if session exists
     *
     * @return mixed
     */
    private function getSessionError($language) {
        if (!isset($_SESSION['session'])) {
            $status = 401;
            $text = ($language == 'pl' ? 'Musisz być zalogowany(-a), aby nominować!' : 'Must be authenticated to nominate!');
            $e = new Exception($text, $status);
            return $e->jsonSerialize();
        } else {
            return false;
        }

    }

    /**
     * Get stored session
     *
     * @return Session
     */
    private function getSession() {
        return $_SESSION['session'];
    }

    private function slackAttachUserDataToMessage(Message $message, User $user) {
        $attachment = new Attachment([
            'title' => 'Ksywa / Grupa',
            'text' => $user->getNicknameAndGroupAsString(),
            'color' => 'good'
        ]);
        $message->attach($attachment);

        $attachment = new Attachment([
            'title' => 'E-mail, kraj',
            'text' => $user->getEmailAndCountryAsString(),
            'color' => 'good'
        ]);
        $message->attach($attachment);
    }

    /**
     * @param string $nickname
     * @param string $description
     * @param Message $message
     */
    private function slackAttachNomineeDataToMessage($nickname, $description, Message $message) {
        $attachment = new Attachment([
            'title' => 'Kandydatura',
            'text' => $nickname,
            'color' => '#3AA3E3'
        ]);
        $message->attach($attachment);

        $attachment = new Attachment([
            'title' => 'Uzasadnienie',
            'text' => $description,
            'color' => '#2a76a5'
        ]);
        $message->attach($attachment);
    }

    private function setUserNominationCasted(User $user) {
        $this->lunoRequester->users->append($user->id, [
            'profile' => [
                'nominationCasted' => true
            ]
        ]);
    }

    private function getUserNominationCasted(User $user) {
        $lunoUser = $this->lunoRequester->users->find($user->id);
        $user->fromLunoUser($lunoUser);
        return $user->nominationCasted;
    }

}
