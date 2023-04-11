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

interface RoleRepositoryInterface
{
    /**
     * Finds a role by the given primary key.
     *
     * @param string $id
     *
     * @return \Walruship\Laratrust\Roles\RoleInterface|null
     */
    public function findById($id);

    /**
     * Finds a role by the given slug.
     *
     * @param string $slug
     *
     * @return \Walruship\Laratrust\Roles\RoleInterface|null
     */
    public function findBySlug($slug);

    /**
     * Finds a role by the given name.
     *
     * @param string $name
     *
     * @return \Walruship\Laratrust\Roles\RoleInterface|null
     */
    public function findByName($name);
}
