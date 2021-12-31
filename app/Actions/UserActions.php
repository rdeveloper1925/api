<?php
namespace App\Actions;
include_once (__DIR__."\..\Database.php");
include_once (__DIR__."\..\Helpers.php");
use App\Database;
use Firebase\JWT\JWT;

class UserActions{
    public $db;
    public $tablename;
    public function __construct(){
        $db=new Database;
        $this->db = $db;
        $this->tablename="users";
    }

    //get user token:: Post
    public function getToken(){
        //authenticate the user
        $data=input()->all();
        if(!isset($data["username"]) || !isset($data["password"])){ 
            return response(0,[],"Username and/or password missing");
        }
        $userData=$this->db->selectWhere("users",["username"=>$data["username"],"password"=>mask($data["password"])]);
        if(empty($userData)){
            return response(0,[],"Incorrect Username and/or password");
        }
        $userData=$userData[0];
        //generate the token
        $payload = array( //info will be used by middleware to authorize different accesses
            "expiry" => time()+(10*60),
            "generated" => time(),
            "userId"=>$userData["username"]."-".$userData["id"]
        );
        $token=JWT::encode($payload,APP_KEY,"HS512");
        //update token in the user table
        $this->db->update("users",["token"=>$token],["id"=>$userData["id"]]);
        //supply token to user
        return response(1,["token"=>$token],"Access expires in ".(($payload["expiry"]-$payload["generated"])/60)." minutes");
    }

    public function saveUser(){
        $data=input()->all();
        $result=$this->db->insert("users",$data);
        return response(1,["id"=>$result],"Data Saved Successfully");
    }

    public function getUser($id){
        $result=$this->db->selectWhere("users",["id"=>$id]);
        if(!empty($result)){
            return response(1,$result,"User Returned");
        }else{
            return response(0,$result,"No such user was found");
        }
    }

    public function updateUser($id){
        //put params should be form-url-encoded
        $data=getPutParams();
        $result=$this->db->update("users",$data,["id"=>$id]);
        return is_array($result)? response(1,$result,"Updated Successfully") : false;
    }

    public function deleteUser($id){
        $result=$this->db->delete("users",["id"=>$id]);
        return $result? response(1,[],"Deleted Successfully") : false;
    }

    //web user login
    public function login(){
        $data=input()->all();
        if(!isset($data["username"]) || !isset($data["password"])){ 
            return response(0,[],"Username and/or password missing");
        }
        $result=$this->db->selectWhere('users',["username"=>$data['username'],"password"=>mask($data['password'])]);
        if(!empty($result)){
            return response(1,[$result]);
        }else{
            return response(0,[],"Username/password incorrect");
        }
    }

}
