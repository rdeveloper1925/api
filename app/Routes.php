<?php
include_once "Config.php";
include_once "Utils.php";
include_once "Actions/Actions.php";
include_once "Actions/UserActions.php";
include_once "Middleware/AuthMiddleware.php";

use App\Actions\Actions;
use App\Actions\UserActions;
use App\Middleware\AuthMiddleware;
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

//Wrapper for the routes
SimpleRouter::group(["prefix"=>BASE_URL], function (){

    //ROUTE FOR 404 NOT FOUND
    SimpleRouter::all("/not-found", function(){
        return response(0,null,"Unknown Route","Information requested for was not found");
    });

    //ROUTE FOR 403 UNAUTHORIZED
    SimpleRouter::all("/unauthorized", function(){
        return response(0,null,"Unauthorized Access","You are not allowed to view the information requested. Check that you have access or the request method");
    });

    //Primary route
    SimpleRouter::match(["get","post"],"/",[Actions::class,'start']);

    SimpleRouter::get("/tasks", [Actions::class,'getAllTasks']);
    SimpleRouter::post("/tasks", [Actions::class,'saveTask']);
    SimpleRouter::put("/tasks/{id}", [Actions::class,'updateTask']);
    SimpleRouter::delete("/tasks/{id}", [Actions::class,'deleteTask']);

    //USER MANAGEMENT ROUTES
    SimpleRouter::post("/users/token", [UserActions::class, "getToken"]);
    SimpleRouter::post("/users", [UserActions::class, "saveUser"]);
    SimpleRouter::get("/users/{id}", [UserActions::class, "getUser"]);
    SimpleRouter::put("/users/{id}", [UserActions::class, "updateUser"]);
    SimpleRouter::delete("/users/{id}", [UserActions::class, "deleteUser"]);

    //Auth protected routes
    SimpleRouter::group(["middleware"=>AuthMiddleware::class], function (){
        SimpleRouter::get("/test", function(){
            return "protected";
        });
    });

});