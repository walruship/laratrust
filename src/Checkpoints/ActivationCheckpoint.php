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

use Walruship\Laratrust\Users\UserInterface;
use Walruship\Laratrust\Activations\ActivationRepositoryInterface;

class ActivationCheckpoint implements CheckpointInterface
{
    use AuthenticatedCheckpoint;

    /**
     * The Activations repository instance.
     *
     * @var \Walruship\Laratrust\Activations\ActivationRepositoryInterface
     */
    protected $activations;

    /**
     * Constructor.
     *
     * @param \Walruship\Laratrust\Activations\ActivationRepositoryInterface $activations
     *
     * @return void
     */
    public function __construct(ActivationRepositoryInterface $activations)
    {
        $this->activations = $activations;
    }

    /**
     * @inheritdoc
     */
    public function login(UserInterface $user): bool
    {
        return $this->checkActivation($user);
    }

    /**
     * @inheritdoc
     */
    public function check(UserInterface $user): bool
    {
        return $this->checkActivation($user);
    }

    /**
     * Checks the activation status of the given user.
     *
     * @param \Walruship\Laratrust\Users\UserInterface $user
     *
     * @throws \Walruship\Laratrust\Checkpoints\NotActivatedException
     *
     * @return bool
     */
    protected function checkActivation(UserInterface $user): bool
    {
        $completed = $this->activations->completed($user);

        if (! $completed) {
            $exception = new NotActivatedException('Your account has not been activated yet.');

            $exception->setUser($user);

            throw $exception;
        }

        return true;
    }
}
