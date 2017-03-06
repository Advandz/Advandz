<?php
/**
 * Allow the creation and management of user accounts.
 *
 * @package Advandz
 * @subpackage Advandz.components.users
 * @copyright Copyright (c) 2012-2017 CyanDark, Inc. All Rights Reserved.
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author The Advandz Team <team@advandz.com>
 */

namespace Advandz\Component;

class Users
{
    /**
     * Constructs a new Users object.
     */
    public function __construct()
    {
        // Load the necessary components
        \Loader::loadComponents($this, ['Orm', 'Input', 'Password']);

        // Load the users table from database to the ORM
        $this->Orm->_(['users']);
    }

    /**
     * Creates a new user.
     *
     * @param  string    $username The username
     * @param  string    $password The password
     * @param  string    $email    The user email
     * @param  array     $vars     The user data
     * @return bool      True if the user can be added successfully
     * @throws Exception Throws when the user already exists
     */
    public function addUser($username, $password, $email, $vars = [])
    {
        // Generate password hash
        $password = $this->Password->hash($password);

        // Add the user to the database
        if (!$this->userExists($username)) {
            if ($this->Input->isEmail($email)) {
                $this->Orm->Users->add([
                    'username' => $username,
                    'password' => $password,
                    'email'    => $email,
                    'vars'     => serialize($vars)
                ]);

                return true;
            }
        } else {
            throw new \Exception('The user '.$username.' already exists');
        }

        return false;
    }

    /**
     * Get the information from a user.
     *
     * @param  string    $username The username
     * @return array     An array containing the user information
     * @throws Exception Throws when the user not exists
     */
    public function getUser($username)
    {
        if ($this->userExists($username)) {
            $result       = $this->Orm->Users->username($username);
            $result->vars = unserialize($result->vars);

            return $result;
        } else {
            throw new \Exception('The user '.$username.' not exists in the database');
        }
    }

    /**
     * Delete a user.
     *
     * @param  string    $username The username
     * @return bool      True if the user can be deleted successfully
     * @throws Exception Throws when the user not exists
     */
    public function removeUser($username)
    {
        if ($this->userExists($username)) {
            // Remove the user from the database
            $this->Orm->Users->remove(['username', '=', $username]);

            return true;
        } else {
            throw new \Exception('The user '.$username.' not exists in the database');
        }
    }

    /**
     * Edit a user.
     *
     * @param  string    $username The username
     * @param  string    $password The password
     * @param  string    $email    The user email
     * @param  array     $vars     The user data
     * @throws Exception Throws when the user not exists
     */
    public function editUser($username, $password = null, $email = null, $vars = [])
    {
        if ($this->userExists($username)) {
            // Edit the password
            if (!empty($password)) {
                $password = $this->Password->hash($password);
                $this->Orm->Users->edit(['password' => $password], ['username', '=', $username]);
            }

            // Edit the email address
            if (!empty($email)) {
                $this->Orm->Users->edit(['email' => $email], ['username', '=', $username]);
            }

            // Edit the user data
            if (!empty($vars)) {
                $this->Orm->Users->edit(['vars' => serialize($vars)], ['username', '=', $username]);
            }
        } else {
            throw new \Exception('The user '.$username.' not exists in the database');
        }
    }

    /**
     * Validate the user credentials.
     *
     * @param string $username The username
     * @param string $password The password
     * @param bool   True if the user credentials are valid
     */
    public function validateCredentials($username, $password)
    {
        if ($this->userExists($username)) {
            $user = $this->Orm->Users->username($username);

            return $this->Password->verify($password, $user->password);
        } else {
            return false;
        }
    }

    /**
     * Log in a user.
     *
     * @param  string $username   The username
     * @param  string $session_id The name of the session ID
     * @return array  An array containing the user information
     */
    public function login($username, $session_id = 'user_auth')
    {
        // Load Session component
        $session = new Session();

        // Clear any persistent session cookie already set
        $session->clearSessionCookie('/', '', false, true);

        // Remove partial login
        $session->clear($session_id);

        // Log the user
        $user             = $this->getUser($username);
        $user->ip_address = $_SERVER['REMOTE_ADDR'];
        $session->write('admin_auth', $user);

        return $user;
    }

    /**
     * Check if a user exists in the database.
     *
     * @param string $username The username to check
     * @param bool True if the user exists, false otherwise
     */
    public function userExists($username)
    {
        return !empty($this->Orm->Users->username($username));
    }
}
