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

interface CheckpointInterface
{
    /**
     * Checkpoint after a user is logged in. Return false to deny persistence.
     *
     * @param \Walruship\Laratrust\Users\UserInterface $user
     *
     * @return bool
     */
    public function login(UserInterface $user);

    /**
     * Checkpoint for when a user is currently stored in the session.
     *
     * @param \Walruship\Laratrust\Users\UserInterface $user
     *
     * @return bool
     */
    public function check(UserInterface $user);

    /**
     * Checkpoint for when a failed login attempt is logged. User is not always
     * passed and the result of the method will not affect anything, as the
     * login failed.
     *
     * @param \Walruship\Laratrust\Users\UserInterface|null $user
     *
     * @return bool
     */
    public function fail(UserInterface $user = null);
}
