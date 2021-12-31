<?php
namespace App\Actions;
include_once (__DIR__."\..\Database.php");
include_once (__DIR__."\..\Helpers.php");
use App\Database;

class CategoryActions{
    public $db;
    public $tablename;
    public function __construct(){
        $db=new Database;
        $this->db = $db;
        $this->tablename="categories";
    }

    
    //insert image
    public function saveCategory(){
        $data=input()->all();
        $result=$this->db->insert("categories",$data);
        return response(1,["id"=>$result],"Data Saved Successfully");
    }

    //get All categories
    public function getcategories(){
        $result=$this->db->selectAll("categories");
        return response(1,[$result],getResultInfo($result));
    }

    //get one Category
    public function getCategory($id){
        $result=$this->db->selectWhere('categories',["id"=>$id]);
        return response(1,[$result],getResultInfo($result));
    }

    //edit Category
    public function updateCategory($id){
        $data=getPutParams();
        $result=$this->db->update('categories',$data,["id"=>$id]);
        return is_array($result)? response(1,$result,"Updated Successfully") : false;
    }

    //delete Category
    public function deleteCategory($id){
        $result=$this->db->delete('categories',['id'=>$id]);
        return $result? response(1,[],"Deleted Successfully") : false;
    }
}