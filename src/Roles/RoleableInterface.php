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

namespace Walruship\Laratrust\Roles;

interface RoleableInterface
{
    /**
     * Returns all the associated roles.
     *
     * @return \IteratorAggregate
     */
    public function getRoles();

    /**
     * Checks if the user is in the given role.
     *
     * @param mixed $role
     *
     * @return bool
     */
    public function inRole($role);

    /**
     * Checks if the user is in any of the given roles.
     *
     * @param array $roles
     *
     * @return bool
     */
    public function inAnyRole(array $roles);
}
