<?php

namespace App\Libraries;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelExporter
{
    /**
     * Stream a list of associative rows as an .xlsx download.
     *
     * @param array<int,array<string,scalar|null>> $rows
     * @param array<int,string>                    $headers
     */
    public static function download(string $filename, array $headers, array $rows): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // header
        $col = 1;
        foreach ($headers as $h) {
            $sheet->setCellValue([$col++, 1], $h);
        }
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->getFont()->setBold(true);

        // body
        $r = 2;
        foreach ($rows as $row) {
            $col = 1;
            foreach ($row as $val) {
                $sheet->setCellValue([$col++, $r], $val);
            }
            $r++;
        }

        foreach (range('A', $sheet->getHighestColumn()) as $colLetter) {
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        (new Xlsx($spreadsheet))->save('php://output');
        exit;
    }

    public static function readRows(string $filePath): array
    {
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);
        return $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
    }
}
