<?php

namespace App\Libraries;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfGenerator
{
    /**
     * Render an HTML view to PDF and return the binary string.
     */
    public static function fromView(string $view, array $data = [], string $paper = 'A4', string $orientation = 'portrait'): string
    {
        $html = view($view, $data);
        return self::fromHtml($html, $paper, $orientation);
    }

    public static function fromHtml(string $html, string $paper = 'A4', string $orientation = 'portrait'): string
    {
        $opts = new Options();
        $opts->set('isRemoteEnabled', true);
        $opts->set('defaultFont', 'DejaVu Sans');

        $pdf = new Dompdf($opts);
        $pdf->loadHtml($html);
        $pdf->setPaper($paper, $orientation);
        $pdf->render();
        return $pdf->output();
    }

    /**
     * Save the rendered PDF to writable/uploads/<subdir>/<name>.pdf and return relative path.
     */
    public static function saveFromView(string $view, array $data, string $subdir, string $filenameBase): string
    {
        $bin = self::fromView($view, $data);
        $dir = WRITEPATH . 'uploads/' . trim($subdir, '/');
        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $rel = trim($subdir, '/') . '/' . $filenameBase . '.pdf';
        file_put_contents(WRITEPATH . 'uploads/' . $rel, $bin);
        return $rel;
    }
}
