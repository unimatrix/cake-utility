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
 * @version 0.3
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
    public static function encrypt($a = null, $s = null) { $s = self::secret($s);
        return is_null($a) ? null : strtr(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($s), serialize($a), MCRYPT_MODE_CBC, md5($s))), '+/=', '-_.');
    }

    /**
     * Decrypt
     *
     * @param string $a
     * @param string $s Paranoia secret
     * @return null|string
     */
    public static function decrypt($a = null, $s = null) { $s = self::secret($s);
        return is_null($a) ? null : unserialize(rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($s), base64_decode(strtr($a, '-_.', '+/=')), MCRYPT_MODE_CBC, md5($s)), "\0"));
    }
}