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
})->add(\MWusuarios::class . ':AccesoSocio');
/*
$app->post('/pedido/', function (Request $request, Response $response){
  $ArrayDeParametros = $request->getParsedBody();
  return Pedido::tomarPedido($ArrayDeParametros);
});
*/
$app->group('/pedido', function(){
  $this->post('/', \Pedido::class . ':tomarPedido');
});

$app->group('/usuario', function(){
  $this->post('/', \Empleado::class . ':CrearUsuario');
  $this->get('/baja/{id}', \Empleado::class . ':BajaUsuario');
  $this->post('/modificar/{id}', \Empleado::class . ':ModificarUsuario');
})->add(\MWusuarios::class . ':AccesoSocio');

$app->group('/mesa', function(){
  $this->post('/', \Mesa::class . ':CrearMesa');
  //$this->get('/baja/{id}', \Mesa::class . ':BajaMesa');
  //$this->post('/modificar/{id}', \Mesa::class . ':ModificarMesa');
})->add(\MWusuarios::class . ':AccesoSocio');

$app->add(\MWusuarios::class . ':AccesoUsuarioRegistrado');

$app->run();