<?php

namespace DataFile\Controllers;

use CodeIgniter\Controller;
use Config\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer;
use PhpOffice\PhpSpreadsheet\Reader;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class DataFileController extends Controller {
  protected $config;

  public function __construct() {
    $this->config = config('DataFile');
  }

  public function exportData(Array $header, Array $data = [], String $fileName = NULL, String $fileType = 'csv' ) {
    if ( is_null($fileName) ) $fileName = date('Ymd_his').".$fileType";
    else $fileName = $fileName.".".$fileType;

    // if ( $fileType == 'xlsx' ) {
    //   $this->exportDataXlsx($header, $data);
    // } elseif ( $fileType == 'csv' ) {    
    //   echo "\xEF\xBB\xBF"; // 한글때문에 꼭 쓰기

    //   $file = fopen('php://output', 'w'); // output
      
    //   fputcsv($file, $header);
    
    //   if ( !empty($data) ) :
    //     foreach($data as $key => $d) :
    //       fputcsv($file, $d);
    //     endforeach;
    //   endif;
    
    //   fclose($file);
    //   exit;
    // } else return;

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $this->config->setHeader($header);
    $this->config->setBody($data);

    if ( !empty($this->config->headers) ) {
      foreach ( $this->config->headers AS $head ) {
        $sheet->setCellValue($head[0], $head[1]);
      }
    } else return;

    if ( !empty($this->config->bodies) ) {
      // $sheet->fromArray($data, NULL, 'A2'); // 배열 값을 그대로 옮길때는 이렇게
      foreach ( $this->config->bodies AS $body) {
        // $sheet->setCellValue($body[0], $body[1]); // 타입 지정 안하고 그대로 사용하게 할때
        $sheet->setCellValueExplicit($body[0], $body[1], DataType::TYPE_STRING);  // 타입 지정해서 저장하게
      }
    }
    
    $writer;
    switch($fileType) {
      case 'xlsx' :
        $writer = new Writer\Xlsx($spreadsheet);
        break;
      case 'xls' :
        $writer = new Writer\Xls($spreadsheet);
        break;
      case 'pdf' :
        $writer = new Writer\Pdf($spreadsheet);
        break;
      default :
        echo "\xEF\xBB\xBF"; // 한글때문에 꼭 쓰기
        $writer = new Writer\Csv($spreadsheet);
        break;
    }
    // $writer = new Writer\Xlsx($spreadsheet);
    $writer->save('php://output');

    header("Content-Description: File Transfer"); 
    header("Content-Disposition: attachment; filename=$fileName"); 
    header("Content-Type: application/$fileType;charset=UTF-8;");
    header('Expires: 0');
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: private, no-transform, no-store, must-revalidate');

  }
  // public function exportDataXlsx(Array $header, Array $data = [] ) {
  //   $spreadsheet = new Spreadsheet();
  //   $sheet = $spreadsheet->getActiveSheet();
  //   $this->config->setHeader($header);
  //   $this->config->setBody($data);

  //   if ( !empty($this->config->headers) ) {
  //     foreach ( $this->config->headers AS $head ) {
  //       $sheet->setCellValue($head[0], $head[1]);
  //     }
  //   } else return;

  //   if ( !empty($this->config->bodies) ) {
  //     // $sheet->fromArray($data, NULL, 'A2'); // 배열 값을 그대로 옮길때는 이렇게
  //     foreach ( $this->config->bodies AS $body) {
  //       // $sheet->setCellValue($body[0], $body[1]); // 타입 지정 안하고 그대로 사용하게 할때
  //       $sheet->setCellValueExplicit($body[0], $body[1], DataType::TYPE_STRING);  // 타입 지정해서 저장하게
  //     }
  //   }
    
  //   $writer = new Writer\Xlsx($spreadsheet);
  //   $writer->save('php://output');
  // }

  public function attachData($FILE = NULL) {
    if ( !empty($FILE) && ($FILE->isValid() && !$FILE->hasMoved() ) ) {
      // $tempName = $FILE->getRandomName();
      // $FILE->move('../public/csvfile', $tempName);
      // $file = fopen('../public/csvfile'.$tempName, 'r');
      $tempFile = $_FILES['file']['tmp_name']; // server 저장된 임시파일
      $fileName = $_FILES['file']['name'];
      $fileType = pathinfo($fileName, PATHINFO_EXTENSION);

      print_r($_FILES);
      echo '<br/><br/>';
      echo $fileType;

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
          // $reader = NULL;
          return;
      }

      $spreadsheet = $reader->load($tempFile);
      $spreadData = $spreadsheet->getActiveSheet()->toArray();


      print_r($spreadData);
    } else return;
  }
}