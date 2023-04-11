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

namespace Walruship\Laratrust\Persistences;

interface PersistenceRepositoryInterface
{
    /**
     * Checks for a persistence code in the current session.
     *
     * @return string|null
     */
    public function check();

    /**
     * Finds a persistence by persistence code.
     *
     * @param string $code
     *
     * @return \Walruship\Laratrust\Persistences\PersistenceInterface|null
     */
    public function findByPersistenceCode(string $code);

    /**
     * Finds a user by persistence code.
     *
     * @param string $code
     *
     * @return \Walruship\Laratrust\Users\UserInterface|null
     */
    public function findUserByPersistenceCode(string $code);

    /**
     * Adds a new user persistence to the current session and attaches the user.
     *
     * @param \Walruship\Laratrust\Persistences\PersistableInterface $persistable
     * @param bool                                                   $remember
     *
     * @return bool|null
     */
    public function persist(PersistableInterface $persistable, $remember = false);

    /**
     * Adds a new user persistence, to remember.
     *
     * @param \Walruship\Laratrust\Persistences\PersistableInterface $persistable
     *
     * @return bool
     */
    public function persistAndRemember(PersistableInterface $persistable);

    /**
     * Removes the persistence bound to the current session.
     *
     * @return bool|null
     */
    public function forget();

    /**
     * Removes the given persistence code.
     *
     * @param string $code
     *
     * @return bool|null
     */
    public function remove($code);

    /**
     * Flushes persistences for the given user.
     *
     * @param \Walruship\Laratrust\Persistences\PersistableInterface $persistable
     * @param bool                                                   $forget
     *
     * @return void
     */
    public function flush(PersistableInterface $persistable, $forget = true);
}
