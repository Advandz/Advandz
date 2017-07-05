<?php
/**
 * The parent controller for the application.
 *
 * @package Advandz
 * @subpackage Advandz.app
 * @copyright Copyright (c) 2016-2017 Advandz, LLC. All Rights Reserved.
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author The Advandz Team <team@advandz.com>
 */

namespace Advandz\App;

use Advandz\Helper\Cdnjs;
use Advandz\Core\Controller;

class AppController extends Controller
{
    /**
     * Pre-Action, This method called before the index method, or controller specified action.
     */
    public function preAction()
    {
        // Load the necessary helpers
        $cdnjs = new Cdnjs();

        // Load the necessary libraries from CDNJS
        $libs = $cdnjs->loadLibraries(['jquery']);

        // Set the structure variables
        $this->structure->set('libs', $libs);
    }

    //
    // TODO: Define any methods that you would like to use in any of your other
    // controller that extend this class.
    //
}
