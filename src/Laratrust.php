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

namespace Walruship\Laratrust;

use Closure;
use Illuminate\Support\Str;
use Walruship\Support\Traits\EventTrait;
use Illuminate\Contracts\Events\Dispatcher;
use Walruship\Laratrust\Users\UserInterface;
use Walruship\Laratrust\Roles\RoleRepositoryInterface;
use Walruship\Laratrust\Users\UserRepositoryInterface;
use Walruship\Laratrust\Checkpoints\CheckpointInterface;
use Walruship\Laratrust\Reminders\ReminderRepositoryInterface;
use Walruship\Laratrust\Throttling\ThrottleRepositoryInterface;
use Walruship\Laratrust\Activations\ActivationRepositoryInterface;
use Walruship\Laratrust\Persistences\PersistenceRepositoryInterface;

class Laratrust
{
    use EventTrait;

    /**
     * The current cached, logged in user.
     *
     * @var \Walruship\Laratrust\Users\UserInterface
     */
    protected $user;

    /**
     * The Persistences repository instance.
     *
     * @var \Walruship\Laratrust\Persistences\PersistenceRepositoryInterface
     */
    protected $persistences;

    /**
     * The Users repository instance.
     *
     * @var \Walruship\Laratrust\Users\UserRepositoryInterface
     */
    protected $users;

    /**
     * The Roles repository instance.
     *
     * @var \Walruship\Laratrust\Roles\RoleRepositoryInterface
     */
    protected $roles;

    /**
     * The Activations repository instance.
     *
     * @var \Walruship\Laratrust\Activations\ActivationRepositoryInterface
     */
    protected $activations;

    /**
     * The Reminders repository.
     *
     * @var \Walruship\Laratrust\Reminders\ReminderRepositoryInterface
     */
    protected $reminders;

    /**
     * The Throttling repository instance.
     *
     * @var \Walruship\Laratrust\Throttling\ThrottleRepositoryInterface
     */
    protected $throttle;

    /**
     * Cached, available methods on the user repository, used for dynamic calls.
     *
     * @var array
     */
    protected $userMethods = [];

    /**
     * Array that holds all the enabled checkpoints.
     *
     * @var array
     */
    protected $checkpoints = [];

    /**
     * Flag for the checkpoint status.
     *
     * @var bool
     */
    protected $checkpointsStatus = true;

    /**
     * The closure to retrieve the request credentials.
     *
     * @var \Closure
     */
    protected $requestCredentials;

    /**
     * The closure used to create a basic response for failed HTTP auth.
     *
     * @var \Closure
     */
    protected $basicResponse;

    /**
     * Constructor.
     *
     * @param \Walruship\Laratrust\Persistences\PersistenceRepositoryInterface $persistences
     * @param \Walruship\Laratrust\Users\UserRepositoryInterface               $users
     * @param \Walruship\Laratrust\Roles\RoleRepositoryInterface               $roles
     * @param \Walruship\Laratrust\Activations\ActivationRepositoryInterface   $activations
     * @param \Illuminate\Contracts\Events\Dispatcher                          $dispatcher
     *
     * @return void
     */
    public function __construct(
        PersistenceRepositoryInterface $persistences,
        UserRepositoryInterface $users,
        RoleRepositoryInterface $roles,
        ActivationRepositoryInterface $activations,
        Dispatcher $dispatcher
    ) {
        $this->users        = $users;
        $this->roles        = $roles;
        $this->dispatcher   = $dispatcher;
        $this->activations  = $activations;
        $this->persistences = $persistences;
    }

    /**
     * Registers a user. You may provide a callback to occur before the user
     * is saved, or provide a true boolean as a shortcut to activation.
     *
     * @param array         $credentials
     * @param bool|\Closure $callback
     *
     * @throws \InvalidArgumentException
     *
     * @return bool|\Walruship\Laratrust\Users\UserInterface
     */
    public function register(array $credentials, $callback = false)
    {
        if (! $callback instanceof \Closure && ! is_bool($callback)) {
            throw new \InvalidArgumentException('You must provide a closure or a boolean.');
        }

        $this->fireEvent('sentinel.registering', [$credentials]);

        $valid = $this->users->validForCreation($credentials);

        if (! $valid) {
            return false;
        }

        $argument = $callback instanceof \Closure ? $callback : null;

        $user = $this->users->create($credentials, $argument);

        if ($callback === true) {
            $this->activate($user);
        }

        $this->fireEvent('sentinel.registered', $user);

        return $user;
    }

