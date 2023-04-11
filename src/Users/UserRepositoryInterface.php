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

namespace Walruship\Laratrust\Users;

interface UserRepositoryInterface
{
    /**
     * Finds a user by the given primary key.
     *
     * @param int $id
     *
     * @return \Walruship\Laratrust\Users\UserInterface|null
     */
    public function findById($id);

    /**
     * Finds a user by the given credentials.
     *
     * @param array $credentials
     *
     * @return \Walruship\Laratrust\Users\UserInterface|null
     */
    public function findByCredentials($credentials);

    /**
     * Finds a user by the given persistence code.
     *
     * @param string $code
     *
     * @return \Walruship\Laratrust\Users\UserInterface|null
     */
    public function findByPersistenceCode($code);

    /**
     * Records a login for the given user.
     *
     * @param \Walruship\Laratrust\Users\UserInterface $user
     *
     * @return bool
     */
    public function recordLogin(UserInterface $user);

    /**
     * Records a logout for the given user.
     *
     * @param \Walruship\Laratrust\Users\UserInterface $user
     *
     * @return bool
     */
    public function recordLogout(UserInterface $user);

    /**
     * Validate the password of the given user.
     *
     * @param \Walruship\Laratrust\Users\UserInterface $user
     * @param array                                    $credentials
     *
     * @return bool
     */
    public function validateCredentials(UserInterface $user, array $credentials);

    /**
     * Validate if the given user is valid for creation.
     *
     * @param array $credentials
     *
     * @return bool
     */
    public function validForCreation(array $credentials);

    /**
     * Validate if the given user is valid for updating.
     *
     * @param int|\Walruship\Laratrust\Users\UserInterface $user
     * @param array                                        $credentials
     *
     * @return bool
     */
    public function validForUpdate($user, array $credentials);

    /**
     * Creates a user.
     *
     * @param array         $credentials
     * @param \Closure|null $callback
     *
     * @return \Walruship\Laratrust\Users\UserInterface|null
     */
    public function create(array $credentials, \Closure $callback = null);

    /**
     * Updates a user.
     *
     * @param int|\Walruship\Laratrust\Users\UserInterface $user
     * @param array                                        $credentials
     *
     * @return \Walruship\Laratrust\Users\UserInterface
     */
    public function update($user, array $credentials);
}
