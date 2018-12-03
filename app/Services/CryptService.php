<?php
namespace App\Services;

class CryptService
{
    public static function encrypt($type = 'rsa', $key, $data) {
        switch ($type) {
            case 'rsa':
                return self::rsaEncrypt($key, $data);
            default:
                return '';
        }
    }

    public static function decrypt($type = 'rsa', $key, $data) {
        switch ($type) {
            case 'rsa':
                return self::rsaDecrypt($key, $data);
            default:
                return [];
        }
    }

    public static function rsaEncrypt($key, $data) {
        $key  = openssl_get_publickey($key);
        if (is_array($data)) {
            $data = json_encode($data);
        }
        $maxlength = 117;
        $output    = '';
        while ($data) {
          $input = substr($data, 0, $maxlength);
          $data = substr($data, $maxlength);

          openssl_public_encrypt($input, $encrypted, $key);
          /*
          if ($type=='private') {
            openssl_private_encrypt($input, $encrypted, $key);
          }else{
          }*/

          $output.= $encrypted;
        }
        return base64_encode($output);
    }

    public static function rsaDecrypt($key, $data) {
        $key  = openssl_get_privatekey($key);
        $data = base64_decode($data);
        $maxlength = 128;
        $output = '';
        while ($data) {
          $input = substr($data, 0, $maxlength);
          $data  = substr($data, $maxlength);
          openssl_private_decrypt($input,$out,$key);
          /*
          if($type=='private'){
          }else{
            openssl_public_decrypt($input,$out,$key);
          }*/

          $output .= $out;
        }
        return $output;
    }
}