    /**
     * Registers and activates the user.
     *
     * @param array $credentials
     *
     * @return bool|\Walruship\Laratrust\Users\UserInterface
     */
    public function registerAndActivate(array $credentials)
    {
        return $this->register($credentials, true);
    }

    /**
     * Activates the given user.
     *
     * @param mixed $user
     *
     * @throws \InvalidArgumentException
     *
     * @return bool
     */
    public function activate($user): bool
    {
        if (is_string($user) || is_array($user)) {
            $users = $this->getUserRepository();

            $method = 'findBy'.(is_string($user) ? 'Id' : 'Credentials');

            $user = $users->{$method}($user);
        }

        if (! $user instanceof UserInterface) {
            throw new \InvalidArgumentException('No valid user was provided.');
        }

        $this->fireEvent('sentinel.activating', $user);

        $activations = $this->getActivationRepository();

        $activation = $activations->create($user);

        $this->fireEvent('sentinel.activated', [$user, $activation]);

        return $activations->complete($user, $activation->getCode());
    }

    /**
     * Checks to see if a user is logged in.
     *
     * @return bool|\Walruship\Laratrust\Users\UserInterface
     */
    public function check()
    {
        if ($this->user !== null) {
            return $this->user;
        }

        if (! $code = $this->persistences->check()) {
            return false;
        }

        if (! $user = $this->persistences->findUserByPersistenceCode($code)) {
            return false;
        }

        if (! $this->cycleCheckpoints('check', $user)) {
            return false;
        }

        return $this->user = $user;
    }

    /**
     * Checks to see if a user is logged in, bypassing checkpoints.
     *
     * @return bool|\Walruship\Laratrust\Users\UserInterface
     */
    public function forceCheck()
    {
        return $this->bypassCheckpoints(function ($sentinel) {
            return $sentinel->check();
        });
    }

    /**
     * Checks if we are currently a guest.
     *
     * @return bool
     */
    public function guest(): bool
    {
        return ! $this->check();
    }

    /**
     * Authenticates a user, with "remember" flag.
     *
     * @param array|\Walruship\Laratrust\Users\UserInterface $credentials
     * @param bool                                           $remember
     * @param bool                                           $login
     *
     * @return bool|\Walruship\Laratrust\Users\UserInterface
     */
    public function authenticate($credentials, bool $remember = false, bool $login = true)
    {
        $response = $this->fireEvent('sentinel.authenticating', [$credentials], true);

        if ($response === false) {
            return false;
        }

        if ($credentials instanceof UserInterface) {
            $user = $credentials;
        } else {
            $user = $this->users->findByCredentials($credentials);

            $valid = $user !== null && $this->users->validateCredentials($user, $credentials);

            if (! $valid) {
                $this->cycleCheckpoints('fail', $user, false);

                return false;
            }
        }

        if (! $this->cycleCheckpoints('login', $user)) {
            return false;
        }

        if ($login) {
            if (! $user = $this->login($user, $remember)) {
                return false;
            }
        }

        $this->fireEvent('sentinel.authenticated', $user);

        return $this->user = $user;
    }

    /**
     * Authenticates a user, with the "remember" flag.
     *
     * @param array|\Walruship\Laratrust\Users\UserInterface $credentials
     *
     * @return bool|\Walruship\Laratrust\Users\UserInterface
     */
    public function authenticateAndRemember($credentials)
    {
        return $this->authenticate($credentials, true);
    }

    /**
     * Forces an authentication to bypass checkpoints.
     *
     * @param array|\Walruship\Laratrust\Users\UserInterface $credentials
     * @param bool                                           $remember
     *
     * @return bool|\Walruship\Laratrust\Users\UserInterface
     */
    public function forceAuthenticate($credentials, bool $remember = false)
    {
        return $this->bypassCheckpoints(function ($sentinel) use ($credentials, $remember) {
            return $sentinel->authenticate($credentials, $remember);
        });
    }

