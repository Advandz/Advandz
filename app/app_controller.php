<?php
/**
 * The parent controller for the application.
 *
 * @package Advandz
 * @subpackage Advandz.app
 * @copyright Copyright (c) 2012-2017 CyanDark, Inc. All Rights Reserved.
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author The Advandz Team <team@advandz.com>
 */
namespace Advandz\App\Controller;

use Controller;
use Loader;

class AppController extends Controller {
    /**
     * Pre-Action, This method called before the index method, or controller specified action.
     */
    public function preAction() {
        Loader::loadHelpers($this, ['Cdnjs']);
        
        $libs = $this->Cdnjs->loadLibraries(['jquery']);
        $this->structure->set("libs", $libs);
    }
    
    #
    # TODO: Define any methods, load any models or components or anything else
    # here that you would like to be available to all controllers that extend
    # this special AppController.  This is great for loading certain language
    # files that are used throughout the application.
    # (e.g. $this->loadLang("langfile", "en_us"))
    #
}