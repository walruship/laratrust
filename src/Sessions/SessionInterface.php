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

namespace Walruship\Laratrust\Sessions;

interface SessionInterface
{
    /**
     * Put a value in the Laratrust session.
     *
     * @param mixed $value
     *
     * @return void
     */
    public function put($value);

    /**
     * Returns the Laratrust session value.
     *
     * @return mixed
     */
    public function get();

    /**
     * Removes the Laratrust session.
     *
     * @return void
     */
    public function forget();
}
