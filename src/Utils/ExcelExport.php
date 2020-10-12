<?php

namespace Sintattica\Atk\Utils;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Sintattica\Atk\Core\Tools;

class ExcelExport
{
    public function export($arr_header, $arr_data, $fileName)
    {

        $fileName =  str_replace(' ', '_', $fileName);
        $dangerous_characters = array(" ", '"', "'", "&", "/", "\\", "?", "#");
        $fileName = str_replace($dangerous_characters, '_', $fileName);

        ob_end_clean();


        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->setTitle('Export');

        if (!empty($arr_header)) {
            array_unshift($arr_data, $arr_header);
        }

        $spreadsheet->setActiveSheetIndex(0)->fromArray($arr_data, null, 'A1');

        $lastColumn = $spreadsheet->getActiveSheet()->getHighestColumn(1);
        $highestColumnIndex = Coordinate::columnIndexFromString($lastColumn);

        if (!empty($arr_header)) {
            $spreadsheet->setActiveSheetIndex(0)->setAutoFilter('A1:' . $lastColumn . '1');
        }

        for ($i = 1; $i <= $highestColumnIndex; $i++) {
            $current_col = Coordinate::stringFromColumnIndex($i);
            $spreadsheet->getActiveSheet()->getColumnDimension($current_col)->setAutoSize(true);

            if (!empty($arr_header)) {
                $spreadsheet->getActiveSheet()->getStyle($current_col . '1')->getFont()->setBold(true);
            }

        }

        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '.xlsx"');
        header('Cache-Control: max-age=0');

        // Fix for downloading (Office) documents using an SSL connection in
        // combination with MSIE.
        if (
            ($_SERVER["SERVER_PORT"] == "443" || Tools::atkArrayNvl($_SERVER, 'HTTP_X_FORWARDED_PROTO') == "https")
            && preg_match("/msie/i", $_SERVER["HTTP_USER_AGENT"])) {
            header('Pragma: public');
        } else {
            header('Pragma: no-cache');
        }

        header('Expires: 0');

        $writer->save('php://output');


        flush();

        exit;

    }


}
