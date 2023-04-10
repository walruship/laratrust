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

namespace Walruship\Laratrust\Throttling;

use Walruship\Laratrust\Users\UserInterface;

interface ThrottleRepositoryInterface
{
    /**
     * Returns the global throttling delay, in seconds.
     *
     * @return int
     */
    public function globalDelay();

    /**
     * Returns the IP address throttling delay, in seconds.
     *
     * @param string $ipAddress
     *
     * @return int
     */
    public function ipDelay($ipAddress);

    /**
     * Returns the throttling delay for the given user, in seconds.
     *
     * @param \Walruship\Laratrust\Users\UserInterface $user
     *
     * @return int
     */
    public function userDelay(UserInterface $user);

    /**
     * Logs a new throttling entry.
     *
     * @param string                                   $ipAddress
     * @param \Walruship\Laratrust\Users\UserInterface $user
     *
     * @return void
     */
    public function log($ipAddress = null, UserInterface $user = null);
}
