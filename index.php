<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


require './composer/vendor/autoload.php';
require_once './clases/AccesoDatos.php';
require_once './clases/AutentificadorJWT.php';
require_once './clases/usuario.php';
require_once './clases/encuesta.php';
require_once './clases/mesa.php';
require_once './clases/pedido.php';
require_once './clases/producto.php';
require_once './clases/pedido_producto.php';
require_once './middleware/MWusuarios.php';
require_once './middleware/MWLog.php';

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;


$app = new \Slim\App(["settings" => $config]);


$app->post('/login/', function (Request $request, Response $response) {
  $ArrayDeParametros = $request->getParsedBody();
  return Usuario::verificarCrearToken($ArrayDeParametros); //nombre, clave
});


$app->post('/producto/', function (Request $request, Response $response) {
  $ArrayDeParametros = $request->getParsedBody();
  return Producto::InsertarProducto($ArrayDeParametros);
})->add(\MWusuarios::class . ':AccesoSocio');


$app->group('/pedido', function(){
  $this->post('/', \Pedido::class . ':tomarPedido')->add(\MWusuarios::class . ':AccesoMozo'); //solo mozo
  //$this->get('/baja/{id}', \Pedido::class . ':BajaPedido'); consultar si es necesaria la baja de pedidos o solo se cancela
  //$this->post('/modificar/{id}', \Pedido::class . ':ModificarPedido'); consultar si se puede modificar un pedido
  $this->get('/ver/', \Pedido::class . ':VerProductosPedidos');
  $this->post('/preparar/', \Pedido::class . ':PrepararPedido');
  $this->post('/fin/', \Pedido::class . ':PedidoListo');
  $this->get('/ver/{id}', \Pedido::class . ':VerPedidoCliente'); //usuarios no registrados
});

//

//consultar si solo puede cambiar el pedido a listo si ese usuario tomo ese pedido


$app->group('/usuario', function(){
  $this->post('/', \Usuario::class . ':CrearUsuario'); 
  $this->get('/suspender/{id}', \Usuario::class . ':SuspenderUsuario');//da de baja, no elimina
  $this->get('/baja/{id}', \Usuario::class . ':BajaUsuario');
  $this->post('/modificar/{id}', \Usuario::class . ':ModificarUsuario');
})->add(\MWusuarios::class . ':AccesoSocio');


$app->group('/mesa', function(){
  $this->post('/', \Mesa::class . ':CrearMesa');
  $this->get('/baja/{id}', \Mesa::class . ':BajaMesa');
  $this->post('/modificar/{id}', \Mesa::class . ':ModificarMesa');
})->add(\MWusuarios::class . ':AccesoSocio');


$app->add(\MWusuarios::class . ':AccesoUsuarioRegistrado');

$app->add(\MWLog::class . ':LogActividades');


$app->run();