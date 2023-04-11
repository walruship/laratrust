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

namespace Walruship\Laratrust\Hashing;

interface HasherInterface
{
    /**
     * Hash the given value.
     *
     * @param string $value
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public function hash($value);

    /**
     * Checks the string against the hashed value.
     *
     * @param string $value
     * @param string $hashedValue
     *
     * @return bool
     */
    public function check($value, $hashedValue);
}
