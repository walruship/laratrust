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

use Illuminate\Database\Eloquent\Model;
use Walruship\Laratrust\Users\EloquentUser;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Walruship\Laratrust\Permissions\PermissibleTrait;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Walruship\Laratrust\Permissions\PermissibleInterface;

class EloquentRole extends Model implements PermissibleInterface, RoleInterface
{
    use HasUuids, PermissibleTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'roles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'permissions',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'permissions' => 'json',
    ];

    /**
     * The Users model FQCN.
     *
     * @var string
     */
    protected static $usersModel = EloquentUser::class;

    /**
     * @inheritdoc
     */
    public function delete()
    {
        if ($this->exists && (! method_exists(static::class, 'isForceDeleting') || $this->isForceDeleting())) {
            $this->users()->detach();
        }

        return parent::delete();
    }

    /**
     * The Users relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(static::$usersModel, 'role_users', 'role_id', 'user_id')->withTimestamps();
    }

    /**
     * @inheritdoc
     */
    public function getRoleId(): int
    {
        return $this->getKey();
    }

    /**
     * @inheritdoc
     */
    public function getRoleSlug(): string
    {
        return $this->slug;
    }

    /**
     * @inheritdoc
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @inheritdoc
     */
    public static function getUsersModel()
    {
        return static::$usersModel;
    }

    /**
     * @inheritdoc
     */
    public static function setUsersModel($usersModel)
    {
        static::$usersModel = $usersModel;
    }

    /**
     * Dynamically pass missing methods to the permissions.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $methods = ['hasAccess', 'hasAnyAccess'];

        if (in_array($method, $methods)) {
            $permissions = $this->getPermissionsInstance();

            return call_user_func_array([$permissions, $method], $parameters);
        }

        return parent::__call($method, $parameters);
    }

    /**
     * @inheritdoc
     */
    protected function createPermissions()
    {
        return new static::$permissionsClass($this->getPermissions());
    }
}
