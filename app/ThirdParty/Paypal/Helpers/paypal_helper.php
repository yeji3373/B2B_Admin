<?php
use Config\Servies;

if ( !function_exists('invoice_detail') || !empty($userData) ) {
  function invoice_detail($userData) {
    $config = config('Paypal');
    $userData['currency_code'] = 'USD';
    $userData['term_type'] = 'NO_DUE_DATE';
    $userData['invoice_date'] = isset($userData['invoice_date']) ? $userData['invoice_date'] : date('Y-m-d');
    $default = [
      'detail'   => [
        'invoice_number'  => $userData['invoice_number'],
        // 'invoice_date'    => date('Y-m-d'),
        'invoice_date'    => $userData['invoice_date'],
        'payment_term'    => [ 'term_type' => $userData['term_type'] ],
        'currency_code'   => $userData['currency_code']
      ],
      'invoicer'  => [
        'name'    => [
          'given_name'  => 'BeautynetKorea Co.,'
        ],
        'address' => [
          'address_line_1'    => '577beon-gill, Baekbeom-ro',
          'address_line_2'    => '#621 Gyeongin Center 20',
          'admin_area_2'      => 'Bupyeong-gu',
          'admin_area_1'      => 'Incheon',
          'postal_code'       => '21449',
          'country_code'      => 'KR'
        ],
        'email_address' => $config->invoicerEmail,
        'phones'    => [
          [
            'country_code'      => '082',
            'national_number'   => '7048005454',
            'extension_number'  => '202',
            'phone_type'        => 'MOBILE'
          ]
        ],
        'logo_url'  => 'https://pics.paypal.com/00/s/MjUwWDEwMDBYUE5H/p/N2VhODRjZDUtOTU3Yy00YWE1LTk0MmQtMWRkNjgxOTA1NDAy/image_109.png'
      ],
      // 'primary_recipients'  =>  $userData['primary_recipients'],
      'primary_recipients'  => [
        [
          'billing_info'  => $userData['billing_info']
        ], 
        // [
        //   'shipping_info' => $userData['shipping_info']
        // ]
      ],
      'items' => [],
      // 'items' => [
      //   // [
      //   //   'name'  => 'cosmetic',
      //   //   'quantity'  => '1',
      //   //   'unit_amount' => [
      //   //     'currency_code' => $userData['currency_code'],
      //   //     'value'         => $userData['unit_amount']
      //   //   ],
      //   //   'unit_of_measure' => 'QUANTITY'
      //   // ]
      // ],
      'configuration' => [
        'partical_payment'    => [
          'allow_partial_payment'  => $userData['partial_payment'],
        ],
        'allow_tip' => false,
        'tax_calculated_after_discount' => true,
        'tax_inclusive' => false
      ]
    ];

    if ( !empty($userData['due_date']) ) { 
      $default['payment_term']['due_date'] = $userData['due_date'];
    }

    if ( !empty($userData['items']) ) {
      print_r($userData['items']);
      foreach($userData['items'] AS $item ) {
        $default['items'][] = [
          'name'        => $item['name'],
          'quantity'    => $item['quantity'],
          'unit_amount' => [
            'currency_code' => $userData['currency_code'],
            'value'         => $item['unit_amount']
          ],
          'unit_of_measure' => 'QUANTITY'
        ];
      }
    } else {
      return;
      // $default['items'] = [
      //   [
      //     'name'  => 'cosmetic',
      //     'quantity'  => '1',
      //     'unit_amount' => [
      //       'currency_code' => $userData['currency_code'],
      //       'value'         => 0
      //     ],
      //     'unit_of_measure' => 'QUANTITY'
      //   ]
      // ];
    }

    if ( $userData['partial_payment'] === true ) {
      $partical_payment = $default['configuration']['partical_payment']['minimum_amount_due'] =
                                      ['currency_code'    => $userData['currency_code'],
                                        'value'           => $userData['minimum_amount']];

      $default = array_merge($default, $partical_payment);
    }

    if ( !empty($userData['shippingFee']) ) {
      $default['amount'] = [
        'breakdown' => [
          'shipping' => [
            'amount' => [
              'currency_code' => $userData['currency_code'],
              'value' => $userData['shippingFee']
            ]
          ]
        ]
      ];
    }

    return json_encode($default);
  }
}

if ( !function_exists('update_invoice_body') || !empty($data) || !empty($amount) ) {
  function update_invoice_body($data, $amount) {
    $body = [
      "id"  => $data['data']['id'],
      "detail"  => [
        "invoice_number"  => $data['data']['detail']['invoice_number'],
        "invoice_date"    => $data['data']['detail']['invoice_date'],
        "currency_code"   => $data['data']['detail']['currency_code'],
        "payment_term"    => $data['data']['detail']['payment_term']
      ],
      "invoicer"  => $data['data']['invoicer'],
      "primary_recipients" => $data['data']['primary_recipients'],
      "items" => [
        [
          "name" => "cosmetic",
          "quantity" => "1",
          "unit_amount" => [
            "currency_code" => $amount['currency_code'],
            "value" => $amount['unit_amount']
          ],
          "unit_of_measure" => "QUANTITY"
        ]
      ],
      'configuration' => [
        'partical_payment'    => [
          'allow_partial_payment'  => false,
        ],
        'allow_tip' => false,
        'tax_calculated_after_discount' => true,
        'tax_inclusive' => false
      ],
      "amount" => [
        "currency_code" => $amount['currency_code'],
        "value" => $amount['amount'],
        "breakdown" => [
          "item_total" => [
            "currency_code" => $amount['currency_code'],
            "value" => $amount['unit_amount']
          ],
          "discount" => $data['data']['amount']['breakdown']['discount'],
          "tax_total" => $data['data']['amount']['breakdown']['tax_total'],
        ]
      ]
    ];

    if ( !empty($amount['shippingFee']) ) {
      $body['amount']['breakdown'] = [
        'shipping' => [
          'amount' => [
            'currency_code' => $amount['currency_code'],
            'value' => $amount['shippingFee']
          ]
        ]
      ];
    }

    return json_encode($body);
  }
}

if ( !function_exists('refund_invoice_body') || !empty($data)) {
  function refund_invoice_body($data) {
    $body = [
      "method" => $data['method'],
      "refund_date" => $data['refund_date'],
      "amount" => [
        "currency_code" => $data['currency_code'],
        "value" => $data['value']
      ]
    ];
    return json_encode($body);
  }
}