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

class WhirlpoolHasher implements HasherInterface
{
    use Hasher;

    /**
     * @inheritdoc
     */
    public function hash($value)
    {
        $salt = $this->createSalt();

        return $salt.hash('whirlpool', $salt.$value);
    }

    /**
     * @inheritdoc
     */
    public function check($value, $hashedValue)
    {
        $salt = substr($hashedValue, 0, $this->saltLength);

        return $this->slowEquals($salt.hash('whirlpool', $salt.$value), $hashedValue);
    }
}
