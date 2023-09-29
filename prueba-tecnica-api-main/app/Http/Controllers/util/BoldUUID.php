<?php

namespace App\Http\Controllers\util;

use App\Http\Controllers\Controller;
use Exception;

/**
 * @author      Daniel Bolivar.
 * @version     v1.0.0
 * @internal    Libreria para creacion de UUID. 
 */

class BoldUUID extends Controller{

    public function generateUUID() {
        $uniqid = $this->uniqidReal(16);
        return $uniqid;
    }

    private function uniqidReal($lenght = 13) {
        if (function_exists("random_bytes")) {
            $bytes = random_bytes(ceil($lenght / 2));
        } elseif (function_exists("openssl_random_pseudo_bytes")) {
            $bytes = openssl_random_pseudo_bytes(ceil($lenght / 2));
        } else {
            throw new Exception("no cryptographically secure random function available");
        }
        return substr(bin2hex($bytes), 0, $lenght);
    }

    function generateUUIDv4() {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // Version 4
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // Variant RFC 4122
        $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        return $uuid;
    }

}

?>