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

namespace Walruship\Laratrust\Native;

use Illuminate\Events\Dispatcher;
use Walruship\Laratrust\Laratrust;
use Symfony\Component\HttpFoundation\Request;
use Walruship\Laratrust\Cookies\NativeCookie;
use Walruship\Laratrust\Hashing\NativeHasher;
use Walruship\Laratrust\Sessions\NativeSession;
use Walruship\Laratrust\Checkpoints\ThrottleCheckpoint;
use Walruship\Laratrust\Roles\IlluminateRoleRepository;
use Walruship\Laratrust\Users\IlluminateUserRepository;
use Walruship\Laratrust\Checkpoints\ActivationCheckpoint;
use Walruship\Laratrust\Reminders\IlluminateReminderRepository;
use Walruship\Laratrust\Throttling\IlluminateThrottleRepository;
use Walruship\Laratrust\Activations\IlluminateActivationRepository;
use Walruship\Laratrust\Persistences\IlluminatePersistenceRepository;

class LaratrustBootstrapper
{
    /**
     * Configuration.
     *
     * @var array
     */
    protected $config;

    /**
     * The event dispatcher.
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $dispatcher;

    /**
     * Constructor.
     *
     * @param array $config
     *
     * @return void
     */
    public function __construct($config = null)
    {
        if (is_string($config)) {
            $this->config = new ConfigRepository($config);
        } else {
            $this->config = $config ?: new ConfigRepository();
        }
    }

    /**
     * Creates a sentinel instance.
     *
     * @return \Walruship\Laratrust\Laratrust
     */
    public function createLaratrust()
    {
        $persistence = $this->createPersistence();
        $users       = $this->createUsers();
        $roles       = $this->createRoles();
        $activations = $this->createActivations();
        $dispatcher  = $this->getEventDispatcher();

        $sentinel = new Laratrust(
            $persistence,
            $users,
            $roles,
            $activations,
            $dispatcher
        );

        $throttle = $this->createThrottling();

        $ipAddress = $this->getIpAddress();

        $checkpoints = $this->createCheckpoints($activations, $throttle, $ipAddress);

        foreach ($checkpoints as $key => $checkpoint) {
            $sentinel->addCheckpoint($key, $checkpoint);
        }

        $reminders = $this->createReminders($users);

        $sentinel->setActivationRepository($activations);

        $sentinel->setReminderRepository($reminders);

        $sentinel->setThrottleRepository($throttle);

        return $sentinel;
    }

    /**
     * Creates a persistences repository.
     *
     * @return \Walruship\Laratrust\Persistences\IlluminatePersistenceRepository
     */
    protected function createPersistence()
    {
        $session = $this->createSession();

        $cookie = $this->createCookie();

        $model = $this->config['persistences']['model'];

        $single = $this->config['persistences']['single'];

        $users = $this->config['users']['model'];

        if (class_exists($users) && method_exists($users, 'setPersistencesModel')) {
            forward_static_call_array([$users, 'setPersistencesModel'], [$model]);
        }

        return new IlluminatePersistenceRepository($session, $cookie, $model, $single);
    }

    /**
     * Creates a session.
     *
     * @return \Walruship\Laratrust\Sessions\NativeSession
     */
    protected function createSession()
    {
        return new NativeSession($this->config['session']);
    }

    /**
     * Creates a cookie.
     *
     * @return \Walruship\Laratrust\Cookies\NativeCookie
     */
    protected function createCookie()
    {
        return new NativeCookie($this->config['cookie']);
    }

    /**
     * Creates a user repository.
     *
     * @return \Walruship\Laratrust\Users\IlluminateUserRepository
     */
    protected function createUsers()
    {
        $hasher = $this->createHasher();

        $model = $this->config['users']['model'];

        $roles = $this->config['roles']['model'];

        $persistences = $this->config['persistences']['model'];

        if (class_exists($roles) && method_exists($roles, 'setUsersModel')) {
            forward_static_call_array([$roles, 'setUsersModel'], [$model]);
        }

        if (class_exists($persistences) && method_exists($persistences, 'setUsersModel')) {
            forward_static_call_array([$persistences, 'setUsersModel'], [$model]);
        }

        return new IlluminateUserRepository($hasher, $this->getEventDispatcher(), $model);
    }

