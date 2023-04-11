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

namespace Walruship\Laratrust\Roles;

use Walruship\Support\Traits\RepositoryTrait;

class IlluminateRoleRepository implements RoleRepositoryInterface
{
    use RepositoryTrait;

    /**
     * The Eloquent role model FQCN.
     *
     * @var string
     */
    protected $model = EloquentRole::class;

    /**
     * Create a new Illuminate role repository.
     *
     * @param string $model
     *
     * @return void
     */
    public function __construct(string $model = null)
    {
        $this->model = $model;
    }

    /**
     * @inheritdoc
     */
    public function findById($id)
    {
        return $this->createModel()->newQuery()->find($id);
    }

    /**
     * @inheritdoc
     */
    public function findBySlug($slug)
    {
        return $this->createModel()->newQuery()->where('slug', $slug)->first();
    }

    /**
     * @inheritdoc
     */
    public function findByName($name)
    {
        return $this->createModel()->newQuery()->where('name', $name)->first();
    }
}
