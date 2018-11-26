<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


require './composer/vendor/autoload.php';
require_once './clases/AccesoDatos.php';
require_once './clases/AutentificadorJWT.php';
require_once './clases/usuario.php';
require_once './clases/encuesta.php';
require_once './clases/encuestaPDO.php';
require_once './clases/mesa.php';
require_once './clases/pedido.php';
require_once './clases/producto.php';
require_once './clases/productoPDO.php';
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


$app->group('/producto', function(){
  $this->post('/', \Producto::class . ':InsertarProducto');
  $this->get('/baja/{id}', \Producto::class . ':BajaProducto');
  $this->post('/modificar/{id}', \Producto::class . ':ModificarProducto');
})->add(\MWusuarios::class . ':AccesoSocio');

$app->group('/pedido', function(){
  $this->post('/', \Pedido::class . ':tomarPedido')->add(\MWusuarios::class . ':AccesoMozo'); //solo mozo
  $this->get('/cancelar/{id}', \Pedido::class . ':CancelarPedido')->add(\MWusuarios::class . ':AccesoSocio'); 
  $this->get('/ver/', \Pedido::class . ':VerProductosPedidos');
  $this->post('/preparar/', \Pedido::class . ':PrepararPedido');
  $this->post('/fin/', \Pedido::class . ':PedidoListo');
  $this->get('/ver/{id}', \Pedido::class . ':VerPedidoCliente'); //usuarios no registrados
  $this->get('/despachar/{id}', \Pedido::class . ':DespacharPedido')->add(\MWusuarios::class . ':AccesoMozo');
  $this->get('/cobrar/{id}', \Pedido::class . ':CobrarPedido')->add(\MWusuarios::class . ':AccesoMozo');
});


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
  $this->get('/cerrar/{id}', \Mesa::class . ':CerrarMesa');
})->add(\MWusuarios::class . ':AccesoSocio');

$app->group('/encuesta', function(){ //para usuario sin registrar
  $this->post('/{id_pedido}', \Encuesta::class . ':CargarEncuesta');
});

$app->add(\MWusuarios::class . ':AccesoUsuarioRegistrado');

$app->add(\MWLog::class . ':LogActividades');


$app->run();