    /**
     * Creates a hasher.
     *
     * @return \Walruship\Laratrust\Hashing\NativeHasher
     */
    protected function createHasher()
    {
        return new NativeHasher();
    }

    /**
     * Creates a role repository.
     *
     * @return \Walruship\Laratrust\Roles\IlluminateRoleRepository
     */
    protected function createRoles()
    {
        $model = $this->config['roles']['model'];

        $users = $this->config['users']['model'];

        if (class_exists($users) && method_exists($users, 'setRolesModel')) {
            forward_static_call_array([$users, 'setRolesModel'], [$model]);
        }

        return new IlluminateRoleRepository($model);
    }

    /**
     * Creates an activation repository.
     *
     * @return \Walruship\Laratrust\Activations\IlluminateActivationRepository
     */
    protected function createActivations()
    {
        $model = $this->config['activations']['model'];

        $expires = $this->config['activations']['expires'];

        return new IlluminateActivationRepository($model, $expires);
    }

    /**
     * Returns the client's ip address.
     *
     * @return string
     */
    protected function getIpAddress()
    {
        $request = Request::createFromGlobals();

        return $request->getClientIp();
    }

    /**
     * Create an activation checkpoint.
     *
     * @param \Walruship\Laratrust\Activations\IlluminateActivationRepository $activations
     *
     * @return \Walruship\Laratrust\Checkpoints\ActivationCheckpoint
     */
    protected function createActivationCheckpoint(IlluminateActivationRepository $activations)
    {
        return new ActivationCheckpoint($activations);
    }

    /**
     * Create activation and throttling checkpoints.
     *
     * @param \Walruship\Laratrust\Activations\IlluminateActivationRepository $activations
     * @param \Walruship\Laratrust\Throttling\IlluminateThrottleRepository    $throttle
     * @param string                                                          $ipAddress
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    protected function createCheckpoints(IlluminateActivationRepository $activations, IlluminateThrottleRepository $throttle, $ipAddress)
    {
        $activeCheckpoints = $this->config['checkpoints'];

        $activation = $this->createActivationCheckpoint($activations);

        $throttle = $this->createThrottleCheckpoint($throttle, $ipAddress);

        $checkpoints = [];

        foreach ($activeCheckpoints as $checkpoint) {
            if (! isset(${$checkpoint})) {
                throw new \InvalidArgumentException("Invalid checkpoint [{$checkpoint}] given.");
            }

            $checkpoints[$checkpoint] = ${$checkpoint};
        }

        return $checkpoints;
    }

    /**
     * Create a throttle checkpoint.
     *
     * @param \Walruship\Laratrust\Throttling\IlluminateThrottleRepository $throttle
     * @param string                                                       $ipAddress
     *
     * @return \Walruship\Laratrust\Checkpoints\ThrottleCheckpoint
     */
    protected function createThrottleCheckpoint(IlluminateThrottleRepository $throttle, $ipAddress)
    {
        return new ThrottleCheckpoint($throttle, $ipAddress);
    }

    /**
     * Create a throttling repository.
     *
     * @return \Walruship\Laratrust\Throttling\IlluminateThrottleRepository
     */
    protected function createThrottling()
    {
        $model = $this->config['throttling']['model'];

        foreach (['global', 'ip', 'user'] as $type) {
            ${"{$type}Interval"} = $this->config['throttling'][$type]['interval'];

            ${"{$type}Thresholds"} = $this->config['throttling'][$type]['thresholds'];
        }

        return new IlluminateThrottleRepository(
            $model,
            $globalInterval,
            $globalThresholds,
            $ipInterval,
            $ipThresholds,
            $userInterval,
            $userThresholds
        );
    }

    /**
     * Returns the event dispatcher.
     *
     * @return \Illuminate\Contracts\Events\Dispatcher
     */
    protected function getEventDispatcher()
    {
        if (! $this->dispatcher) {
            $this->dispatcher = new Dispatcher();
        }

        return $this->dispatcher;
    }

    /**
     * Create a reminder repository.
     *
     * @param \Walruship\Laratrust\Users\IlluminateUserRepository $users
     *
     * @return \Walruship\Laratrust\Reminders\IlluminateReminderRepository
     */
    protected function createReminders(IlluminateUserRepository $users)
    {
        $model = $this->config['reminders']['model'];

        $expires = $this->config['reminders']['expires'];

        return new IlluminateReminderRepository($users, $model, $expires);
    }
}
