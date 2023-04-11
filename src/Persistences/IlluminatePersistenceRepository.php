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

use Walruship\Support\Traits\RepositoryTrait;
use Walruship\Laratrust\Cookies\CookieInterface;
use Walruship\Laratrust\Sessions\SessionInterface;

class IlluminatePersistenceRepository implements PersistenceRepositoryInterface
{
    use RepositoryTrait;

    /**
     * Single session.
     *
     * @var bool
     */
    protected $single = false;

    /**
     * Session storage driver.
     *
     * @var \Walruship\Laratrust\Sessions\SessionInterface
     */
    protected $session;

    /**
     * Cookie storage driver.
     *
     * @var \Walruship\Laratrust\Cookies\CookieInterface
     */
    protected $cookie;

    /**
     * Model name.
     *
     * @var string
     */
    protected $model = EloquentPersistence::class;

    /**
     * Create a new Laratrust persistence repository.
     *
     * @param \Walruship\Laratrust\Sessions\SessionInterface $session
     * @param \Walruship\Laratrust\Cookies\CookieInterface   $cookie
     * @param string                                         $model
     * @param bool                                           $single
     *
     * @return void
     */
    public function __construct(SessionInterface $session, CookieInterface $cookie, string $model = null, bool $single = false)
    {
        $this->model = $model;

        $this->session = $session;

        $this->cookie = $cookie;

        $this->single = $single;
    }

    /**
     * @inheritdoc
     */
    public function check()
    {
        if ($code = $this->session->get()) {
            return $code;
        }

        if ($code = $this->cookie->get()) {
            return $code;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function findByPersistenceCode(string $code)
    {
        return $this->createModel()->newQuery()->where('code', $code)->first();
    }

    /**
     * @inheritdoc
     */
    public function findUserByPersistenceCode(string $code)
    {
        $persistence = $this->findByPersistenceCode($code);

        return $persistence ? $persistence->user : null;
    }

    /**
     * @inheritdoc
     */
    public function persist(PersistableInterface $persistable, $remember = false)
    {
        if ($this->single) {
            $this->flush($persistable);
        }

        $code = $persistable->generatePersistenceCode();

        $this->session->put($code);

        if ($remember) {
            $this->cookie->put($code);
        }

        $persistence = $this->createModel();

        $persistence->{$persistable->getPersistableKey()} = $persistable->getPersistableId();

        $persistence->code = $code;

        return $persistence->save();
    }

    /**
     * @inheritdoc
     */
    public function persistAndRemember(PersistableInterface $persistable)
    {
        return $this->persist($persistable, true);
    }

    /**
     * @inheritdoc
     */
    public function forget()
    {
        $code = $this->check();

        if ($code === null) {
            return null;
        }

        $this->session->forget();
        $this->cookie->forget();

        return $this->remove($code);
    }

    /**
     * @inheritdoc
     */
    public function remove($code)
    {
        return $this->createModel()->newQuery()->where('code', $code)->delete();
    }

    /**
     * @inheritdoc
     */
    public function flush(PersistableInterface $persistable, $forget = true)
    {
        if ($forget) {
            $this->forget();
        }

        $relationship = $persistable->getPersistableRelationship();

        foreach ($persistable->{$relationship}()->get() as $persistence) {
            if ($persistence->code !== $this->check()) {
                $persistence->delete();
            }
        }
    }
}
