<?php
namespace App\Helpers;
class AuthHelper {
    public function verifyToken($token) {
        if ($token == 'helloalfred') {
            return true;
        } else {
            return false;
        }
    }
}