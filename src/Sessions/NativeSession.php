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

class NativeSession implements SessionInterface
{
    /**
     * The session key.
     *
     * @var string
     */
    protected $key = 'walruship_laratrust';

    /**
     * Constructor.
     *
     * @param string $key
     *
     * @return void
     */
    public function __construct(string $key = null)
    {
        $this->key = $key;

        $this->startSession();
    }

    /**
     * Called upon destruction of the native session handler.
     *
     * @return void
     */
    public function __destruct()
    {
        $this->writeSession();
    }

    /**
     * @inheritdoc
     */
    public function put($value)
    {
        $this->setSession($value);
    }

    /**
     * @inheritdoc
     */
    public function get()
    {
        return $this->getSession();
    }

    /**
     * @inheritdoc
     */
    public function forget()
    {
        $this->forgetSession();
    }

    /**
     * Starts the session if it does not exist.
     *
     * @return void
     */
    protected function startSession()
    {
        // Check that the session hasn't already been started
        if (session_status() != PHP_SESSION_ACTIVE && ! headers_sent()) {
            session_start();
        }
    }

    /**
     * Writes the session.
     *
     * @return void
     */
    protected function writeSession()
    {
        session_write_close();
    }

    /**
     * Un-serializes a value from the session and returns it.
     *
     * @return mixed
     */
    protected function getSession()
    {
        if (isset($_SESSION[$this->key])) {
            $value = $_SESSION[$this->key];

            if ($value) {
                return unserialize($value);
            }
        }
    }

    /**
     * Interacts with the $_SESSION global to set a property on it.
     * The property is serialized initially.
     *
     * @param mixed $value
     *
     * @return void
     */
    protected function setSession($value)
    {
        $_SESSION[$this->key] = serialize($value);
    }

    /**
     * Forgets the Laratrust session from the global $_SESSION.
     *
     * @return void
     */
    protected function forgetSession()
    {
        if (isset($_SESSION[$this->key])) {
            unset($_SESSION[$this->key]);
        }
    }
}
