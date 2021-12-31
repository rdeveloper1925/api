<?php
namespace App;

use Exception;
use PDO, PDOException;

include_once "Config.php";
include_once "Utils.php";

//Holds prime fuctions relating to the database
class Database {
    public $conn=null;
    public function __construct(){
        try {
            $host=DB_HOST;
            $db=DB;
            $pass=DB_PASS;
            $user=DB_USER;
            $dsn = "mysql:dbname=$db;host=$host";
            $options  = array(
                PDO::ATTR_ERRMODE =>PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            );
            $this->conn = new PDO($dsn, $user, $pass, $options);
            //see($this->conn);
            return $this->conn;

        } catch (PDOException $e) {
            echo response(0,[],"",$e->getMessage());
            die();
        }    
    }

    public function selectAll($tablename){
        try{
            $query="SELECT * FROM $tablename";
            $stmt=$this->conn->prepare($query);
            $stmt->execute();
            $result=$stmt->fetchAll();
            if($tablename=="users"){//wouldnt want sharing passwords
                foreach($result as $key=>$val){
                    unset($result[$key]["password"]);
                }
            }
            return ($result);
        }catch(Exception $e){
            echo response(0,[],"",$e->getMessage());
            die();
        }
    }

    public function selectFromMultiple($tables, $conditions, $glue="and"){
        try{
            $query="SELECT * FROM ".implode(", ",$tables)." WHERE ";
            $query.=$this->implementFillables($conditions);
            $stmt=$this->conn->prepare($query);
            $stmt->execute();
            $result=$stmt->fetchAll();
            see($query);
            see($result);
            return ($result);
        }catch(Exception $e){
            echo response(0,[],"",$e->getMessage());
            die();
        }
    }

    public function getRequiredFields($tablename){
        try{
            $query="desc $tablename ";
            $stmt=$this->conn->prepare($query);
            $stmt->execute();
            $result=$stmt->fetchAll();
            $result=array_map(function($value){
                if($value['Null']==="NO"&&$value['Extra']!="auto_increment"&&$value['Default']==null){
                    return $value['Field'];
                }
            },$result);
            $result=array_filter($result);
            return ($result);
        }catch(Exception $e){
            echo response(0,[],"",$e->getMessage());
            die();
        }
    }

    //checks if a value exists for a given field ($criteria)
    public function checkExists($tablename,$criteria,$value){
        try{
            $query="SELECT * FROM $tablename WHERE $criteria=:value";
            $stmt=$this->conn->prepare($query);
            $stmt->bindParam(':value',$value);
            $stmt->execute();
            return $stmt->rowCount()>0 ? true : false;
        }catch(Exception $e){
            echo response(0,[],"",$e->getMessage());
            die();
        }
    }

    public function checkUniqueness($tablename,$data){
        try{
            error_reporting(0); //will throw a warning if id is not contained in the data
            $cols=$this->getCols($tablename);
            $duplicates=array();
            foreach($cols as $col){
                if($this->isUnique($tablename,$col) && $this->checkExists($tablename,$col,$data[$col])){ //if the column is meant to be unique
                    array_push($duplicates,ucwords($col));
                }
            }
            if(!empty($duplicates)){
                $msg="The following values provided already exist in the db: ".implode(", ",$duplicates);
                throw new Exception($msg,3);
            }else{
                return true;
            }
        }catch(Exception $e){
            echo response(0,[],"",$e->getMessage());
            die();
        }
        
    }

    //function to eliminate unwanted inputs that may come in the request
    public function sortInputs($tablename,$data){
        $tableCols=array_flip($this->getCols($tablename));
        foreach($data as $key=>$value){
            if(!array_key_exists($key, $tableCols)){
                unset($data[$key]); //removing the unwanted.
            }
        }
        return $data;
    }

