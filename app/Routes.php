<?php
include_once "Config.php";
include_once "Utils.php";
include_once "Actions/Actions.php";
include_once "Actions/ProductActions.php";
include_once "Actions/UserActions.php";
include_once "Actions/CategoryActions.php";
include_once "Middleware/AuthMiddleware.php";

use App\Actions\Actions;
use App\Actions\ProductActions;
use App\Actions\CategoryActions;
use App\Actions\UserActions;
use App\Middleware\AuthMiddleware;
use Pecee\Http\Request;
use Pecee\Http\Response;

use Pecee\SimpleRouter\SimpleRouter;

SimpleRouter::response()->header("Content-Type: application/json");
SimpleRouter::response()->header("Access-Control-Allow-Origin: http://localhost:3000"); //required with react apps. replace with the react app url upon deployment
SimpleRouter::response()->header("Access-Control-Allow-Headers: Content-type"); //required with react apps. replace with the react app url upon deployment

#####################################START OF DEFAULT ROUTES! DO NOT DELETE#########################
//ERROR HANDLING FOR ROUTING
SimpleRouter::error(function(Request $request,Exception $exception){
    switch($exception->getCode()){
        case "4054":
            SimpleRouter::response()->redirect(API_BASE_URL."/not-found");
            break;
        
        case "4043":
            SimpleRouter::response()->redirect(API_BASE_URL."/unauthorized");
            break;

        default:
            return response(0,$exception,$exception->getMessage());
    }
});

//Route for files
SimpleRouter::get(API_BASE_URL."/files/{tablename}/{id}",[Actions::class,"getFile"]);
SimpleRouter::get(WEB_BASE_URL."/files/{tablename}/{id}",[Actions::class,"getFile"]);
##################################### END OF DEFAULT ROUTES! DO NOT DELETE#########################


//Wrapper for the routes
SimpleRouter::group(["prefix"=>API_BASE_URL], function (){

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

//web routes
SimpleRouter::group(["prefix"=>WEB_BASE_URL], function (){
    SimpleRouter::post("/login", [UserActions::class, "login"]);

    SimpleRouter::post("products", [ProductActions::class, "saveProduct"]);
    SimpleRouter::get("products", [ProductActions::class, "getProducts"]);
    SimpleRouter::get("products/{id}", [ProductActions::class, "getProduct"]);
    SimpleRouter::put("products/{id}", [ProductActions::class, "updateProduct"]);
    SimpleRouter::delete("products/{id}", [ProductActions::class, "deleteProduct"]);
    //join test
    SimpleRouter::get("product/join", [ProductActions::class, "joinTest"]);

    //product category routes
    SimpleRouter::post("categories", [CategoryActions::class, "saveCategory"]);
    SimpleRouter::get("categories", [CategoryActions::class, "getCategories"]);
    SimpleRouter::get("categories/{id}", [CategoryActions::class, "getCategory"]);
    SimpleRouter::put("categories/{id}", [CategoryActions::class, "updateCategory"]);
    SimpleRouter::delete("categories/{id}", [CategoryActions::class, "deleteCategory"]);

    //Handling images
    SimpleRouter::get("/products/images/{img}",function($img){
        $path=__DIR__."\Storage\productImages\img.jpg";
        header('Content-Disposition: attachment; filename="Storage/productImages/img.jpg"');
        echo readfile($path);
        return 1;
    });

    SimpleRouter::get("/web", function(){
        return "Hello webber";
    });
});