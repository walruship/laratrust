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

namespace Walruship\Laratrust\Checkpoints;

use Walruship\Laratrust\Users\UserInterface;

class NotActivatedException extends \RuntimeException
{
    /**
     * The user which caused the exception.
     *
     * @var \Walruship\Laratrust\Users\UserInterface
     */
    protected $user;

    /**
     * Returns the user.
     *
     * @return \Walruship\Laratrust\Users\UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Sets the user associated with Laratrust (does not log in).
     *
     * @param \Walruship\Laratrust\Users\UserInterface $user
     *
     * @return void
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;
    }
}
