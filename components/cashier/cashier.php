<?php
/**
 * Provides a number of methods for manage your billing subscriptions, with built-in gateways integrations.
 *
 * @package Advandz
 * @subpackage Advandz.components.cashier
 * @copyright Copyright (c) 2012-2017 CyanDark, Inc. All Rights Reserved.
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author The Advandz Team <team@advandz.com>
 */

namespace Advandz\Component;

class Cashier
{
    /**
     * @var string The current currency code
     */
    private $currency = null;

    /**
     * Initializes a Gateways Class.
     *
     * @param  string $gateway The payment gateway to load
     * @return mixed  Returns a Table Object if the table exists
     */
    public function _($gateway)
    {
        //
        // TODO: Create the Gateway classes for Stripe and Braintree.
        //
    }

    public function getCurrency()
    {
        
    }

    public function setCurrency()
    {

    }

    public function formatAmount()
    {

    }

    public function setAmount()
    {

    }

    public function getAmount()
    {
        
    }
}
