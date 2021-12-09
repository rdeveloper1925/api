<?php
include_once "Config.php";
include_once "Utils.php";
include_once "Actions.php";

use App\Actions;
use Pecee\Http\Request;
use Pecee\Http\Response;

use Pecee\SimpleRouter\SimpleRouter;

//SimpleRouter::response()->header("Content-Type: application/json");
//ERROR HANDLING FOR ROUTING
SimpleRouter::error(function(Request $request,Exception $exception){
    switch($exception->getCode()){
        case "404":
            SimpleRouter::response()->redirect(BASE_URL."/not-found");
            break;
        
        case "403":
            SimpleRouter::response()->redirect(BASE_URL."/unauthorized");
            break;

        default:
            return response(0,$exception,$exception->getMessage());
    }
});

//ROUTE FOR 404 NOT FOUND
SimpleRouter::all(BASE_URL."/not-found", function(){
    return response(0,null,"Unknown Route","Information requested for was not found");
});

//ROUTE FOR 403 UNAUTHORIZED
SimpleRouter::all(BASE_URL."/unauthorized", function(){
    return response(0,null,"Unauthorized Access","You are not allowed to view the information requested. Check that you have access or the request method");
});

//Primary route
SimpleRouter::get(BASE_URL."/",[Actions::class,'start']);

SimpleRouter::get(BASE_URL."/tasks", [Actions::class,'getAllTasks']);
SimpleRouter::post(BASE_URL."/tasks", [Actions::class,'saveTask']);
SimpleRouter::put(BASE_URL."/tasks/{id}", [Actions::class,'updateTask']);
SimpleRouter::delete(BASE_URL."/tasks/{id}", [Actions::class,'deleteTask']);

