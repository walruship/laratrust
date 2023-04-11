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

use Closure;

class CallbackHasher implements HasherInterface
{
    /**
     * The closure used for hashing a value.
     *
     * @var \Closure
     */
    protected $hash;

    /**
     * The closure used for checking a hashed value.
     *
     * @var \Closure
     */
    protected $check;

    /**
     * Constructor.
     *
     * @param \Closure $hash
     * @param \Closure $check
     *
     * @return void
     */
    public function __construct(\Closure $hash, \Closure $check)
    {
        $this->hash  = $hash;
        $this->check = $check;
    }

    /**
     * @inheritdoc
     */
    public function hash($value)
    {
        $callback = $this->hash;

        return $callback($value);
    }

    /**
     * @inheritdoc
     */
    public function check($value, $hashedValue)
    {
        $callback = $this->check;

        return $callback($value, $hashedValue);
    }
}
