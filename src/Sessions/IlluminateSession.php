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

namespace Walruship\Laratrust\Sessions;

use Illuminate\Session\Store as SessionStore;

class IlluminateSession implements SessionInterface
{
    /**
     * The session store object.
     *
     * @var \Illuminate\Session\Store
     */
    protected $session;

    /**
     * The session key.
     *
     * @var string
     */
    protected $key = 'walruship_laratrust';

    /**
     * Constructor.
     *
     * @param \Illuminate\Session\Store $session
     * @param string                    $key
     *
     * @return void
     */
    public function __construct(SessionStore $session, string $key = null)
    {
        $this->session = $session;

        $this->key = $key;
    }

    /**
     * @inheritdoc
     */
    public function put($value)
    {
        $this->session->put($this->key, $value);
    }

    /**
     * @inheritdoc
     */
    public function get()
    {
        return $this->session->get($this->key);
    }

    /**
     * @inheritdoc
     */
    public function forget()
    {
        $this->session->forget($this->key);
    }
}
