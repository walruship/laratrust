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

namespace Walruship\Laratrust\Throttling;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class EloquentThrottle extends Model
{
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'throttle';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ip',
        'type',
    ];
}
