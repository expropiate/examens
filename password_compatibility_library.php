<?php
/**
 * A Compatibility library with PHP 5.5's simplified password hashing API.
 *
 * @author Anthony Ferrara <ircmaxell@php.net>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 2012 The Authors
 */

if (!defined('PASSWORD_DEFAULT')) {
    define('PASSWORD_BCRYPT', 1);
    define('PASSWORD_DEFAULT', PASSWORD_BCRYPT);

    /**
     * Hash the password using the specified algorithm
     *
     * @param string $password The password to hash
     * @param int    $algo     The algorithm to use (Defined by PASSWORD_* constants)
     * @param array  $options  The options for the algorithm to use
     *
     * @return string|false The hashed password, or false on error.
     */
    function password_hash($password, $algo, array $options = array()) {
        if (!function_exists('crypt')) {
            trigger_error("Crypt must be loaded for password_hash to function", E_USER_WARNING);
            return false;
        }

        if (!is_string($password)) {
            trigger_error("password_hash(): Password must be a string", E_USER_WARNING);
            return false;
        }

        if (!is_int($algo)) {
            trigger_error("password_hash() expects parameter 2 to be long, " . gettype($algo) . " given", E_USER_WARNING);
            return false;
        }

        // Extract bcrypt handling into a separate function
        if ($algo === PASSWORD_BCRYPT) {
            return handle_bcrypt_hashing($password, $options);
        }

        trigger_error(sprintf("password_hash(): Unknown password hashing algorithm: %s", $algo), E_USER_WARNING);
        return false;
    }

    /**
     * Handle the bcrypt password hashing.
     *
     * @param string $password The password to hash
     * @param array  $options  The options for bcrypt
     *
     * @return string|false The hashed password, or false on error.
     */
    function handle_bcrypt_hashing($password, array $options) {
        $cost = isset($options['cost']) ? $options['cost'] : 10;
        if ($cost < 4 || $cost > 31) {
            trigger_error(sprintf("password_hash(): Invalid bcrypt cost parameter specified: %d", $cost), E_USER_WARNING);
            return false;
        }

        $salt = generate_salt();
        $hash_format = sprintf("$2y$%02d$", $cost);
        $hash = $hash_format . $salt;

        $ret = crypt($password, $hash);
        if (!is_string($ret) || strlen($ret) <= 13) {
            return false;
        }

        return $ret;
    }

    /**
     * Generate a random salt for bcrypt hashing.
     *
     * @return string The generated salt.
     */
    function generate_salt() {
        $raw_salt_len = 16;
        $required_salt_len = 22;
        $buffer = '';
        $buffer_valid = false;

        // Try different methods to generate the salt
        if (function_exists('mcrypt_create_iv')) {
            $buffer = mcrypt_create_iv($raw_salt_len, MCRYPT_DEV_URANDOM);
            if ($buffer) {
                $buffer_valid = true;
            }
        }

        if (!$buffer_valid && function_exists('openssl_random_pseudo_bytes')) {
            $buffer = openssl_random_pseudo_bytes($raw_salt_len);
            if ($buffer) {
                $buffer_valid = true;
            }
        }

        if (!$buffer_valid && is_readable('/dev/urandom')) {
            $f = fopen('/dev/urandom', 'r');
            $buffer = fread($f, $raw_salt_len);
            fclose($f);
            $buffer_valid = true;
        }

        if (!$buffer_valid) {
            // Fallback to random generation
            for ($i = 0; $i < $raw_salt_len; $i++) {
                $buffer .= chr(mt_rand(0, 255));
            }
        }

        // Ensure the salt is of correct length
        return str_replace('+', '.', base64_encode(substr($buffer, 0, $required_salt_len)));
    }

    /**
     * Get information about the password hash.
     *
     * @param string $hash The password hash to extract info from
     *
     * @return array The array of information about the hash.
     */
    function password_get_info($hash) {
        $return = array(
            'algo' => 0,
            'algoName' => 'unknown',
            'options' => array(),
        );
        if (substr($hash, 0, 4) == '$2y$' && strlen($hash) == 60) {
            $return['algo'] = PASSWORD_BCRYPT;
            $return['algoName'] = 'bcrypt';
            list($cost) = sscanf($hash, "$2y$%d$");
            $return['options']['cost'] = $cost;
        }
        return $return;
    }

    /**
     * Determine if the password hash needs to be rehashed according to the options provided
     *
     * @param string $hash    The hash to test
     * @param int    $algo    The algorithm used for new password hashes
     * @param array  $options The options array passed to password_hash
     *
     * @return boolean True if the password needs to be rehashed.
     */
    function password_needs_rehash($hash, $algo, array $options = array()) {
        $info = password_get_info($hash);
        if ($info['algo'] != $algo) {
            return true;
        }

        if ($algo === PASSWORD_BCRYPT && isset($options['cost']) && $options['cost'] !== $info['options']['cost']) {
            return true;
        }

        return false;
    }

    /**
     * Verify a password against a hash using a timing attack resistant approach
     *
     * @param string $password The password to verify
     * @param string $hash     The hash to verify against
     *
     * @return boolean If the password matches the hash
     */
    function password_verify($password, $hash) {
        if (!function_exists('crypt')) {
            trigger_error("Crypt must be loaded for password_verify to function", E_USER_WARNING);
            return false;
        }
        $ret = crypt($password, $hash);
        if (!is_string($ret) || strlen($ret) != strlen($hash) || strlen($ret) <= 13) {
            return false;
        }

        $status = 0;
        for ($i = 0; $i < strlen($ret); $i++) {
            $status |= (ord($ret[$i]) ^ ord($hash[$i]));
        }

        return $status === 0;
    }
}
