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
class Cashier {
    /**
     * Initializes a Gateways Class
     *
     * @param string $table Called function
     * @return mixed Returns a Table Object if the table exists
     */
    public function useGateway($table) {
        #
        # TODO: Create the Gateway classes for PayPal, BitPay and Stripe.
        #
    }
}