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

namespace Walruship\Laratrust\Cookies;

interface CookieInterface
{
    /**
     * Put a value in the Laratrust cookie (to be stored until it's cleared).
     *
     * @param mixed $value
     *
     * @return void
     */
    public function put($value);

    /**
     * Returns the Laratrust cookie value.
     *
     * @return mixed
     */
    public function get();

    /**
     * Remove the Laratrust cookie.
     *
     * @return void
     */
    public function forget();
}
