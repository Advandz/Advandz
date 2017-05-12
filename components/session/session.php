<?php
/**
 * A database powered Session driver. Requires the Record component.
 *
 * @package Advandz
 * @subpackage Advandz.components.session
 * @copyright Copyright (c) 2010-2013 Phillips Data, Inc. All Rights Reserved.
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Cody Phillips <therealclphillips.woop@gmail.com>
 */

namespace Advandz\Component;

use PDO;
use Configure;

class Session
{
    /**
     * @var Record Record object class
     */
    private $Record;

    /**
     * @var int Time to Live (seconds)
     */
    private $ttl;

    /**
     * @var string Name of the session table
     */
    private $tbl;

    /**
     * @var string Name of the session ID field
     */
    private $tblid;

    /**
     * @var string Name of the session expire date field
     */
    private $tblexpire;

    /**
     * @var string Name of the session value field
     */
    private $tblvalue;

    /**
     * @var string The cookie session ID
     */
    private $csid;

    /**
     * @var string The session ID
     */
    private $sid;

    /**
     * @var int Session instances
     */
    private static $instances = 0;

    /**
     * Initialize the Session.
     */
    public function __construct()
    {
        \Loader::load(COMPONENTDIR . 'record' . DS . 'record.php');
        Configure::load('session');

        $this->Record = new Record();
        $this->Record->setFetchMode(PDO::FETCH_OBJ);

        $this->sessionSet(
            Configure::get('Session.ttl'),
            Configure::get('Session.tbl'),
            Configure::get('Session.tbl_id'),
            Configure::get('Session.tbl_exp'),
            Configure::get('Session.tbl_val'),
            Configure::get('Session.session_name'),
            Configure::get('Session.session_httponly')
        );
    }

    /**
     * Clean up any loose ends.
     */
    public function __destruct()
    {
        // Write and close the session (if not already handled)
        if (--self::$instances == 0) {
            session_write_close();
        }
    }

    /**
     * Return the session ID.
     *
     * @return string The session ID
     */
    public function getSid()
    {
        return $this->sid;
    }

    /**
     * Read Session information for the given index.
     *
     * @param  string $name    The name of the index to read
     * @param  mixed  $persist
     * @return mixed  The value stored in $name of the session, or an empty string.
     */
    public function read($name, $persist = true)
    {
        if (isset($_SESSION[$name])) {
            $val = $_SESSION[$name];

            if ($persist == false) {
                $this->clear($name);
            }

            return $val;
        }

        return null;
    }

