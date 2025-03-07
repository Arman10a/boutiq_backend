<?php

if (! function_exists('_dd')) {
    function _dd(mixed ...$vars) {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: *');
        header('Access-Control-Allow-Headers: *');
        dd(...func_get_args());
    }
}
