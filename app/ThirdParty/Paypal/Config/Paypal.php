<?php
namespace Paypal\Config;

use CodeIgniter\Config\BaseConfig;
use Config\Services;

class Paypal extends BaseConfig {
  public $sandbox = TRUE; // TRUE:test계정 사용하기
  // public $sandbox = FALSE; // FALSE:LIVE 계정사용하기

  protected $sandboxURL = 'https://sandbox.paypal.com';
  protected $liveURL = 'https://www.paypal.com';
  
  /* api url */
  protected $sandBoxBaseUrl = 'https://api-m.sandbox.paypal.com';
  protected $liveBaseUrl = 'https://api-m.paypal.com';

  /* v2plus1v@hotmail.com 계정일 때 */
  protected $sandBoxEmail = 'sb-amddg11313806@business.example.com';
  protected $sandBoxClientId = 'AVJV4jd9LkeLypYumyAPrbl3DAYOOdFQws0tVBjHB9DKSLoGETmDFa6B0c4BIGok8_Q211dnDMz-yctu';
  protected $sandBoxClientSecret = 'EBujP4D-WeYXLQLVIPARWwaNT73MWIFb0QUjwTVcjtqOTFOfBCsnWGrWb3Oa122jzdWGmO3eq_tgHw4x';

  protected $liveEmail = 'v2plus1v@hotmail.com';
  protected $liveClientId = 'AfWhStgsSYVOBBJtWRcJ2CNjSn7uVwGW-bditn4ZL4KXxqJJaemgdzpjK1ckk-DM7eEh5cE1jgEX5GT1';
  protected $liveClientSecret = 'ELMz-SDSkIAXEX8q2t1qmfV8fLxsfgE6aXEtsJ6e_it2qEzPw8SQ_xYv-TsGlZv9kSTPknrGZAu7sPKL';
  /* v2plus1v@hotmail.com 계정일 때 */

  /* jmh@beautynetkorea.com */
  // protected $sandBoxEmail = 'sb-amddg11313806@business.example.com';
  // protected $sandBoxClientId = 'AS57yt5JUkBJbyBOIM959Q_DqRmS0tpXieYWoNNJaCBUBZ9McG8b-XIN4BYHujAa6TeUY9VyQbxhnI1r';
  // protected $sandBoxClientSecret = 'EBcvXFRfInaYcjjZgAs-whqG12cPA5mQtJzHmdlKDVL6yEa8NX7yqiY3lv9MfESKq3I2kPzjKVAySy9M';

  // protected $liveEmail = 'jmh@beautynetkorea.com';
  // protected $liveClientId = 'AVJV4jd9LkeLypYumyAPrbl3DAYOOdFQws0tVBjHB9DKSLoGETmDFa6B0c4BIGok8_Q211dnDMz-yctu';
  // protected $liveClientSecret = 'EBujP4D-WeYXLQLVIPARWwaNT73MWIFb0QUjwTVcjtqOTFOfBCsnWGrWb3Oa122jzdWGmO3eq_tgHw4x';
  /* jmh@beautynetkorea.com */

  protected $needNewToken = true;
  public $accessToken;
  public $accessTokenExpiry;

  protected $lastError;

  protected $buttonPath;
  protected $submitBtn;

  public $clientID;
  public $clientScret;
  public $baseUrl;
  public $invoicerEmail;

  public $paypalUrl = [
    'token'         => '/v1/oauth2/token',
    'invoice'       => '/v2/invoicing/invoices',
    'searchInvoice' => '/v2/invoicing/search-invoices',
  ];
  public $invoiceViewer;

  public function __construct() {
    $this->curl = service('curlrequest');

    if ( $this->sandbox ) {
      $this->clientID = $this->sandBoxClientId;
      $this->clientScret = $this->sandBoxClientSecret;
      $this->baseUrl = $this->sandBoxBaseUrl;
      $this->invoicerEmail = $this->sandBoxEmail;
      $this->invoiceViewer = $this->sandboxURL.'/invoice/p/';
    } else {
      $this->clientID = $this->liveClientId;
      $this->clientScret = $this->liveClientSecret;
      $this->baseUrl = $this->liveBaseUrl;
      $this->invoicerEmail = $this->liveEmail;
      $this->invoiceViewer = $this->liveURL.'/invoice/p/';
    }

    if ( !empty($this->accessToken) || !empty($this->accessTokenExpiry) ) {
      if ( $this->accessTokenExpiry >= time() ) {
        $this->needNewToken = false;
      } else $this->needNewToken = true;
    } 

    if ( $this->needNewToken ) $this->getOauth();
  }

  protected function getOauth() {
    $oauth = $this->curl->post(
                $this->baseUrl.$this->paypalUrl['token'],
                [
                  'auth'        => [$this->clientID, $this->clientScret],
                  'debug'       => true,
                  'headers'     => ['Content-Type' => 'application/x-www-form-urlencoded'],
                  'form_params' => ['grant_type' => 'client_credentials']
                ]
              );

    if ( $oauth->getStatusCode() == 200 ) : 
      $this->needNewToken = false;

      $body = json_decode($oauth->getBody(), true);
      $this->accessToken = $body['access_token'];
      $this->accessTokenExpiry = time() + $body['expires_in'];
    endif;
  }

  public $invoiceStatus = [ 
    [ 'value' => 'DRAFT'
      , 'comment' => 'The invoice is in draft state. It is not yet sent to the payer.' ],
    ['value' => 'SENT'
      , 'comment' => 'The invoice has been sent to the payer. The payment is awaited from the payer.'],
    ['value' => 'SCHEDULED'
      , 'comment' => 'The invoice is scheduled on a future date. It is not yet sent to the payer.' ],
    ['value' => 'PAID' 
      , 'comment' => 'The payer has paid for the invoice.'],
    ['value' => 'MARKED_AS_PAID' 
      , 'comment' => 'The invoice is marked as paid by the invoicer.'],
    ['value' => 'CANCELLED' 
      , 'comment' => 'The invoice has been cancelled by the invoicer.'],
    ['value' => 'REFUNDED'
      , 'comment' => 'The invoice has been refunded by the invoicer.' ],
    ['value' => 'PARTIALLY_PAID' 
      , 'comment' => 'The payer has partially paid for the invoice.' ],
    ['value' => 'PARTIALLY_REFUNDED' 
      , 'comment' => 'The invoice has been partially refunded by the invoicer.'],
    ['value' => 'MARKED_AS_REFUNDED'
      , 'comment' => 'The invoice is marked as refunded by the invoicer.' ],
    ['value' => 'UNPAID' 
      , 'comment' => 'The invoicer is yet to receive the payment from the payer for the invoice.' ],
    ['value' => 'PAYMENT_PENDIN' 
      , 'comment' => 'The invoicer is yet to receive the payment for the invoice. It is under pending']
  ];
}