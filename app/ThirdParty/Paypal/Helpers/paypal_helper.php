<?php
use Config\Servies;

if ( !function_exists('invoice_detail') || !empty($userData) ) {
  function invoice_detail($userData, $currencyUnit = 'USD') {
    $config = config('Paypal');
    
    if ( !empty($userData['currency_code']) ) {
      $currencyUnit = $userData['currency_code'];
    }

    $default = [
      'detail'   => [
        // 'invoice_number'  => empty($userData['invoce_number']) ? $userData['buyerName'].'_'.date('YmdHms', time()) : $userData['invoce_number'],
        'invoice_number'  => empty($userData['invoice_number']) ? $userData['buyerName'].'_'.date('Ymd', time()) : $userData['invoice_number'],
        'invoice_date'    => date('Y-m-d'),
        'payment_term'    => ['term_type' => 'NO_DUE_DATE'],
        'currency_code'   => $currencyUnit
      ],
      'invoicer'  => [
        'name'    => [
          'given_name'  => 'BeautynetKorea Co.,'
        ],
        'address' => [
          'address_line_1'    => '21, Janggogae-ro 231beonan-gil, Seo-gu',
          'address_line_2'    => 'Beautynetkorea Bldg',
          'admin_area_2'      => 'Incheon',
          'admin_area_1'      => 'Korea',
          'postal_code'       => '22827',
          'country_code'      => 'KR'
        ],
        'email_address' => $config->invoicerEmail,
        'phones'    => [
          [
            'country_code'      => '082',
            'national_number'  => '7048005454',
            'extension_number'  => '202',
            'phone_type'        => 'MOBILE'
          ]
        ],
        // "website" => "www.beautynetkorea.com",
        // "tax_id" => "XX-XXXXXXXX",
        'logo_url'  => 'https://pics.paypal.com/00/s/MjUwWDEwMDBYUE5H/p/N2VhODRjZDUtOTU3Yy00YWE1LTk0MmQtMWRkNjgxOTA1NDAy/image_109.png'
      ],
      'primary_recipients'  => [
        [
          'billing_info'  => [
            'name'      => ['given_name' => $userData['buyerName']],
            // 'address'   => [
            //   'address_line_1'  => $userData['streetAddr1'].$userData['streetAddr2'],
            //   // 'admin_area_2'    => 'Anytown',
            //   // 'admin_area_1'    => 'CA',
            //   'postal_code'     => '',
            //   'country_code'    => 'US'
            // ], 
            'email_address'   => $userData['email'],
            'phones'   => [
              [
                'country_code'    => $userData['phone_code'],
                'national_number' => $userData['phone'],
                'phone_type'      => 'MOBILE'
              ]
            ], 
            'additional_info_value' => 'add-info'
          ], 
          'shipping_info' => [
            'name'  => [
              'given_name' => $userData['consignee']
            ],
            'address' => [
              'address_line_1'  => $userData['streetAddr1'],
              'address_line_2'  => $userData['streetAddr2'],
              'postal_code'     => $userData['zipcode'],
              'country_code'    => $userData['country_code']
            ]
          ]
        ]
      ],
      'items' => [
        [
          'name'  => 'cosmetic',
          'quantity'  => '1',
          'unit_amount' => [
            'currency_code' => $currencyUnit,
            // 'value'         => ($userData['order-subtotal-price'] * $userData['depositRate'])
            'value'         => $userData['subtotal']
          ],
          'unit_of_measure' => 'QUANTITY'
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
      // 'amount' => [
      //   'breakdown' => [
      //     'shipping' => [
      //       'amount' => [
      //         'currency_code' => $currencyUnit,
      //         'value' => $userData['shippingFee']
      //       ]
      //     ]
      //   ]
      // ]
    ];

    if ( !empty($userData['shippingFee']) ) {
      $default['amount'] = [
        'breakdown' => [
          'shipping' => [
            'amount' => [
              'currency_code' => $currencyUnit,
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
        "value" => $amount['unit_amount'],
        "breakdown" => [
          "item_total" => [
            "currency_code" => $amount['currency_code'],
            "value" => $amount['unit_amount']
          ],
          // "discount" => $data['data']['amount']['breakdown']['discount'],
          // "tax_total" => $data['data']['amount']['breakdown']['tax_total'],
        ]
      ]
    ];

    if ( !empty($amount['shippingFee']) ) {
      echo "has shippingFee";
      $body['amount']['breakdown'] = [
        'shipping' => [
          'amount' => [
            'currency_code' => $amount['currency_code'],
            'value' => $amount['shippingFee']
          ]
        ]
      ];
      $body['amount']['value'] = floatval(sprintf('%0.2f',($amount['unit_amount'] + $amount['shippingFee'])));
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