<?php
namespace App\Models;
use CodeIgniter\Model;

class ProductGroupModel extends Model {
    protected $table = 'product_group';
    protected $primaryKey = 'id';
    protected $useSoftDeletes = false;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $dateFormat = "datetime";

    protected $allowedFields = ['brand_id', 'category_ids', 'img_url',
    'group_name', 'available'];
}