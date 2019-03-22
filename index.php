<?php
require __DIR__ . "/_init.php";
require __DIR__ . "/_taint.php";
use core\Taint;

class Input {
    public $val;
    public $country;

    public function rules() {
        return array(
            "val" => array(),
            "country" => array()
        );
    }
}
Taint::init();
$input = Taint::get(new Input());
if (is_array($input)) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 400 Invalid Input', true, 400);
    echo "Invalid input.";
    error_log("xsreg::lookup invinput=" . print_r($input, true));
    exit;
}

    // http://stackoverflow.com/questions/12082507/php-most-lightweight-psr-0-compliant-autoloader
    spl_autoload_register(function($c){@include preg_replace('#\\\|_(?!.+\\\)#','/',$c).'.php';});

        $input->val = preg_replace("/[^0-9]/", "", $input->val);

        // Lookup
        try {
            $util = \libphonenumber\PhoneNumberUtil::getInstance();
            $proto = $util->parse($input->val, $input->country);
            if (! $util->isValidNumber($proto)) {
                throw new \Exception("Invalid phonenumber=" . $input->val . " for country=" . $input->country);
            }
            $res = $util->format($proto, \libphonenumber\PhoneNumberFormat::E164);
            $res = str_replace("+", "", $res);

        } catch (\Exception $e) {
            //error_log("lookup: phone err=" . $e->getMessage());
            echo json_encode(array(
                "ok" => false,
                "msg" => "Invalid phone number"
            ));
            exit;
        }

