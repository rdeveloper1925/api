<?php

namespace App\Middleware;
//require_once "../../vendor/autoload.php";
require_once (__DIR__."\..\..\\vendor\autoload.php");

use Exception;
use Firebase\JWT\ExpiredException;
use Pecee\Http\Middleware\IMiddleware;
use Pecee\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware implements IMiddleware{
    public function handle(Request $request):void{
        try{
            //can use $request->getHeaders() to get request headers
            $token=extractToken();
            //decode the token
            $decoded=JWT::decode($token, new Key(APP_KEY, "HS512"));
            //verify expiry validity of the token
            see($decoded);
            if($decoded->expiry <= time()){
                throw new ExpiredException("Token has expired. Request for a new one");
            }

        }catch(Exception $e){
            echo response(0,[],"",$e->getMessage());
            die();
        }
    }
}