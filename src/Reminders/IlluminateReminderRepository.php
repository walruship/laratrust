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

namespace Walruship\Laratrust\Reminders;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Walruship\Laratrust\Users\UserInterface;
use Walruship\Support\Traits\RepositoryTrait;
use Walruship\Laratrust\Users\UserRepositoryInterface;

class IlluminateReminderRepository implements ReminderRepositoryInterface
{
    use RepositoryTrait;

    /**
     * The Users repository instance.
     *
     * @var \Walruship\Laratrust\Users\UserRepositoryInterface
     */
    protected $users;

    /**
     * The Eloquent reminder model name.
     *
     * @var string
     */
    protected $model = EloquentReminder::class;

    /**
     * The expiration time in seconds.
     *
     * @var int
     */
    protected $expires = 259200;

    /**
     * Constructor.
     *
     * @param \Walruship\Laratrust\Users\UserRepositoryInterface $users
     * @param string                                             $model
     * @param int                                                $expires
     *
     * @return void
     */
    public function __construct(UserRepositoryInterface $users, $model = null, $expires = null)
    {
        $this->users   = $users;
        $this->model   = $model;
        $this->expires = $expires;
    }

    /**
     * @inheritdoc
     */
    public function create(UserInterface $user)
    {
        $reminder = $this->createModel();

        $code = $this->generateReminderCode();

        $reminder->fill([
            'code'      => $code,
            'completed' => false,
        ]);

        $reminder->user_id = $user->getUserId();

        $reminder->save();

        return $reminder;
    }

    /**
     * @inheritdoc
     */
    public function get(UserInterface $user, $code = null)
    {
        $expires = $this->expires();

        $reminder = $this
            ->createModel()
            ->newQuery()
            ->where('user_id', $user->getUserId())
            ->where('completed', false)
            ->where('created_at', '>', $expires)
        ;

        if ($code) {
            $reminder->where('code', $code);
        }

        return $reminder->first();
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
    public function complete(UserInterface $user, $code, $password)
    {
        $expires = $this->expires();

        $reminder = $this
            ->createModel()
            ->newQuery()
            ->where('user_id', $user->getUserId())
            ->where('code', $code)
            ->where('completed', false)
            ->where('created_at', '>', $expires)
            ->first()
        ;

        if ($reminder === null) {
            return false;
        }

        $credentials = compact('password');

        $valid = $this->users->validForUpdate($user, $credentials);

        if (! $valid) {
            return false;
        }

        $this->users->update($user, $credentials);

        $reminder->fill([
            'completed'    => true,
            'completed_at' => Carbon::now(),
        ]);

        $reminder->save();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function removeExpired()
    {
        $expires = $this->expires();

        return $this
            ->createModel()
            ->newQuery()
            ->where('completed', false)
            ->where('created_at', '<', $expires)
            ->delete()
        ;
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
     * Returns the random string used for the reminder code.
     *
     * @return string
     */
    protected function generateReminderCode()
    {
        return Str::random(32);
    }
}