    /**
     * Forces an authentication to bypass checkpoints, with the "remember" flag.
     *
     * @param array|\Walruship\Laratrust\Users\UserInterface $credentials
     *
     * @return bool|\Walruship\Laratrust\Users\UserInterface
     */
    public function forceAuthenticateAndRemember($credentials)
    {
        return $this->forceAuthenticate($credentials, true);
    }

    /**
     * Attempt a stateless authentication.
     *
     * @param array|\Walruship\Laratrust\Users\UserInterface $credentials
     *
     * @return bool|\Walruship\Laratrust\Users\UserInterface
     */
    public function stateless($credentials)
    {
        return $this->authenticate($credentials, false, false);
    }

    /**
     * Attempt to authenticate using HTTP Basic Auth.
     *
     * @return mixed
     */
    public function basic()
    {
        $credentials = $this->getRequestCredentials();

        // We don't really want to add a throttling record for the
        // first failed login attempt, which actually occurs when
        // the user first hits a protected route.
        if ($credentials === null) {
            return $this->getBasicResponse();
        }

        $user = $this->stateless($credentials);

        if ($user) {
            return;
        }

        return $this->getBasicResponse();
    }

    /**
     * Returns the request credentials.
     *
     * @return array|null
     */
    public function getRequestCredentials()
    {
        if ($this->requestCredentials === null) {
            $this->requestCredentials = function () {
                $credentials = [];

                if (isset($_SERVER['PHP_AUTH_USER'])) {
                    $credentials['login'] = $_SERVER['PHP_AUTH_USER'];
                }

                if (isset($_SERVER['PHP_AUTH_PW'])) {
                    $credentials['password'] = $_SERVER['PHP_AUTH_PW'];
                }

                if (count($credentials) > 0) {
                    return $credentials;
                }
            };
        }

        $credentials = $this->requestCredentials;

        return $credentials();
    }

    /**
     * Sets the closure which resolves the request credentials.
     *
     * @param \Closure $requestCredentials
     *
     * @return void
     */
    public function setRequestCredentials(\Closure $requestCredentials)
    {
        $this->requestCredentials = $requestCredentials;
    }

    /**
     * Sends a response when HTTP basic authentication fails.
     *
     * @throws \RuntimeException
     *
     * @return mixed
     */
    public function getBasicResponse()
    {
        // Default the basic response
        if ($this->basicResponse === null) {
            $this->basicResponse = function () {
                if (headers_sent()) {
                    throw new \RuntimeException('Attempting basic auth after headers have already been sent.');
                }

                header('WWW-Authenticate: Basic');
                header('HTTP/1.0 401 Unauthorized');

                echo 'Invalid credentials.';
                exit;
            };
        }

        $response = $this->basicResponse;

        return $response();
    }

    /**
     * Sets the callback which creates a basic response.
     *
     * @param \Closure $basicResonse
     *
     * @return void
     */
    public function creatingBasicResponse(\Closure $basicResponse)
    {
        $this->basicResponse = $basicResponse;
    }

    /**
     * Persists a login for the given user.
     *
     * @param \Walruship\Laratrust\Users\UserInterface $user
     * @param bool                                     $remember
     *
     * @return bool|\Walruship\Laratrust\Users\UserInterface
     */
    public function login(UserInterface $user, bool $remember = false)
    {
        $this->fireEvent('sentinel.logging-in', $user);

        $this->persistences->persist($user, $remember);

        $response = $this->users->recordLogin($user);

        if (! $response) {
            return false;
        }

        $this->fireEvent('sentinel.logged-in', $user);

        return $this->user = $user;
    }

    /**
     * Persists a login for the given user, with the "remember" flag.
     *
     * @param \Walruship\Laratrust\Users\UserInterface $user
     *
     * @return bool|\Walruship\Laratrust\Users\UserInterface
     */
    public function loginAndRemember(UserInterface $user)
    {
        return $this->login($user, true);
    }

    /**
     * Logs the current user out.
     *
     * @param \Walruship\Laratrust\Users\UserInterface|null $user
     * @param bool                                          $everywhere
     *
     * @return bool
     */
    public function logout(UserInterface $user = null, bool $everywhere = false)
    {
        $currentUser = $this->check();

        $this->fireEvent('sentinel.logging-out', $user);

        if ($user && $user !== $currentUser) {
            $this->persistences->flush($user, false);

            $this->fireEvent('sentinel.logged-out', $user);

            return true;
        }

        $user = $user ?: $currentUser;

        if ($user === false) {
            $this->fireEvent('sentinel.logged-out', $user);

            return true;
        }

        $method = $everywhere === true ? 'flush' : 'forget';

        $this->persistences->{$method}($user);

        $this->user = null;

        $this->fireEvent('sentinel.logged-out', $user);

        return $this->users->recordLogout($user);
    }

