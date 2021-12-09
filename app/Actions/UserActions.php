<?php
namespace App\Actions;
include_once (__DIR__."\..\Database.php");
include_once (__DIR__."\..\Helpers.php");
use App\Database;

class UserActions{
    public $db;
    public function __construct(){
        $db=new Database;
        $this->db = $db;
    }

    //get user token
    public function getToken(){
        var_dump(input()->all());
    }

    public function saveUser(){
        $data=input()->all();
        $result=$this->db->insert("users",$data);
        return response(1,[$result],"Data Saved Successfully");
    }

    public function getUser($id){
        $result=$this->db->selectWhere("users",["id"=>$id]);
        return response(1,$result,"User Returned");
    }

    public function updateUser($id){
        $data=getPutParams();
        $result=$this->db->update("users",$data,["id"=>$id]);
        return is_array($result)? response(1,$result,"Updated Successfully") : false;
    }
}