    public function insert($tablename,$data){
        try{
            $requiredFields=$this->getRequiredFields($tablename);
            //function bulk validates all input data. returns more db friendly input data
            $data=$this->sanitizeInputs($tablename,$data);

            //now we know that all values required are present, valid and username is unique
            $query="INSERT INTO $tablename (";
            $valuesPart="VALUES ("; //iterating the values part simultaneously
            foreach($requiredFields as $k=>$field){
                if($k==array_key_last($requiredFields)){ //closing the values part if this is the last iteration
                    $query.="`$field`) ";
                    $valuesPart .= ":$field)";
                }else{
                    $query.="`$field`, ";
                    $valuesPart .= ":$field, ";
                }
            }
            $query .= $valuesPart;
            //now bind the params to the query
            $stmt=$this->conn->prepare($query);
            $result=$stmt->execute($data);
            unset($data["password"]);
            return $result ? $this->conn->lastInsertId(): null;
            //return $result?response(true,$data,"User Created Successfully!"):response(0,[],"Sorry! An unknown error occured","Sorry! An unknown error occured");
        }catch(Exception $e){
            echo response(0,[],"",$e->getMessage());
            die();
        }
    }

    //Gets all the columns of a table
    public function getCols($tablename){
        try{
            $query="show columns from $tablename";
            $stmt=$this->conn->prepare($query);
            $stmt->execute();
            $result=$stmt->fetchAll(PDO::FETCH_COLUMN,0); //fetching from one column
            return ($result);
            
        }catch(Exception $e){
            echo response(0,[],"",$e->getMessage());
            die();
        }
    }

    //Checks if column only accepts unique data
    public function isUnique($tablename,$column){
        try{
            $query="show index from $tablename where column_name='$column' and non_unique='0' ";
            $stmt=$this->conn->prepare($query);
            $stmt->execute();
            return $stmt->rowCount()>0 ? true : false;
        }catch(Exception $e){
            echo response(0,[],"",$e->getMessage());
            die();
        }

    }

    //function checks for missing inputs, removes unwanted fields and form validation
    public function sanitizeInputs($tablename,$data,$flags=array()){
        try{
            //1- Look for missing fields except for update
            $missingFields=array(); 
            if(!in_array("skipMissing",$flags)){ 
                $requiredFields=$this->getRequiredFields($tablename);
                foreach($requiredFields as $key){
                    if(!array_key_exists($key,$data)){ //checking that the key exists in the data supplied
                        $missingFields[]=ucwords($key);
                    }else{ //if it exists, proceed to check that it aint empty
                        if(!isset($data[$key]) || trim($data[$key])==""){
                            $missingFields[]=ucwords($key);
                        }
                    }
                }
            }
            //stop further processing if we have missing fields
            if(!empty($missingFields)){ //this will be skipped if skipMissing flag is set
                throw new Exception("Oops! Looks like the following vital fields are missing: ".implode(", ",$missingFields));
            }

            //2- Sort inputs, removing any extra that are unwanted.
            $tableCols=array_flip($this->getCols($tablename));
            foreach($data as $key=>$value){
                if(!array_key_exists($key, $tableCols)){
                    unset($data[$key]); //removing the unwanted.
                }
            }
            
            //3- Performing validations on the data provided
            if(!empty($validationResult=validate($data))){
                throw new Exception(implode(", ",$validationResult));
            }

            //4- House keeping for passwords
            if(array_key_exists('password',$data)){
                $unhashed=$data['password'];
                $data['password']=mask($unhashed);
            }

            //5- House keeping for unique values: check for unique fields and if their unique constraint isnt violated.
            $this->checkUniqueness($tablename,$data); 

            return $data; //because we made some changes to the data (sorting)

        }catch(Exception $e){
            echo response(0,[],"",$e->getMessage());
            die();
        }
    }

    public function selectWhere($tablename, $conditions=array(),$glue="and"){ //['username'=>['=rodney'," and"]]
        try{
            if(empty($conditions)){
                $query="SELECT * FROM $tablename ";
            }else{
                $query="SELECT * FROM $tablename WHERE ";
                //handling the conditions
                $query .= $this->implementFillables($conditions,$glue);
            }
            //seedie($query);
            $stmt=$this->conn->prepare($query);
            $stmt->execute($conditions);
            $result=$stmt->fetchAll();
            if($tablename=="users"){//wouldnt want sharing passwords
                foreach($result as $key=>$val){
                    unset($result[$key]["password"]);
                }
            }
            return $result;
        }catch(Exception $e){
            echo response(0,[],"",$e->getMessage());
            die();
        }
    }

