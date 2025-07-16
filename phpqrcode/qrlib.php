<?php
/*
 * PHP QR Code encoder
 * (c) Kazuhiko Arase
 * https://github.com/kazuhikoarase/qrcode-generator
 */

class QRcode {

    public static function png($text, $outfile = false, $level = QR_ECLEVEL_L, $size = 3, $margin = 4) {
        $enc = urlencode($text);
        $url = "https://chart.googleapis.com/chart?cht=qr&chs=" . ($size * 25) . "x" . ($size * 25) . "&chld=$level|$margin&chl=$enc";

        if ($outfile) {
            $img = file_get_contents($url);
            file_put_contents($outfile, $img);
        } else {
            header('Content-Type: image/png');
            readfile($url);
        }
    }
}

define('QR_ECLEVEL_L', 'L');
define('QR_ECLEVEL_M', 'M');
define('QR_ECLEVEL_Q', 'Q');
define('QR_ECLEVEL_H', 'H');
