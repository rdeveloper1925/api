<?php
//CONTAINS GENERIC ACTIONS THAT DONT FIT ANYWHERE
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

    //public file handling
    public static function getFile($tablename,$id){
        $db=new Database;
        //get the fileName from db
        $result=$db->selectWhere($tablename,["id"=>$id]);
        if(empty($result)){
            return "File/Image was not found";
        }
        $fileName=$result[0]["name"];
        $path=__DIR__."\..\Storage\\$fileName";
        //deciding the header
        $allowedExtensions=array("jpg","jpeg","png");
        preg_match('/\w+$/', $fileName, $extension);
        if(in_array($extension[0],$allowedExtensions)){
            if($extension[0]=="png"){
                header("Content-type: image/png");
            }else{
                header("Content-type: image/jpeg");
            }
        }else{
            header('Content-Disposition: attachment; filename="'.$fileName.'"');
        }
        echo readfile($path);
        return 1;
        see($result);
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