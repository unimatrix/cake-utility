<?php

namespace Unimatrix\Utility\Lib;

use Cake\Core\Configure;

/**
 * Paranoia
 * Encrypts and decrypts integers, strings or arrays based on a secret key
 *
 * Usage example:
 * ---------------------------------
 * // app.php
 * 'Paranoia' => 'secret_key',
 *
 * // controller
 * use Unimatrix\Utility\Lib\Paranoia;
 *
 * $encrypted = Paranoia::encrypt('string_to_encrypt');
 * $decrypted = Paranoia::decrypt('string_to_decrypt');
 *
 * @author Flavius
 * @version 0.4
 */
class Paranoia {
    /**
     * Get secret key from either
     * the config value or from parameter
     *
     * @param string $s
     * @return string
     */
    private static function secret($s = null) {
        return is_null($s) ? Configure::read('Paranoia') : $s;
    }

    /**
     * Encrypt
     *
     * @param integer|string|array $a
     * @param string $s Paranoia secret
     * @return null|string
     */
    public static function encrypt($a = null, $s = null) {
        $s = self::secret($s);
        $z = openssl_encrypt(serialize($a), 'AES-256-CBC', md5($s), OPENSSL_RAW_DATA, substr(md5($s), 0, 16));

        return is_null($a) ? null : strtr(base64_encode($z), '+/=', '-_.');
    }

    /**
     * Decrypt
     *
     * @param string $a
     * @param string $s Paranoia secret
     * @return null|string
     */
    public static function decrypt($a = null, $s = null) {
        $s = self::secret($s);
        $z = openssl_decrypt(base64_decode(strtr($a, '-_.', '+/=')), 'AES-256-CBC', md5($s), OPENSSL_RAW_DATA, substr(md5($s), 0, 16));

        return is_null($a) ? null : unserialize(rtrim($z, "\0"));
    }
}