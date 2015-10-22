<?php

namespace Borg\Lib;

/**
 * Paranoia
 * Encrypts and decrypts integers, strings or arrays based on a secret key
 *
 * @author Borg
 * @version 0.1
 */
class Paranoia {
    // default
    const SECRET = 'SSQWIIISLKISIS351Nwtt';

    /**
     * Encrypt
     *
     * @param integer|string|array $a
     * @param string $s Paranoia secret
     * @return null|string
     */
    public static function encrypt($a = null, $s = self::SECRET) {
        return is_null($a) ? null : strtr(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($s), serialize($a), MCRYPT_MODE_CBC, md5($s))), '+/=', '-_@');
    }

    /**
     * Decrypt
     *
     * @param string $a
     * @param string $s Paranoia secret
     * @return null|string
     */
    public static function decrypt($a = null, $s = self::SECRET) {
        return is_null($a) ? null : unserialize(rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($s), base64_decode(strtr($a, '-_@', '+/=')), MCRYPT_MODE_CBC, md5($s)), "\0"));
    }

    /**
     * Base64 encode helper
     * @param string $x
     * @return null|string
     */
    public static function safe_b64encode($x = null) {
        return is_null($x) ? null : str_replace(array('+', '/', '='), array('-', '#', ''), base64_encode($x));
    }

    /**
     * Base64 decode helper
     * @param string $x
     * @return null|string
     */
    public static function safe_b64decode($x = null) {
        if(is_null($x))
            return null;

        $d = str_replace(array('-', '#'), array('+', '/'), $x);
        if(strlen($d) % 4)
            $d .= substr('====', strlen($d) % 4);

        return base64_decode($d);
    }
}