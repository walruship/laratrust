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

trait Hasher
{
    /**
     * The salt length.
     *
     * @var int
     */
    protected $saltLength = 22;

    /**
     * Create a random string for a salt.
     *
     * @return string
     */
    protected function createSalt()
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ./';

        $max = strlen($pool) - 1;

        $salt = '';

        for ($i = 0; $i < $this->saltLength; $i++) {
            $salt .= $pool[random_int(0, $max)];
        }

        return $salt;
    }

    /**
     * Compares two strings $a and $b in length-constant time.
     *
     * @param string $a
     * @param string $b
     *
     * @return bool
     */
    protected function slowEquals($a, $b)
    {
        $diff = strlen($a) ^ strlen($b);

        for ($i = 0; $i < strlen($a) && $i < strlen($b); $i++) {
            $diff |= ord($a[$i]) ^ ord($b[$i]);
        }

        return $diff === 0;
    }
}
