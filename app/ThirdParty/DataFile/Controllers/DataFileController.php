<?php

namespace DataFile\Controllers;

use CodeIgniter\Controller;
use Config\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer;
use PhpOffice\PhpSpreadsheet\Reader;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style;

class DataFileController extends Controller {
  protected $config;
  public $numberOfFields = 0; // 열(column)의 개수
  public $numberOfRecords = 0; // 배열의 모든 요소의 개수 / 행의 개수. count는 1부터 시작이라 -1 // 행(row)의 개수

  public function __construct() {
    $this->config = config('DataFile');
  }

  public function exportOptions(Array $defaultOptions = [], Array $headerOptions = [],  Array $bodyOptions = []) {
    if ( !empty($defaultOptions) ) $this->config->defaultOpts = $defaultOptions;
    if ( !empty($headerOptions) ) $this->config->headerOpts = $headerOptions;
    if ( !empty($bodyOptions) ) $this->config->bodyOpts = $bodyOptions;
  }

  public function exportSetOption($activeSheet, $column, $type = 'header') {
    if ( !empty($this->config->defaultOpts) ) {
      if ( isset($this->config->defaultOpts['width']) ) $activeSheet->getDefaultColumnDimension()->setWidth($this->config->defaultOpts['width']);
    }

    if ( $type == 'header' ) {
      if ( !empty($this->config->headerOpts) ) {
        // if ( isset($this->config->headerOpts['font_size']) ) {
        //   $activeSheet->getStyle($column)->getFont()->setSize($this->config->headerOpts['font_size']);
        // }
        
        if ( isset($this->config->headerOpts['bold']) ) {
          $activeSheet->getStyle($column)->getFont()->setBold($this->config->headerOpts['bold']);
        }

        if ( isset($this->config->headerOpts['fill']) ) {
          $activeSheet->getStyle($column)->getFill()->setFillType(Style\Fill::FILL_SOLID)->getStartColor()->setARGB($this->config->headerOpts['fill']['color']);
        }

        if ( isset($this->config->headerOpts['align_vertical'])) {
          if ( $this->config->headerOpts['align_vertical'] == 'center' ) {
            $activeSheet->getStyle($column)->getAlignment()->setVertical(Style\Alignment::VERTICAL_CENTER);
          }
          // $sheet->getStyle($head[0])->getAlignment()->setVertical(Style\Alignment::VERTICAL_CENTER)->setWrapText(true);  // 세로 가운데 정렬&줄바꿈 허용
        }

        if ( isset($this->config->headerOpts['set_wrap'])) {
          $activeSheet->getStyle($column)->getAlignment()->setWrapText(true);  // 줄바꿈 허용
        }          
      }
    }
    
    return $column;
  }

