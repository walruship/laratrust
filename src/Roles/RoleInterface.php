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

interface RoleInterface
{
    /**
     * Returns the role's primary key.
     *
     * @return string
     */
    public function getRoleId();

    /**
     * Returns the role's slug.
     *
     * @return string
     */
    public function getRoleSlug();

    /**
     * Returns all users for the role.
     *
     * @return \IteratorAggregate
     */
    public function getUsers();

    /**
     * Returns the users model.
     *
     * @return string
     */
    public static function getUsersModel();

    /**
     * Sets the users model.
     *
     * @param string $usersModel
     *
     * @return void
     */
    public static function setUsersModel($usersModel);
}
