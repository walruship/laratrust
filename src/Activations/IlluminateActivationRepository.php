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

namespace Walruship\Laratrust\Activations;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Walruship\Laratrust\Users\UserInterface;
use Walruship\Support\Traits\RepositoryTrait;

class IlluminateActivationRepository implements ActivationRepositoryInterface
{
    use RepositoryTrait;

    /**
     * The Activation model FQCN.
     *
     * @var string
     */
    protected $model = EloquentActivation::class;

    /**
     * The activation expiration time, in seconds.
     *
     * @var int
     */
    protected $expires = 259200;

    /**
     * Constructor.
     *
     * @param string $model
     * @param int    $expires
     *
     * @return void
     */
    public function __construct($model, $expires)
    {
        $this->model   = $model;
        $this->expires = $expires;
    }

    /**
     * @inheritdoc
     */
    public function create(UserInterface $user)
    {
        $activation = $this->createModel();

        $code = $this->generateActivationCode();

        $activation->fill([
            'code' => $code,
        ]);

        $activation->user_id = $user->getUserId();

        $activation->save();

        return $activation;
    }

    /**
     * @inheritdoc
     */
    public function get(UserInterface $user, $code = null)
    {
        $expires = $this->expires();

        return $this
            ->createModel()
            ->newQuery()
            ->where('user_id', $user->getUserId())
            ->where('completed', false)
            ->where('created_at', '>', $expires)
            ->when($code, function ($query, $code) {
                return $query->where('code', $code);
            })
            ->first()
        ;
    }

    /**
     * @inheritdoc
     */
    public function exists(UserInterface $user, $code = null)
    {
        return (bool) $this->get($user, $code);
    }

    /**
     * @inheritdoc
     */
    public function complete(UserInterface $user, $code)
    {
        $expires = $this->expires();

        $activation = $this
            ->createModel()
            ->newQuery()
            ->where('user_id', $user->getUserId())
            ->where('code', $code)
            ->where('completed', false)
            ->where('created_at', '>', $expires)
            ->first()
        ;

        if (! $activation) {
            return false;
        }

        $activation->fill([
            'completed'    => true,
            'completed_at' => Carbon::now(),
        ]);

        $activation->save();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function completed(UserInterface $user)
    {
        $userId = $user->getUserId();

        return $this->createModel()->newQuery()->where('user_id', $userId)->where('completed', true)->exists();
    }

    /**
     * @inheritdoc
     */
    public function remove(UserInterface $user)
    {
        $userId = $user->getUserId();

        $activation = $this->createModel()->newQuery()->where('user_id', $userId)->where('completed', true)->first();

        if (! $activation) {
            return false;
        }

        return $activation->delete();
    }

    /**
     * @inheritdoc
     */
    public function removeExpired()
    {
        $expires = $this->expires();

        return $this->createModel()->newQuery()->where('completed', false)->where('created_at', '<', $expires)->delete();
    }

    /**
     * Returns the expiration date.
     *
     * @return \Carbon\Carbon
     */
    protected function expires()
    {
        return Carbon::now()->subSeconds($this->expires);
    }

    /**
     * Returns the random string used for the activation code.
     *
     * @return string
     */
    protected function generateActivationCode()
    {
        return Str::random(32);
    }
}
