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

use Illuminate\Database\Eloquent\Model;
use Walruship\Laratrust\Users\EloquentUser;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class EloquentPersistence extends Model implements PersistenceInterface
{
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'persistences';

    /**
     * The Users model FQCN.
     *
     * @var string
     */
    protected static $usersModel = EloquentUser::class;

    /**
     * @inheritdoc
     */
    public function user()
    {
        return $this->belongsTo(static::$usersModel);
    }

    /**
     * Get the Users model FQCN.
     *
     * @return string
     */
    public static function getUsersModel()
    {
        return static::$usersModel;
    }

    /**
     * Set the Users model FQCN.
     *
     * @param string $usersModel
     *
     * @return void
     */
    public static function setUsersModel(string $usersModel)
    {
        static::$usersModel = $usersModel;
    }
}
