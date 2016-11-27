<?php
/**
 * Provides secure Bcrypt hashing for storing user passwords.
 *
 * @package Advandz
 * @subpackage Advandz.components.password
 */
class Password extends Model {
	
    /**
     * Generates a secure and strong Password
     *
     * @param integer $length Length of the password
     * @param  boolean $numbers Include numbers in the password
     * @param  boolean $symbols Include symbols in the password
     * @return String 	$pass Return the password generated
     */ 

    public function generate($length, $numbers, $symbols) {
    	// Define the length of the password
    	$length = 10;
    	// Define the numbers
    	$numbers = "0123456789";
    	// Define the caracters
    	$symbols = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz!#$%\*\/?\|^\{\}`~&'\+=_.-";
    	$length_symbols = strlen($symbols, $numbers);
    	// Define the variable that will contain the password
    	$pass = "";

    	// Create the password
    	for ($i=1; $i<=$length ; $i++) { 
    		// Define a random number between 0 and the length of the password - 1
    		$n_random = rand(0,$length_symbols-1);

    		// Create the password
    		$pass .= substr($numbers,$symbols,$n_random, 1);
    	}
    	return $pass;
    }

    echo $pass;

    public function hash(){

    }
}
