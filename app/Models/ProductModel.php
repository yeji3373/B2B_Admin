<?php
namespace App\Models;
use CodeIgniter\Model;

class ProductModel extends Model {
  protected $table = 'product';
  protected $primaryKey = 'id';

  protected $returnType = 'array';
  protected $useSoftDeletes = true;

  protected $allowedFields = [
    'brand_id', 'category_ids', 'barcode', 'productCode',
    'hs_code', 'sample',  'name', 'name_en',
    'img_url', 'type', 'type_en', 'box', 'in_the_box', 
    'contents_of_box', 'contents_type_of_box',
    'spec', 'spec2', 'container', 'spec_detail', 'spec_pcs',
    'sales_channel', 'unit_weight', 'shipping_weight', 
    'package', 'package_detail', 'etc',
    'edition', 'edition_en',
    'renewal', 'renewal_date',
    'discontinued', 'display', 'group_id'
  ];

  protected $useTimestamps = true;
  protected $createdField = 'created_at';
  protected $updatedField = 'updated_at';
  protected $deletedField = 'deleted_at';
  protected $dateFormat = "datetime";

  // protected $allowCallbacks = true;
  // protected $beforeInsert = ['before'];
  // protected $afterInsert = ['after'];

  // protected $beforeUpdate = ['before'];
  // protected $afterUpdate = ['after'];

  // // function __construct() {
  // //   $this->transStrict(false);
  // //   $this->transBegin();
  // // }

  // function test($a) {
  //   $id = $a['id'];
  //   // unset($a['id']);
  //   // $a = array_values($a);

  //   // return "$id ".implode(',', $a);
  //   // return $a;
  //   // $this->query("INSERT INTO {$this->table}({explode(',', array_keys($a))}) VALUES ({array_values($a)})");
  //   $this->query("UPDATE {$this->table} SET hs_code = NULL WHERE id = 1");
  //   // $this->save($a);
  //   // return $this->transStatus();
  // }

  function productDefault() {
    $this->select("product.id")
        ->select("brand.brand_id, UPPER(brand.brand_name) AS brand_name")
        ->select("product.barcode, product.productCode, product.img_url")
        ->select("product.name")
        ->select("product.name_en")
        ->select('product.box')
        ->select('product.contents_type_of_box')
        ->select('product.in_the_box, product.contents_of_box')
        ->select('product.package_detail')
        ->select("product.spec, product.spec2, product.container, product.spec_detail, product.spec_pcs")
        ->select("product.shipping_weight, product.sample")
        ->select("product.type, product.type_en, product.package, product.package_detail")
        ->select("product.renewal, product.etc")
        ->select("product.discontinued, product.display")
        ->join("brand", "brand.brand_id = product.brand_id")
        ->join("brand_opts", "brand_opts.brand_id = brand.brand_id", 'left outer')
        ->orderBy('brand.brand_id ASC, brand.own_brand DESC, product.id ASC')
        ->where(['product.discontinued' => 0, 'product.display' => 1]);
    return $this;
  }

  function productPriceJoin() {
    $this->select("product_price.retail_price")
        ->select('product_price.supply_price')
        ->select("IFNULL ( product_price.supply_rate_applied, 0 ) AS supply_rate_applied")
        ->select("IFNULL ( product_price.supply_rate, '0.00' ) AS supply_rate")
        ->select('product_price.not_calculating_margin')
        ->select(' IF (product_price.not_calculating_margin = 1, supply_price.price, "") AS price')
        ->select("product_price.taxation")
        ->join('product_price', "product_price.product_idx = product.id", 'left outer')
        ->join('( SELECT product_idx, GROUP_CONCAT(price SEPARATOR "/") AS price
                  FROM supply_price
                  WHERE available = 1
                  GROUP BY product_idx ) AS supply_price'
                , 'supply_price.product_idx = product_price.product_idx'
                , 'left outer')
        ->where('product_price.available', 1);
    return $this;
  }

  function productMoqJoin() {
    $this->select('product_spq.moq, product_spq.spq_inBox, product_spq.spq_outBox, product_spq.spq_criteria,
        product_spq.calc_code, product_spq.calc_unit')
        ->join('product_spq', 'product_spq.product_idx = product.id AND product_spq.available = 1', 'left outer');
    return $this;
  }

  function productMinimalizeJoin() {
    return $this->select('product.id')
                ->select('brand.brand_id, UPPER(brand.brand_name) AS brand_name')
                ->select('product.barcode, product.name, product.name_en')
                ->join('brand', 'brand.brand_id = product.brand_id'); 
  }

  // function before() {
  //   $this->transStrict(false);
  //   $this->transBegin();
  //   // $this->transStart();
  // }

  // function after() {
  //   if ( $this->transStatus() ) {
  //     $this->transCommit();
  //   } else $this->transRollback();
  //   // $this->transRollback();
  // }
}