  public function exportData(Array $header, Array $data = [], String $fileName = NULL, String $fileType = 'csv', Int $headColumnCnt = NULL, Array $opts = [] ) {
  // public function exportData(Array $header, Array $data = [], Array $opts = [], String $fileName = NULL, String $fileType = 'csv' ) {
    if ( is_null($fileName) ) $fileName = date('Ymd_his').".$fileType";
    else $fileName = $fileName.".".$fileType;
    
    $spreadsheet = new Spreadsheet();
    if ( !empty($opt) ) {
      $spreadsheet->getDefaultStyle()->getFont()->setSize($opt['fontSize']); //  font size 설정
      $activeWorksheet = $spreadsheet->setActiveSheetIndex($opt['activeIndex']); // spreadsheet index로 선택
      // $activeWorksheet = $spreadsheet->setActiveSheetIndexByName('Sheet1') // spreadsheet 시트 이름으로 선택
    } else {
      $spreadsheet->getDefaultStyle()->getFont()->setSize(9); //  font size 설정
      $activeWorksheet = $spreadsheet->setActiveSheetIndex(0); // spreadsheet index로 선택
      // $activeWorksheet = $spreadsheet->setActiveSheetIndexByName('Sheet1') // spreadsheet 시트 이름으로 선택
    }
    $sheet = $spreadsheet->getActiveSheet();
    // print_r($header);
    // echo count($header, 1).' '.count($header, 0).'<br/>';
    // $headColCnt = (count($header, 1) / count($header, 0));
    $headColCnt = (count($header) / count($header)); // column 시작점
    // $headColCnt = 2;
    $this->config->setHeader($header, $headColCnt);
    $this->config->setBody($data);

    if ( !empty($this->config->headers) ) {
      // print_r($this->config->headers);
      $sheet->getRowDimension(1)->setRowHeight(57); // header가 있을 때, header 높이만 높게, 

      foreach ( $this->config->headers AS $i => $head ) {
        // print_r($head);
        $this->exportSetOption($sheet, $head[0]);
        if ( isset($head[1]['opts']['bold']) ) {
          $sheet->getStyle($head[0])->getFont()->setBold($head[1]['opts']['bold']);
        }
        if ( isset($head[1]['opts']['fill']) ) {
          $sheet->getStyle($head[0])->getFill()->setFillType(Style\Fill::FILL_SOLID)->getStartColor()->setARGB($head[1]['opts']['fill']['color']);
        }

        if ( isset($head[1]['opts']['width']) ) {
          // echo $this->config->coordinate[$i]."<br/>";
          $sheet->getColumnDimension($this->config->coordinate[$i])->setWidth($head[1]['opts']['width']);
        }

        // if ( isset($head[1]['opts']['removeColumn']) ) {
        //   $sheet->removeColumnByIndex($head[1]['opts']['removeColumn']);
        // }

        // // $sheet->getStyle($head[0])->getBorders()->getBottom()->setBorderStyle(Style\Border::BORDER_DOUBLE);
        // // $sheet->getStyle($head[0])->getBorders()->getRight()->setBorderStyle(Style\Border::BORDER_THIN);        
        // $sheet->setCellValue($head[0], $head[1]);
        $sheet->setCellValue($head[0], $head[1]['header']);
      }
    } else return;

    if ( !empty($this->config->bodies) ) {
      foreach ( $this->config->bodies AS $body) {
        $sheet->setCellValueExplicit($body[0], $body[1], DataType::TYPE_STRING);  // 타입 지정해서 저장하게
        // $sheet->getStyle($body[0])->getBorders()->getBottom()->setBorderStyle(Style\Border::BORDER_THIN);
        // $sheet->getStyle($body[0])->getBorders()->getRight()->setBorderStyle(Style\Border::BORDER_THIN);
      }
      // if ( !empty($opts) ) {
      //   foreach($opts AS $opt) {
      //     $sheet->getColumnDimension($opt[0])->setWidth($opt[1]);
      //   }
      // }
    }
    
    $writer;
    $contentType = "application/$fileType;charset=UTF-8;";
    switch($fileType) {
      case 'xlsx' :
        $writer = new Writer\Xlsx($spreadsheet);
        $contentType = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;";
        break;
      case 'xls' :
        $writer = new Writer\Xls($spreadsheet);
        break;
      // case 'pdf' :
      //   $writer = new Writer\Pdf($spreadsheet);
      //   break;
      default :
        echo "\xEF\xBB\xBF"; // 한글때문에 꼭 쓰기
        $writer = new Writer\Csv($spreadsheet);
        break;
    }

    $writer->save('php://output');

    header("Content-Description: File Transfer"); 
    header("Content-Disposition: attachment; filename=$fileName"); 
    header("Content-Type: $contentType");
    header('Expires: 0');
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: private, no-transform, no-store, must-revalidate');
  }

  public function attachData($FILE = NULL) {
    if ( !empty($FILE) && ($FILE->isValid() && !$FILE->hasMoved() ) ) {
      $tempFile = $_FILES['file']['tmp_name']; // server 저장된 임시파일
      $fileName = $_FILES['file']['name'];
      $fileType = pathinfo($fileName, PATHINFO_EXTENSION);

      switch($fileType) {
        case 'xlsx': 
          $reader = new Reader\Xlsx();
          break;
        case 'xls' :
          $reader = new Reader\Xls();
          break;
        case 'xml' :
          $reader = new Reader\Xml();
          break;
        case 'csv' :
          $reader = new Reader\Csv();
          break;
        default :
          $reader = NULL;
          return;
       }

      if ( !empty($reader) ) {
        $spreadsheet = $reader->load($tempFile);
        $spreadData = $spreadsheet->getActiveSheet()->toArray();

        $this->numberOfFields = count($spreadData);
        $this->numberOfRecords = (count($spreadData, 1) / count($spreadData)) - 1;
        // $this->config->headers = $spreadData[0];
        return $spreadData;
      } else return;
    } else return;
  }

  public function specificFiltering($data = [], $key= NULL, $keyword = NULL) {
    if ( empty($key) || empty($keyword) || empty($data) ) return;
    $FILTEREDVALUES = [];

    foreach($data AS $_KEY => $_VALUE) {
      if ( strtoupper($_VALUE[$key]) == strtoupper($keyword) ) {
        array_push($FILTEREDVALUES, $_VALUE);
      }
    }
    return $FILTEREDVALUES;
  }

  public function imgConvert($img, $imgUrl = null ) {
    // https://teserre.tistory.com/19
    if ( empty($img) ) return;
    else {
      if ( strpos($img, '.png') ) {
        $imgType = 'png';
        $gdImg = imagecreatefrompng($img);
      } else if ( strpos($img, '.png') ) {
        $imgType = 'gif';
        $gdImg = imagecreatefromgif($img);
      } else {
        $imgType = 'jpeg';
        $gdImg = imagecreatefromjpeg($img);
      }
    }
  }
}