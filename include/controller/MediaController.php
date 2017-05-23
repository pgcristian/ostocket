<?php
namespace controller;

class MediaController {

    public function defaultAction($request_path) {

        $file = stock_ASSETS_DIR . $request_path;


        if (file_exists($file)) {

            if ($this->endsWith($file, '.js')) {
                header('Content-type: text/javascript');
            } else if ($this->endsWith($file, '.css')) {
                header('Content-type: text/css');
            } else if ($this->endsWith($file, '.png')) {
                header('Content-type: image/png');
            }
            echo file_get_contents($file);
        } else {
            echo 'File does not exist';
        }
    }

    public function redirectAction($request_path) {
        header("Location: " . OST_WEB_ROOT . $request_path);
        die();
    }

    public function endsWith($haystack, $needle) {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }

}
