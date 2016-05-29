<?php

namespace Riverwash;

use AyeAye\Api\Controller;
use Duffleman\Luno\LunoRequester;

class RiverwashController extends Controller {

    /**
     * Holds the Luno Requester.
     *
     * @var LunoRequester
     */
    protected $lunoRequester;

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

    }
}