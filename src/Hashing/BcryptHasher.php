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

class BcryptHasher implements HasherInterface
{
    use Hasher;

    /**
     * The hash strength.
     *
     * @var int
     */
    public $strength = 8;

    /**
     * @inheritdoc
     */
    public function hash($value)
    {
        $salt = $this->createSalt();

        $strength = str_pad($this->strength, 2, '0', STR_PAD_LEFT);

        $prefix = '$2y$';

        return crypt($value, $prefix.$strength.'$'.$salt.'$');
    }

    /**
     * @inheritdoc
     */
    public function check($value, $hashedValue)
    {
        return $this->slowEquals(crypt($value, $hashedValue), $hashedValue);
    }
}
