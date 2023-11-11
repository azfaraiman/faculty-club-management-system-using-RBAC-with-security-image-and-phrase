<?php
require_once 'config.php';
function encryptData($data, $key, $iv) {
    $encrypted_data = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
    return $encrypted_data;
}

function decryptData($encrypted_data, $key, $iv) {
    $decrypted_data = openssl_decrypt($encrypted_data, 'AES-256-CBC', $key, 0, $iv);
    return $decrypted_data;
}
?>