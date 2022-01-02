<?php
namespace App\Actions;
include_once (__DIR__."\..\Database.php");
include_once (__DIR__."\..\Helpers.php");
use App\Database;

class ProductActions{
    public $db;
    public $tablename;
    public function __construct(){
        $db=new Database;
        $this->db = $db;
        $this->tablename="users";
    }

    //insert product if it has images
    public function saveProduct(){
        //if request has images,
        //1. here the text inputs are provided by $_POST not input()->all()
        $productId=$this->db->insert("products",$_POST);
        //2. then the file(s) are uploaded and recorded in the db
        if(!empty($_FILES)){
            $uploadResults=$this->db->insertFile("productImages",$_FILES,$productId);
        }
        
        see($uploadResults);
        see($_FILES);
        seedie($productId);
        return response(1,["id"=>$productId],"Data Saved Successfully");
    }

    //get All Products
    public function getProducts(){
        $result=$this->db->selectAll("products");
        return response(1,$result,getResultInfo($result));
    }

    //get one Product
    public function getProduct($id){
        $result=$this->db->selectWhere('products',["id"=>$id]);
        return response(1,$result,getResultInfo($result));
    }

    //edit product
    public function updateProduct($id){
        $data=getPutParams();
        $result=$this->db->update('products',$data,["id"=>$id]);
        return is_array($result)? response(1,$result,"Updated Successfully") : false;
    }

    //delete product
    public function deleteProduct($id){
        $result=$this->db->delete('products',['id'=>$id]);
        return $result? response(1,[],"Deleted Successfully") : false;
    }

    //join test
    public function joinTest(){
        $result=$this->db->runQuery("SELECT * FROM PRODUCTS, CATEGORIES WHERE PRODUCTS.categoryId=CATEGORIES.ID");
        return response(1,$result);
    }
}