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

namespace Walruship\Laratrust\Persistences;

interface PersistableInterface
{
    /**
     * Returns the persistable key value.
     *
     * @return string
     */
    public function getPersistableId();

    /**
     * Returns the persistable key name.
     *
     * @return string
     */
    public function getPersistableKey();

    /**
     * Sets persistable.
     *
     * @param string $key
     *
     * @return string
     */
    public function setPersistableKey($key);

    /**
     * Returns the persistable relationship name.
     *
     * @return string
     */
    public function getPersistableRelationship();

    /**
     * Sets persistable relationship.
     *
     * @param $persistableRelationship
     *
     * @return mixed
     */
    public function setPersistableRelationship($persistableRelationship);

    /**
     * Generates a random persist code.
     *
     * @return string
     */
    public function generatePersistenceCode(): string;
}
