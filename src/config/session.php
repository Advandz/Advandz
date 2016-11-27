<?php
/**
 * Session settings.
 *
 * @package Advandz
 * @copyright Copyright (c) 2012-2017 CyanDark, Inc. All Rights Reserved.
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author The Advandz Team <team@advandz.com>
 */

// The number of seconds to keep a session alive.
Configure::set("Session.ttl", 1800);
// The number of seconds to keep a cookie stored session alive
Configure::set("Session.cookie_ttl", 7*24*60*60);
// The name of the session cookie to be used by the session (if available)
Configure::set("Session.cookie_name", "csid");
// The name of the sessions database table
Configure::set("Session.tbl", "sessions");
// The name of the id field in the sessions database table
Configure::set("Session.tbl_id", "id");
// The name of the expiration field in the sessions database table
Configure::set("Session.tbl_exp", "expire");
// The name of the value field in the sessions database table
Configure::set("Session.tbl_val", "value");
// The name of the session
Configure::set("Session.session_name", "sid");
// Whether or not enable HTTP only session cookies
Configure::set("Session.session_httponly", true);
