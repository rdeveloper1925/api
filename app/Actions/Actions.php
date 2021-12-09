<?php
namespace App\Actions;
include_once (__DIR__."\..\Database.php");
include_once (__DIR__."\..\Helpers.php");
use App\Database;

class Actions{
    public $db;
    public function __construct(){
        $db=new Database;
        $this->db = $db;
    }

    //STARTING TEST FUNCTIONS
    public static function start(){
        return response(1,[],"Welcome to my api!!!! enjoy");
    }

    public static function getAllTasks(){
        $db = new Database;
        $result= $db->selectAll("tasks");
        return response(1,$result);
    }

    public static function saveTask(){
        $db=new Database;
        $result=$db->insert("tasks",input()->all());
        return response(1,[$result],"Data Saved Successfully");
    }

    public static function updateTask($id){
        $data=getPutParams();
        $db=new Database;
        $result=$db->update("tasks",$data,["id"=>$id]);
        return is_array($result)? response(1,$result,"Updated Successfully") : false;
    }

    public function deleteTask($id){
        $result=$this->db->delete("tasks",['id'=>$id]);
        return $result;
    }
}