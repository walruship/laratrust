<?php

/*
 * Part of the Laratrust package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.
 *
 * @package    Laratrust
 * @version    7.0.0
 * @author     Walruship LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011-2023, Walruship LLC
 * @link       https://cartalyst.com
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
