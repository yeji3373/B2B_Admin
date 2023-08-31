<?php
namespace App\Models;
use CodeIgniter\Model;

class ProductModel extends Model {
  protected $table = 'product';
  protected $primaryKey = 'id';
  protected $useSoftDeletes = false;

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
    'discontinued', 'display',
  ];

  protected $useTimestamps = true;
  protected $createdField = 'created_at';
  protected $updatedField = 'updated_at';
  protected $dateFormat = "datetime";

  // function productJoin() {
  //   return $this->join("brand", "brand.brand_id = product.brand_id")
  //               ->join("brand_opts", "brand_opts.brand_id = brand.brand_id", 'left outer')
  //               ->join('product_price', "product_price.product_idx = product.id", 'left outer');
  //               // ->join('supply_price', 'supply_price.product_price_idx = product_price.idx', 'left outer');
  // }
}