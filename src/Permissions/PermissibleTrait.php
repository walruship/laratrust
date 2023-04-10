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

namespace Walruship\Laratrust\Permissions;

trait PermissibleTrait
{
    /**
     * The cached permissions instance for the given user.
     *
     * @var \Walruship\Laratrust\Permissions\PermissionsInterface
     */
    protected $permissionsInstance;

    /**
     * The permissions instance FQCN.
     *
     * @var string
     */
    protected static $permissionsClass = StrictPermissions::class;

    /**
     * Returns the permissions.
     *
     * @return array
     */
    public function getPermissions()
    {
        return $this->permissions ?? [];
    }

    /**
     * Sets permissions.
     *
     * @param array $permissions
     *
     * @return $this
     */
    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;

        return $this;
    }

    /**
     * Returns the permissions class name.
     *
     * @return string
     */
    public static function getPermissionsClass()
    {
        return static::$permissionsClass;
    }

    /**
     * Sets the permissions class name.
     *
     * @param string $permissionsClass
     *
     * @return void
     */
    public static function setPermissionsClass($permissionsClass)
    {
        static::$permissionsClass = $permissionsClass;
    }

    /**
     * Creates the permissions object.
     *
     * @return $this
     */
    abstract protected function createPermissions();

    /**
     * @inheritdoc
     */
    public function getPermissionsInstance()
    {
        if ($this->permissionsInstance === null) {
            $this->permissionsInstance = $this->createPermissions();
        }

        return $this->permissionsInstance;
    }

    /**
     * @inheritdoc
     */
    public function addPermission($permission, $value = true)
    {
        if (! array_key_exists($permission, $this->getPermissions())) {
            $this->permissions = array_merge($this->getPermissions(), [$permission => $value]);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function updatePermission($permission, $value = true, $create = false)
    {
        if (array_key_exists($permission, $this->getPermissions())) {
            $permissions = $this->getPermissions();

            $permissions[$permission] = $value;

            $this->permissions = $permissions;
        } elseif ($create) {
            $this->addPermission($permission, $value);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removePermission($permission)
    {
        if (array_key_exists($permission, $this->getPermissions())) {
            $permissions = $this->getPermissions();

            unset($permissions[$permission]);

            $this->permissions = $permissions;
        }

        return $this;
    }
}
