<?php
include_once "Config.php";
include_once "Utils.php";
include_once "Actions/Actions.php";
include_once "Actions/UserActions.php";

use App\Actions\Actions;
use App\Actions\UserActions;
use Pecee\Http\Request;
use Pecee\Http\Response;

use Pecee\SimpleRouter\SimpleRouter;

//SimpleRouter::response()->header("Content-Type: application/json");
//ERROR HANDLING FOR ROUTING
SimpleRouter::error(function(Request $request,Exception $exception){
    switch($exception->getCode()){
        case "4054":
            SimpleRouter::response()->redirect(BASE_URL."/not-found");
            break;
        
        case "4043":
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
SimpleRouter::match(["get","post"],BASE_URL."/",[Actions::class,'start']);

SimpleRouter::get(BASE_URL."/tasks", [Actions::class,'getAllTasks']);
SimpleRouter::post(BASE_URL."/tasks", [Actions::class,'saveTask']);
SimpleRouter::put(BASE_URL."/tasks/{id}", [Actions::class,'updateTask']);
SimpleRouter::delete(BASE_URL."/tasks/{id}", [Actions::class,'deleteTask']);

//USER MANAGEMENT ROUTES
SimpleRouter::post(BASE_URL."/users/token", [UserActions::class, "getToken"]);
SimpleRouter::post(BASE_URL."/users", [UserActions::class, "saveUser"]);
SimpleRouter::get(BASE_URL."/users/{id}", [UserActions::class, "getUser"]);
SimpleRouter::put(BASE_URL."/users/{id}", [UserActions::class, "updateUser"]);
SimpleRouter::delete(BASE_URL."/users/{id}", [UserActions::class, "deleteUser"]);