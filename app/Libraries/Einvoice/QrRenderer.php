<?php

namespace App\Libraries\Einvoice;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

/** Tiny wrapper around chillerlan/php-qrcode that produces dompdf-friendly
 *  base64 data URIs for the IRBM public-portal QR. */
class QrRenderer
{
    public static function dataUri(string $payload, int $scale = 4): string
    {
        $opts = new QROptions([
            'outputType'  => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel'    => QRCode::ECC_M,
            'scale'       => $scale,
            'imageBase64' => true,
        ]);
        return (new QRCode($opts))->render($payload);
    }
}
