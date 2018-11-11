<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


require './composer/vendor/autoload.php';
require_once './clases/AccesoDatos.php';
require_once './clases/AutentificadorJWT.php';
require_once './clases/empleado.php';
require_once './clases/encuesta.php';
require_once './clases/mesa.php';
require_once './clases/pedido.php';
require_once './clases/producto.php';
require_once './middleware/MWusuarios.php';

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;


$app = new \Slim\App(["settings" => $config]);

$app->post('/login/', function (Request $request, Response $response) {
  $ArrayDeParametros = $request->getParsedBody();
  return Empleado::verificarCrearToken($ArrayDeParametros); //nombre, clave
});

$app->post('/producto/', function (Request $request, Response $response) {
  $ArrayDeParametros = $request->getParsedBody();
  return Producto::InsertarProducto($ArrayDeParametros);
})->add(\MWusuarios::class . ':VerificarUsuario');
/*
$app->post('/pedido/', function (Request $request, Response $response){
  $ArrayDeParametros = $request->getParsedBody();
  return Pedido::tomarPedido($ArrayDeParametros);
});
*/
$app->group('/pedido', function(){
  $this->post('/', \Pedido::class . ':tomarPedido');
});

/* socio
 "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE1NDE2NDQ2ODEsImV4cCI6MTU0MTY0ODI4MSwiYXVkIjoiMjM3NWQ3OTA4MmI3ZDU5NTgzMTMyMzhiMGEyNmU3NTU5Y2RlNjBhOSIsImRhdGEiOnsibm9tYnJlIjoic2FtYW50aGEiLCJ0aXBvIjoic29jaW8iLCJjbGF2ZSI6bnVsbCwiZXN0YWRvIjpudWxsLCJpZCI6MSwiYWN0aXZvIjoxfSwiYXBwIjoiTGEgY29tYW5kYSJ9.Ogkk_WgYdbx523Te38XQiydO23dtuCSb1NfRE8YW8jc" 

empleado:
"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE1NDE2NDU3NzksImV4cCI6MTU0MTY0OTM3OSwiYXVkIjoiMjM3NWQ3OTA4MmI3ZDU5NTgzMTMyMzhiMGEyNmU3NTU5Y2RlNjBhOSIsImRhdGEiOnsibm9tYnJlIjoiam9yZ2UiLCJ0aXBvIjoiY29jaW5lcm8iLCJjbGF2ZSI6bnVsbCwiZXN0YWRvIjpudWxsLCJpZCI6MiwiYWN0aXZvIjoxfSwiYXBwIjoiTGEgY29tYW5kYSJ9.UvaxLGVvmKdkZZ9s3yQvPC6Hlmk8hdZJ4YGeA47EfoY"

 */





//dar de alta a usuario -> solo socios

/*

$app->group('/cd', function () {
 
  $this->get('/', \cdApi::class . ':traerTodos')->add(\MWparaCORS::class . ':HabilitarCORSTodos');
 
  $this->get('/{id}', \cdApi::class . ':traerUno')->add(\MWparaCORS::class . ':HabilitarCORSTodos');

  $this->post('/', \cdApi::class . ':CargarUno');//->add(\AutenticarAdmin::class . ':VerificarAdmin');

  $this->delete('/', \cdApi::class . ':BorrarUno');

  $this->put('/', \cdApi::class . ':ModificarUno');
     
})->add(\MWparaAutentificar::class . ':VerificarUsuario')->add(\MWparaCORS::class . ':HabilitarCORS8080');

*/
$app->run();