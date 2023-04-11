<?php
/*
 * Walruship Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the 3-clause BSD license that is
 * available through the world-wide-web at this URL:
 * https://walruship.com/LICENSE.txt
 *
 * @author          Walruship Co., Ltd
 * @copyright       Copyright (c) 2023 Walruship
 * @link            https://walruship.com
 */

namespace Walruship\Laratrust\Cookies;

use Illuminate\Http\Request;
use Illuminate\Cookie\CookieJar;

class IlluminateCookie implements CookieInterface
{
    /**
     * The current request.
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * The cookie object.
     *
     * @var \Illuminate\Cookie\CookieJar
     */
    protected $jar;

    /**
     * The cookie key.
     *
     * @var string
     */
    protected $key = 'walruship_laratrust';

    /**
     * Constructor.
     *
     * @param \Illuminate\Http\Request     $request
     * @param \Illuminate\Cookie\CookieJar $jar
     * @param string                       $key
     *
     * @return void
     */
    public function __construct(Request $request, CookieJar $jar, $key = null)
    {
        $this->request = $request;

        $this->jar = $jar;

        if (isset($key)) {
            $this->key = $key;
        }
    }

    /**
     * @inheritdoc
     */
    public function put($value)
    {
        $cookie = $this->jar->forever($this->key, $value);

        $this->jar->queue($cookie);
    }

    /**
     * @inheritdoc
     */
    public function get()
    {
        $key = $this->key;

        // Cannot use $this->jar->queued($key, function()) because it's not
        // available in 4.0.*, only 4.1+
        $queued = $this->jar->getQueuedCookies();

        if (isset($queued[$key])) {
            return $queued[$key]->getValue();
        }

        return $this->request->cookie($key);
    }

    /**
     * @inheritdoc
     */
    public function forget()
    {
        $cookie = $this->jar->forget($this->key);

        $this->jar->queue($cookie);
    }
}
