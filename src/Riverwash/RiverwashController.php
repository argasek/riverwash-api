<?php

namespace Riverwash;

use AyeAye\Api\Controller;
use Duffleman\Luno\LunoRequester;
use Maknz\Slack\Client;

class RiverwashController extends Controller {

    /**
     * Holds the Luno Requester.
     *
     * @var LunoRequester
     */
    protected $lunoRequester;

    /**
     * @var
     */
    protected $slack;

    /**
     * RiverwashController constructor.
     */
    public function __construct() {
        $this->lunoRequester = new LunoRequester([
            'sandbox' => getenv('LUNO_SANDBOX') === 'true',
            'key' => getenv('LUNO_KEY'),
            'secret' => getenv('LUNO_SECRET'),
            'timeout' => 10000,
        ]);

        $this->slack = new Client(getenv('SLACK_WEBHOOK'), [
            'username' => 'Pralka Rzeczna',
            'channel' => getenv('SLACK_CHANNEL'),
            'icon' => ':washing:',
            'link_names' => true
        ]);

    }
}