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

namespace Walruship\Laratrust\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Walruship\Laratrust\Laratrust
 */
class Laratrust extends Facade
{
    /**
     * @inheritdoc
     */
    protected static function getFacadeAccessor()
    {
        return 'laratrust';
    }
}
