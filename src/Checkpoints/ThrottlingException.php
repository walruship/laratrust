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

namespace Walruship\Laratrust\Checkpoints;

use Carbon\Carbon;

class ThrottlingException extends \RuntimeException
{
    /**
     * The delay, in seconds.
     *
     * @var int
     */
    protected $delay = 0;

    /**
     * The throttling type which caused the exception.
     *
     * @var string
     */
    protected $type = '';

    /**
     * Returns the delay.
     *
     * @return int
     */
    public function getDelay()
    {
        return $this->delay;
    }

    /**
     * Sets the delay.
     *
     * @param int $delay
     *
     * @return $this
     */
    public function setDelay(int $delay)
    {
        $this->delay = $delay;

        return $this;
    }

    /**
     * Returns the type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the type.
     *
     * @param string $type
     *
     * @return $this
     */
    public function setType(string $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Returns a carbon object representing the time which the throttle is lifted.
     *
     * @return \Carbon\Carbon
     */
    public function getFree()
    {
        return Carbon::now()->addSeconds($this->delay);
    }
}