    public function update($tablename,$data,$condition=array(),$glue="and"){
        try{
            //dont update if condition is empty or data is empty
            if(empty($condition) || empty($data)){
                throw new Exception("Looks like we have a missing condition for this update. Aborting Now",2);
            }
            //Validating received data
            $data=$this->sanitizeInputs($tablename,$data,["skipMissing"]);

            //So we'd hate to update the key being used as
            $keysToEliminate=array_keys($condition);
            foreach($keysToEliminate as $k){ //iterate through condition array to see the keys there
                if(array_key_exists($k,$data)){ //if that key exists in the original dataset,.....
                    unset($data[$k]); //unset it from the original dataset
                }
            }//at the end of this, the original dataset doesnt have the keys being used in the condition as the keys to set
            
            $query="UPDATE $tablename SET ";
            $query .= $this->implementFillables($data); //implementing the fields to be updated/set
            $query.=" where ";
            $query .= $this->implementFillables($condition,$glue);
            //see([$data,$condition]);
            //seedie($query);
            $stmt=$this->conn->prepare($query);
            $result=$stmt->execute(array_merge($data,$condition));
            if($stmt->rowCount() > 0 && $result){ //query success and a row updated
                return $this->selectWhere($tablename,$condition);
            }else if($stmt->rowCount() <= 0 ){ //query success but no row updated
                echo response(1,$this->selectWhere($tablename,$condition),"No Data was updated.");
                die();
            }else{ //general error
                throw new Exception("The update either failed or could not match a row to update",2);
            }
        }catch(Exception $e){
            echo response(0,[$query],"",$e->getMessage());
            die();
        }
    }

    //checks that the cols supplied in the data are available in the db
    public function checkCols($tablename,$data){
        try{
            $query="desc $tablename ";
            $stmt=$this->conn->prepare($query);
            $stmt->execute();
            $result=$stmt->fetchAll();
            $result=array_map(function($value){
                return $value["Field"];
            },$result);
            $result=array_flip($result); //make the values keys
            $unexpectedKeys=array();
            foreach($data as $key=>$val){
                if(!array_key_exists($key,$result)){
                    //$unexpectedKeys[]=$key;
                    array_splice($data,1,1);
                }
            }
            return ($data);
            //see([$data, $unexpectedKeys]);
        }catch(Exception $e){
            echo response(0,[],"",$e->getMessage());
            die();
        }
    }

    //mimimal validation for this function. make certain its accurate (query)
    public function runQuery($query){
        try{
            $stmt=$this->conn->prepare($query);
            $rs=$stmt->execute();
            $result=$stmt->fetchAll();
            return $result;
        }catch(Exception $e){
            echo response(0,[],"",$e->getMessage());
            die();
        }
    }

    public function delete($tablename,$condition){
        try{
            if(empty($condition)){throw new Exception("Condition is required for delete method");}
            $this->checkCondition($condition);
            $query="DELETE FROM $tablename WHERE ";
            $query .= $this->implementFillables($condition,"and");
            $stmt=$this->conn->prepare($query);
            $result=$stmt->execute($condition);
            if($stmt->rowCount()>0 && $result){
                return 1;
            }else if($stmt->rowCount()<=0 && $result){
                echo response($result,[],"No record was deleted");
                die();
            }else{
                throw new Exception("An Error occurred while deleting",5);
            }
        }catch(Exception $e){
            echo response(0,[],"",$e->getMessage());
            die();
        }
    }

    public function checkCondition($condition){
        //will check required condition and break if none is provided
        if(empty($condition)){
            $msg="Looks like your request is lacking parameters to complete it. Aborting Now";
            echo response(0,[],$msg,$msg);die();
        }else{
            return true;
        }
    }

    //organizes fillable elements preparing them to be appended into the sql statement
    public function implementFillables($condition, $glue=" , "){
        $this->checkCondition($condition); //condition must always be available
        $conditionPart="";
        //glue will determine the type of fillable: , for set then and for conditions
        //for the select, update and delete options to fill in the where clause glue=and
        //for the update options, filling in the set glue= , 
        foreach($condition as $key=>$val){
            if(array_key_last($condition)==$key){
                $conditionPart.="$key = :$key ";
            }else{
                $conditionPart.="$key = :$key $glue ";
            }
        }
        return $conditionPart;
    }

    
}

//#######################################################################################################