    /**
     * Pass a closure to Laratrust to bypass checkpoints.
     *
     * @param \Closure $callback
     * @param array    $checkpoints
     *
     * @return mixed
     */
    public function bypassCheckpoints(\Closure $callback, array $checkpoints = [])
    {
        $originalCheckpoints = $this->checkpoints;

        $activeCheckpoints = [];

        foreach (array_keys($originalCheckpoints) as $checkpoint) {
            if ($checkpoints && ! in_array($checkpoint, $checkpoints)) {
                $activeCheckpoints[$checkpoint] = $originalCheckpoints[$checkpoint];
            }
        }

        // Temporarily replace the registered checkpoints
        $this->checkpoints = $activeCheckpoints;

        // Fire the callback
        $result = $callback($this);

        // Reset checkpoints
        $this->checkpoints = $originalCheckpoints;

        return $result;
    }

    /**
     * Checks if checkpoints are enabled.
     *
     * @return bool
     */
    public function checkpointsStatus()
    {
        return $this->checkpointsStatus;
    }

    /**
     * Enables checkpoints.
     *
     * @return void
     */
    public function enableCheckpoints(): void
    {
        $this->checkpointsStatus = true;
    }

    /**
     * Disables checkpoints.
     *
     * @return void
     */
    public function disableCheckpoints()
    {
        $this->checkpointsStatus = false;
    }

    /**
     * Returns all the added Checkpoints.
     *
     * @return array
     */
    public function getCheckpoints()
    {
        return $this->checkpoints;
    }

    /**
     * Add a new checkpoint to Laratrust.
     *
     * @param string                                               $key
     * @param \Walruship\Laratrust\Checkpoints\CheckpointInterface $checkpoint
     *
     * @return void
     */
    public function addCheckpoint(string $key, CheckpointInterface $checkpoint)
    {
        $this->checkpoints[$key] = $checkpoint;
    }

    /**
     * Removes a checkpoint.
     *
     * @param string $key
     *
     * @return void
     */
    public function removeCheckpoint(string $key): void
    {
        if (isset($this->checkpoints[$key])) {
            unset($this->checkpoints[$key]);
        }
    }

    /**
     * Removes the given checkpoints.
     *
     * @param array $checkpoints
     *
     * @return void
     */
    public function removeCheckpoints(array $checkpoints = [])
    {
        foreach ($checkpoints as $checkpoint) {
            $this->removeCheckpoint($checkpoint);
        }
    }