    /**
     * Writes the given session information to the given index.
     *
     * @param string $name  The index to write to
     * @param mixed  $value The value to write
     */
    public function write($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    /**
     * Unsets the value of a given session variable, or the entire session array
     * of all values.
     *
     * @param string $name The session variable to unset
     */
    public function clear($name = null)
    {
        if ($name) {
            unset($_SESSION[$name]);
        } else {
            foreach ($_SESSION as $key => $value) {
                unset($_SESSION[$key]);
            }
        }
    }

    /**
     * Set the session cookie.
     *
     * @param string $path     The path for this cookie, default is the current URI
     * @param string $domain   The domain that the cookie is available to, default is the current domain
     * @param bool   $secure   Whether or not the cookie should be transmitted over a secure connection from the client
     * @param bool   $httponly Whether or not the cookie should be flagged for HTTP only
     */
    public function setSessionCookie($path = '', $domain = '', $secure = false, $httponly = false)
    {
        if (version_compare(phpversion(), '5.2.0', '>=')) {
            setcookie(Configure::get('Session.cookie_name'), $this->getSid(), time() + Configure::get('Session.cookie_ttl'), $path, $domain, $secure, $httponly);
        } else {
            setcookie(Configure::get('Session.cookie_name'), $this->getSid(), time() + Configure::get('Session.cookie_ttl'), $path, $domain, $secure);
        }
    }

    /**
     * Updates the session cookie expiration date so that it remains active without expiring.
     *
     * @param string $path     The path for this cookie, default is the current URI
     * @param string $domain   The domain that the cookie is available to, default is the current domain
     * @param bool   $secure   Whether or not the cookie should be transmitted over a secure connection from the client
     * @param bool   $httponly Whether or not the cookie should be flagged for HTTP only
     */
    public function keepAliveSessionCookie($path = '', $domain = '', $secure = false, $httponly = false)
    {
        if (isset($_COOKIE[Configure::get('Session.cookie_name')])) {
            $this->setSessionCookie($path, $domain, $secure, $httponly);
        }
    }

    /**
     * Deletes the session cookie.
     *
     * @param string $path   The path for this cookie, default is the current URI
     * @param string $domain The domain that the cookie is available to, default is the current domain
     * @param bool   $secure Whether or not the cookie should be transmitted over a secure connection from the client
     */
    public function clearSessionCookie($path = '', $domain = '', $secure = false)
    {
        if (isset($_COOKIE[Configure::get('Session.cookie_name')])) {
            setcookie(Configure::get('Session.cookie_name'), '', time() - Configure::get('Session.cookie_ttl'), $path, $domain, $secure);
        }
    }

    /**
     * Set session handler callback methods and start the session.
     *
     * @param int    $ttl          Time to Live (seconds)
     * @param string $tbl          Name of the session table
     * @param string $tblid        Name of the session ID field
     * @param string $tblexpire    Name of the session expire date field
     * @param string $tblvalue     Name of the session value field
     * @param bool   $httponly     Whether or not the cookie should be flagged for HTTP only
     * @param mixed  $session_name
     */
    private function sessionSet($ttl, $tbl, $tblid, $tblexpire, $tblvalue, $session_name, $httponly)
    {
        $this->ttl       = $ttl;
        $this->tbl       = $tbl;
        $this->tblid     = $tblid;
        $this->tblexpire = $tblexpire;
        $this->tblvalue  = $tblvalue;

        if (self::$instances == 0) {
            // Ensure session is HTTP Only
            if (version_compare(phpversion(), '5.2.0', '>=')) {
                $session_params = session_get_cookie_params();
                session_set_cookie_params($session_params['lifetime'], $session_params['path'], $session_params['domain'], $session_params['secure'], $httponly);
                unset($session_params);
            }

            session_name($session_name);

            session_set_save_handler(
                [&$this, 'sessionOpen'],
                [&$this, 'sessionClose'],
                [&$this, 'sessionSelect'],
                [&$this, 'sessionWrite'],
                [&$this, 'sessionDestroy'],
                [&$this, 'sessionGarbageCollect']
            );

            // If a cookie is available, attempt to use that session and reset
            // the ttl to use the cookie ttl, but only if we don't have a current session cookie as well
            if (isset($_COOKIE[Configure::get('Session.cookie_name')]) && !isset($_COOKIE[session_name()])) {
                if ($this->setKeepAlive($_COOKIE[Configure::get('Session.cookie_name')])) {
                    $this->setCsid($_COOKIE[Configure::get('Session.cookie_name')]);
                    $this->ttl = Configure::get('Session.cookie_ttl');
                }
            } elseif (isset($_COOKIE[Configure::get('Session.cookie_name')]) && isset($_COOKIE[session_name()]) && $_COOKIE[Configure::get('Session.cookie_name')] == $_COOKIE[session_name()]) {
                $this->ttl = Configure::get('Session.cookie_ttl');
            }

            // Start the session
            session_start();
        }
        self::$instances++;
    }

    /**
     * Sets the cookie session ID.
     *
     * @param string $csid The cookie session ID
     */
    private function setCsid($csid)
    {
        $this->csid = $csid;
    }

    /**
     * Reawake the session using the given cookie session id.
     *
     * @param  string $csid The cookie session ID
     * @return bool   If Keep-Alive has been set
     */
    private function setKeepAlive($csid)
    {
        $row = $this->Record->select($this->tblvalue)
            ->from($this->tbl)
            ->where($this->tblid, '=', $csid)
            ->where($this->tblexpire, '>', date('Y-m-d H:i:s'))
            ->fetch();

        if ($row) {
            // Set the session ID to that from our cookie so when we start
            // the session, PHP will pick up the old session automatically.
            session_id($csid);

            return true;
        }

        return false;
    }

    /**
     * Open the given session. Not implemented, included only for compatibility.
     *
     * @param  string $session_path The path to the session
     * @param  string $session_name The name of the session
     * @return bool   True, always
     */
    private function sessionOpen($session_path, $session_name)
    {
        return true;
    }

    /**
     * Close a session. Not implemented, included only for compatibility.
     *
     * @return bool True, always
     */
    private function sessionClose()
    {
        return true;
    }

    /**
     * Reads the session data from the database.
     *
     * @param  int    $sid Session ID
     * @return string The table value
     */
    private function sessionSelect($sid)
    {
        // We need to use the sid set so we can write a cookie if needed
        $this->sid = $sid;

        $row = $this->Record->select($this->tblvalue)
            ->from($this->tbl)
            ->where($this->tblid, '=', $this->sid)
            ->where($this->tblexpire, '>', date('Y-m-d H:i:s'))
            ->fetch();

        if ($row) {
            return $row->{$this->tblvalue};
        }

        return '';
    }

    /**
     * Writes the session data to the database.
     * If that SID already exists, then the existing data will be updated.
     *
     * @param string $sid   The session ID
     * @param string $value The value to write to the session
     */
    private function sessionWrite($sid, $value)
    {
        // We need to use the sid set so we can write a cookie if needed
        $this->sid = $sid;

        $expiration = date('Y-m-d H:i:s', time() + $this->ttl);

        $this->Record->duplicate($this->tblexpire, '=', $expiration)
            ->duplicate($this->tblvalue, '=', $value)
            ->insert($this->tbl, [$this->tblid => $sid, $this->tblexpire => $expiration, $this->tblvalue => $value]);
    }

    /**
     * Deletes all session information for the given session ID.
     *
     * @param string $sid The session ID
     */
    private function sessionDestroy($sid)
    {
        $this->Record->from($this->tbl)->where($this->tblid, '=', $sid)->delete();
    }

    /**
     * Deletes all sessions that have expired.
     *
     * @param  int $lifetime TTL of the session
     * @return int Affected rows
     */
    private function sessionGarbageCollect($lifetime)
    {
        $this->Record->from($this->tbl)
            ->where($this->tblexpire, '<', date('Y-m-d H:i:s', time() - $lifetime))
            ->delete();

        return $this->Record->affectedRows();
    }
}
