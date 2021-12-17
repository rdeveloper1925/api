<?php

namespace App\Middleware;
//require_once "../../vendor/autoload.php";
require_once (__DIR__."\..\..\\vendor\autoload.php");

use Exception;
use Pecee\Http\Middleware\IMiddleware;
use Pecee\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class TestMiddleware implements IMiddleware{
    public function handle(Request $request):void{
        try{
            //can use $request->getHeaders() to get request headers
            //$token=extractToken();
            //decode the token
            //$decoded=JWT::decode($token, new Key(APP_KEY, "HS512"));

            $payload = array(
                "expiry" => time()+(5*60),
                "generated" => time()
            );
            $jwt=JWT::encode($payload,APP_KEY,"HS512");
            var_dump([$_SERVER,$request,$jwt]);

            //die();
        }catch(Exception $e){
            echo response(0,[],"",$e->getMessage());
            die();
        }
    }
}