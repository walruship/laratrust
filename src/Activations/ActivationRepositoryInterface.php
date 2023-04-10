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

namespace Walruship\Laratrust\Activations;

use Walruship\Laratrust\Users\UserInterface;

interface ActivationRepositoryInterface
{
    /**
     * Create a new activation record and code.
     *
     * @param \Walruship\Laratrust\Users\UserInterface $user
     *
     * @return \Walruship\Laratrust\Activations\ActivationInterface
     */
    public function create(UserInterface $user);

    /**
     * Gets the activation for the given user.
     *
     * @param \Walruship\Laratrust\Users\UserInterface $user
     * @param string                                   $code
     *
     * @return \Walruship\Laratrust\Activations\ActivationInterface|null
     */
    public function get(UserInterface $user, $code = null);

    /**
     * Checks if a valid activation for the given user exists.
     *
     * @param \Walruship\Laratrust\Users\UserInterface $user
     * @param string                                   $code
     *
     * @return bool
     */
    public function exists(UserInterface $user, $code = null);

    /**
     * Completes the activation for the given user.
     *
     * @param \Walruship\Laratrust\Users\UserInterface $user
     * @param string                                   $code
     *
     * @return bool
     */
    public function complete(UserInterface $user, $code);

    /**
     * Checks if a valid activation has been completed.
     *
     * @param \Walruship\Laratrust\Users\UserInterface $user
     *
     * @return bool
     */
    public function completed(UserInterface $user);

    /**
     * Remove an existing activation (deactivate).
     *
     * @param \Walruship\Laratrust\Users\UserInterface $user
     *
     * @return bool|null
     */
    public function remove(UserInterface $user);

    /**
     * Remove expired activation codes.
     *
     * @return bool
     */
    public function removeExpired();
}
