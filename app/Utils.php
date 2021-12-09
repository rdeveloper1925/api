<?php
include_once "Config.php";

function see($variable){
    $trace=debug_backtrace();
    $cut_trace=array_shift($trace);
    $line=$cut_trace['line'];
    $file=$cut_trace['file'];
    echo "Seeing var at Line: $line :: $file <br/><pre>".var_dump($variable)."</pre>";
    return;
}

function seedie($variable){
    $trace=debug_backtrace();
    $cut_trace=array_shift($trace);
    $line=$cut_trace['line'];
    $file=$cut_trace['file'];
    echo "Seeing var at Line: $line :: $file <br/><pre>".var_dump($variable)."</pre>";
    die("because seedie");
    return;
}

//STANDARD RESPONSE
// {
//     'success':0,
//     'message':{
//         'data':{...}
//     },
//     'errors':{
        
//     }
// }
//offers standardized json responses across the api
function response(int $success,$data=[],$information="",$errors=""){
    $response=array(
        "success"=>$success,
        "message"=>array(
            "information"=>$information,
            "data"=>$data,
            "errors"=>$errors
        )
    );
    return json_encode($response);
}
//password hashing 
function mask($pass){
    //salting
    $pass=$pass.APP_KEY;
    return password_hash($pass,PASSWORD_BCRYPT);
}

//request input validation
function validate($data){
    $validationResult=array();
    foreach($data as $key=>$val){
        $val=trim($val);
        $key=trim($key);
        $data[$key]=$val; //strip extra spaces in vals.
        switch($key){
            case "username":
                //the particular validation function returns a message and attaches to the validationResult array. if no issue, it returns the old array
                $validationResult=maxCharValidation($key,$val,$validationResult);
                $validationResult=minCharValidation($key,$val,$validationResult);
                break;

            case "firstName":case "lastName":
                $msgonfailure="Looks like the ".ucwords($key)." has some invalid characters";
                $validationResult=regexMatcher($val,$validationResult,'/^[a-zA-Z]+$/',$msgonfailure);
                break;

            case "email":
                if(!filter_var($val, FILTER_VALIDATE_EMAIL)) {
                    $validationResult[]="Please check that the email is valid";
                }
                break;

            case "password":
                $validationResult=minCharValidation($key,$val,$validationResult,10);
                break;


            default:

        }
    }
    return $validationResult;
}

//max character validation rule
function maxCharValidation($key,$value,$validationResult, $maxchars=25){
    if(strlen($value) > $maxchars){
        $validationResult[]="The $key must have a max of $maxchars characters";
    }
    return $validationResult;
}

//min character validation rule
function minCharValidation($key,$value,$validationResult, $minchars=4){
    if(strlen($value) < $minchars){
        $validationResult[]="The $key must have a minimum of $minchars characters";
    }
    return $validationResult;
}

//regex matcher
function regexMatcher($value, $validationResult, $pattern, $msgonfailure="Malformed values received"){
    preg_match($pattern,$value,$output);
    if(empty($output)){ //not matched, hence failure
        $validationResult[]=$msgonfailure;
    }
    return $validationResult;
}

//request input validation
function validate_old($validateAs="string",$var){
    switch(strtolower($validateAs)){
        case "email":
            $result=filter_var($var,FILTER_VALIDATE_EMAIL)?true:false;
            break;

        case "string":
            $result=is_string($var)?true:false;
            break;

        case "integer":
            $result=is_integer($var)?true:false;
            break;

        case "date":
            $pregResult=preg_match("(^\d{4}\-[0|1]\d\-[0|1|2|3]\d$)",$var,$matches);
            $result=!empty($pregResult)?true:false;

        default:
            $result=false;
    }

    return $result;
}