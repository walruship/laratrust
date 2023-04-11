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

namespace Walruship\Laratrust\Reminders;

use Walruship\Laratrust\Users\UserInterface;

interface ReminderRepositoryInterface
{
    /**
     * Create a new reminder record and code.
     *
     * @param \Walruship\Laratrust\Users\UserInterface $user
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(UserInterface $user);

    /**
     * Gets the reminder for the given user.
     *
     * @param \Walruship\Laratrust\Users\UserInterface $user
     * @param string                                   $code
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function get(UserInterface $user, $code = null);

    /**
     * Check if a valid reminder exists.
     *
     * @param \Walruship\Laratrust\Users\UserInterface $user
     * @param string                                   $code
     *
     * @return bool
     */
    public function exists(UserInterface $user, $code = null);

    /**
     * Complete reminder for the given user.
     *
     * @param \Walruship\Laratrust\Users\UserInterface $user
     * @param string                                   $code
     * @param string                                   $password
     *
     * @return bool
     */
    public function complete(UserInterface $user, $code, $password);

    /**
     * Remove expired reminder codes.
     *
     * @return bool
     */
    public function removeExpired();
}