    /**
     * Cycles through all the registered checkpoints for a user. Checkpoints
     * may throw their own exceptions, however, if just one returns false,
     * the cycle fails.
     *
     * @param string                                   $method
     * @param \Walruship\Laratrust\Users\UserInterface $user
     * @param bool                                     $halt
     *
     * @return bool
     */
    protected function cycleCheckpoints(string $method, UserInterface $user = null, bool $halt = true)
    {
        if (! $this->checkpointsStatus) {
            return true;
        }

        foreach ($this->checkpoints as $checkpoint) {
            $response = $checkpoint->{$method}($user);

            if (! $response && $halt) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns the currently logged in user, lazily checking for it.
     *
     * @param bool $check
     *
     * @return \Walruship\Laratrust\Users\UserInterface|null
     */
    public function getUser(bool $check = true)
    {
        if ($check && $this->user === null) {
            $this->check();
        }

        return $this->user;
    }

    /**
     * Sets the user associated with Laratrust (does not log in).
     *
     * @param \Walruship\Laratrust\Users\UserInterface $user
     *
     * @return void
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;
    }

    /**
     * Returns the user repository.
     *
     * @return \Walruship\Laratrust\Users\UserRepositoryInterface
     */
    public function getUserRepository()
    {
        return $this->users;
    }

    /**
     * Sets the user repository.
     *
     * @param \Walruship\Laratrust\Users\UserRepositoryInterface $users
     *
     * @return void
     */
    public function setUserRepository(UserRepositoryInterface $users)
    {
        $this->users = $users;

        $this->userMethods = [];
    }

    /**
     * Returns the role repository.
     *
     * @return \Walruship\Laratrust\Roles\RoleRepositoryInterface
     */
    public function getRoleRepository()
    {
        return $this->roles;
    }

    /**
     * Sets the role repository.
     *
     * @param \Walruship\Laratrust\Roles\RoleRepositoryInterface $roles
     *
     * @return void
     */
    public function setRoleRepository(RoleRepositoryInterface $roles)
    {
        $this->roles = $roles;
    }

    /**
     * Returns the persistences repository.
     *
     * @return \Walruship\Laratrust\Persistences\PersistenceRepositoryInterface
     */
    public function getPersistenceRepository()
    {
        return $this->persistences;
    }

    /**
     * Sets the persistences repository.
     *
     * @param \Walruship\Laratrust\Persistences\PersistenceRepositoryInterface $persistences
     *
     * @return void
     */
    public function setPersistenceRepository(PersistenceRepositoryInterface $persistences)
    {
        $this->persistences = $persistences;
    }

    /**
     * Returns the activations repository.
     *
     * @return \Walruship\Laratrust\Activations\ActivationRepositoryInterface
     */
    public function getActivationRepository()
    {
        return $this->activations;
    }

    /**
     * Sets the activations repository.
     *
     * @param \Walruship\Laratrust\Activations\ActivationRepositoryInterface $activations
     *
     * @return void
     */
    public function setActivationRepository(ActivationRepositoryInterface $activations)
    {
        $this->activations = $activations;
    }

    /**
     * Returns the reminders repository.
     *
     * @return \Walruship\Laratrust\Reminders\ReminderRepositoryInterface
     */
    public function getReminderRepository(): ReminderRepositoryInterface
    {
        return $this->reminders;
    }

    /**
     * Sets the reminders repository.
     *
     * @param \Walruship\Laratrust\Reminders\ReminderRepositoryInterface $reminders
     *
     * @return void
     */
    public function setReminderRepository(ReminderRepositoryInterface $reminders)
    {
        $this->reminders = $reminders;
    }

    /**
     * Returns the throttle repository.
     *
     * @return \Walruship\Laratrust\Throttling\ThrottleRepositoryInterface
     */
    public function getThrottleRepository()
    {
        return $this->throttle;
    }

    /**
     * Sets the throttle repository.
     *
     * @param \Walruship\Laratrust\Throttling\ThrottleRepositoryInterface $throttle
     *
     * @return void
     */
    public function setThrottleRepository(ThrottleRepositoryInterface $throttle)
    {
        $this->throttle = $throttle;
    }

    /**
     * Returns all accessible methods on the associated user repository.
     *
     * @return array
     */
    protected function getUserMethods()
    {
        if (empty($this->userMethods)) {
            $users = $this->getUserRepository();

            $methods = get_class_methods($users);

            $this->userMethods = array_diff($methods, ['__construct']);
        }

        return $this->userMethods;
    }

    /**
     * Dynamically pass missing methods to Laratrust.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @throws \BadMethodCallException
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $methods = $this->getUserMethods();

        if (in_array($method, $methods)) {
            $users = $this->getUserRepository();

            return call_user_func_array([$users, $method], $parameters);
        }

        if (Str::startsWith($method, 'findUserBy')) {
            $user = $this->getUserRepository();

            $method = 'findBy'.substr($method, 10);

            return call_user_func_array([$user, $method], $parameters);
        }

        if (Str::startsWith($method, 'findRoleBy')) {
            $roles = $this->getRoleRepository();

            $method = 'findBy'.substr($method, 10);

            return call_user_func_array([$roles, $method], $parameters);
        }

        $methods = ['getRoles', 'inRole', 'inAnyRole', 'hasAccess', 'hasAnyAccess'];

        $className = get_class($this);

        if (in_array($method, $methods)) {
            $user = $this->getUser();

            if ($user === null) {
                throw new \BadMethodCallException("Method {$className}::{$method}() can only be called if a user is logged in.");
            }

            return call_user_func_array([$user, $method], $parameters);
        }

        throw new \BadMethodCallException("Call to undefined method {$className}::{$method}()");
    }
}
