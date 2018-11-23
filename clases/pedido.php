<?php 
require_once 'pedidoPDO.php';
class Pedido
{
	public $codigo;
	public $codigo_mesa;
	public $nombre_cliente;
	public $productos; //array de objetos tipo producto
    public $estado; // mozo->"cliente esperando pedido" mozo->"clientes comiendo" mozo->"clientes pagando"  socio->"cerrada"
	public $demora; //tiempo calculado del mayor tiempo de los productos
    public $foto;

	public function crearCodigo()
	{
		$arrayPedidos = pedidoPDO::TraerPedidos();
		$ultimoPedido = $arrayPedidos[count($arrayPedidos)-1]->codigo;
		$ultimoPedido = substr($ultimoPedido, -3); //devuelvo solo la parte del numero de pedido

		//$this->codigo = substr($this->nombre_cliente, 0, 2); //obtengo las primeras 2 letras del nombre del cliente
		$this->codigo = "PD";
        $num = (integer)$ultimoPedido + 1;
        if (strlen($num) == 1) {
            $num = "00" . $num;
        }
        if (strlen($num) == 2) {
            $num = "0" . $num;
        }
        $this->codigo = $this->codigo . $num; 
        $this->codigo = strtoupper($this->codigo); //convierto en mayuscula
        if (strlen($num) > 3) {
            $this->codigo = "P0001";
        }
	}

    public static function tomarPedido($request, $response)
    {
    	$ArrayDeParametros = new stdclass();
    	$ArrayDeParametros = $request->getParsedBody();
    	//por lo menos tiene que tener un producto 
    	if(isset($ArrayDeParametros['id_mesa']) != null && isset($ArrayDeParametros['cliente']) != null && isset($ArrayDeParametros['id_producto1']) != null && isset($ArrayDeParametros['cantidad_producto1']) != null)
    	{
    		$pedido = new Pedido();
    		$pedido->codigo_mesa = $ArrayDeParametros['id_mesa'];
    		$pedido->nombre_cliente = $ArrayDeParametros['cliente'];
    		$pedido->crearCodigo();
    		$pedido->productos = array();
            $pedido->estado = "Pendiente";

    		$mesa = new Mesa($ArrayDeParametros['id_mesa']);

    		//veo cuantos productos hay ingresados
    		$num = 1;
    		while($num)
    		{
    			$prod = "id_producto" . $num;
                $cant = "cantidad_producto" . $num;
    			if(isset($ArrayDeParametros[$prod]) && isset($ArrayDeParametros[$cant]))
    				$num++;
    			else
    				break;
    				//$num = -1;
    		}
    		for ($i=1; $i < $num; $i++) { 
    			$id = $ArrayDeParametros["id_producto" . $i];
                $cant = $ArrayDeParametros["cantidad_producto" . $i];
                $producto = Producto::RellenarDatos($id, $cant);
                array_push($pedido->productos, $producto);  
            }

        	//verifico si existe foto
        	if($request->getUploadedFiles() != null)
        	{
        		$archivo = $request->getUploadedFiles();
        		if(isset($archivo['foto']))
        		{
        			$destino = "./fotos_mesas/";
        			//creo carpeta si no existe
        			if (file_exists($destino) == false) {
        				mkdir($destino, 0777);
        			}

        			$nombre = $pedido->codigo . "_" . $pedido->id_mesa;
        			$extension = $archivo['foto']->getClientFilename();
        			$extension = explode(".", $extension);
        			$extension = $extension[1];
        			$pedido->foto = $nombre . "." . $extension;
        			$archivo['foto']->moveTo($destino . $nombre . "." . $extension);
        		}
        	}

            $retorno = $response->withJson($pedido, 200);
        }
        else
        {
            $respuesta->resultado = "Parametros incorrectos o faltantes";    
            $retorno = $response->withJson($respuesta, 500);
        }

        //inserto en la bd
        pedidoPDO::InsertarPedido($pedido);

    	return $retorno;
    }

    public static function VerProductosPedidos($request, $response)
    {
        $arrayConToken = $request->getHeader('token');
        $token = $arrayConToken[0];

        $payload = AutentificadorJWT::ObtenerData($token);
        $tipo = $payload->tipo;

        $respuesta = Pedido::VerProductosPedidosPorTipo($tipo);
        $retorno = $response->withJson($respuesta, 200);

        return $retorno;
    }    

    public static function VerProductosPedidosPorTipo($tipo)
    {
        $arrayPedidos = pedidoPDO::TraerPedidos();
        $arrayPedidosActivos = array();

        for ($i=0; $i < count($arrayPedidos); $i++) { //tomo solo los pedidos esperando ser atendidos
            if($arrayPedidos[$i]->estado == "Pendiente" || $arrayPedidos[$i]->estado == "En preparacion")
            {
                array_push($arrayPedidosActivos, $arrayPedidos[$i]);
            }
        }
        
        $arrayPP = pedido_producto::traerPedidosProductos();

        $arrayProductosActivos = array();
        for ($i=0; $i < count($arrayPP); $i++) { //reviso todos los elementos de la tabla pedidos_productos
            for ($j=0; $j < count($arrayPedidosActivos); $j++) { //verifico que este contenido dentro de los pedidos activos
                if ($arrayPP[$i]->id_pedido == $arrayPedidosActivos[$j]->codigo && $arrayPP[$i]->estado == 0) {
                    array_push($arrayProductosActivos, $arrayPP[$i]);
                }
            }
        }
        $productosEnBD = producto::traerProductos();
        $productosDevueltos = array();
        switch ($tipo) {
            case 'cocinero':
                for ($i=0; $i < count($arrayProductosActivos); $i++) { 
                    for ($j=0; $j < count($productosEnBD); $j++) { 
                        if ($arrayProductosActivos[$i]->id_producto == $productosEnBD[$j]->id) {
                            if ($productosEnBD[$j]->atendido_por == "cocinero") {
                                array_push($productosDevueltos, $arrayProductosActivos[$i]);
                            }
                        }
                    }
                }
                break;
            case 'mozo':
                for ($i=0; $i < count($arrayProductosActivos); $i++) { 
                    for ($j=0; $j < count($productosEnBD); $j++) { 
                        if ($arrayProductosActivos[$i]->id_producto == $productosEnBD[$j]->id) {
                            if ($productosEnBD[$j]->atendido_por == "mozo") {
                                array_push($productosDevueltos, $arrayProductosActivos[$i]);
                            }
                        }
                    }
                }
                break;
            case 'bartender':
                for ($i=0; $i < count($arrayProductosActivos); $i++) { 
                    for ($j=0; $j < count($productosEnBD); $j++) { 
                        if ($arrayProductosActivos[$i]->id_producto == $productosEnBD[$j]->id) {
                            if ($productosEnBD[$j]->atendido_por == "bartender") {
                                array_push($productosDevueltos, $arrayProductosActivos[$i]);
                            }
                        }
                    }
                }
                break;
            case 'cervecero':
                for ($i=0; $i < count($arrayProductosActivos); $i++) { 
                    for ($j=0; $j < count($productosEnBD); $j++) { 
                        if ($arrayProductosActivos[$i]->id_producto == $productosEnBD[$j]->id) {
                            if ($productosEnBD[$j]->atendido_por == "cervecero") {
                                array_push($productosDevueltos, $arrayProductosActivos[$i]);
                            }
                        }
                    }
                }
                break;
            case 'socio':
                //mostrar todos los productos
                $productosDevueltos = $arrayProductosActivos;
                break;
            
            default:
                # mensaje de tipo no permitido
                $respuesta->resultado = "Tipo de usuario no existente.";    
                $retorno = $response->withJson($respuesta, 500);
                break;
        }
        return $productosDevueltos;
    }

    public static function PrepararPedido($request, $response)
    {
        $parametros = $request->getParsedBody();
        $arrayConToken = $request->getHeader('token');
        $token = $arrayConToken[0];

        if (isset($parametros['id_pedido']) != null && isset($parametros['id_producto']) != null && isset($parametros['cantidad']) != null) {
            $id_pedido = $parametros['id_pedido'];
            $id_producto = $parametros['id_producto'];
            $cantidad = $parametros['cantidad'];
            $estado = true;

            //calculo tiempo de preparacion
            $payload = AutentificadorJWT::ObtenerData($token);
            $tipo = $payload->tipo;
            switch ($tipo) {
                case 'cocinero':
                    $tiempo = rand(5, 20)*$cantidad;
                    break;
                case 'bartender':
                    $tiempo = rand(1, 7)*$cantidad;
                    break;
                case 'cervecero':
                    $tiempo = rand(1, 3)*$cantidad;
                    break;
                default:
                    $tiempo = rand(10, 15)*$cantidad;
                    break;
            }

            $estado_pedido = "En preparacion";

            //guardo tiempo en el tiempo_preparacion de pedido_producto
            //actualizo estado del pedido en bd
            if(pedido_producto::ModificarPedidoProducto($id_pedido, $id_producto, $estado, $tiempo) > 0 && pedidoPDO::ModificarEstadoPedidoBD($id_pedido, $estado_pedido) > 0)
            {
                $nueva = new stdclass();
                $nueva->respuesta = "Pedido en preparacion";
                $nueva = $response->withJson($nueva, 200);
            }
            else
            {
                $nueva = new stdclass();
                $nueva->respuesta = "Ocurrio un error";
                $nueva = $response->withJson($nueva, 200);
            }
        }
        else
        {
            $nueva = new stdclass();
            $nueva->respuesta = "Parametros incorrectos o faltantes";
            $nueva = $response->withJson($nueva, 200);
        }
        return $nueva;
    }

    public static function PedidoListo($request, $response)
    {
        $parametros = $request->getParsedBody();
        if(isset($parametros['id_pedido']) != null && isset($parametros['id_producto']) != null)
        {
            //cambio estado del pedido_producto a 2 (tomado y terminado)
            if (isset($parametros['id_pedido']) != null && isset($parametros['id_producto']) != null) {
                if (pedido_producto::ModificarPedidoProductoEstado($parametros['id_pedido'], $parametros['id_producto'], 2) > 0) {
                    $nueva = new stdclass();
                    $nueva->respuesta = "Listo para servir... esperando los otros productos";
                    $nueva = $response->withJson($nueva, 200);
                }
                else
                {
                    $nueva = new stdclass();
                    $nueva->respuesta = "Ocurrio un error";
                    $nueva = $response->withJson($nueva, 200);
                }
            }

            $arrayPedidosProductos = pedido_producto::traerPedidosProductos();
            //obtengo todos los productos de ese pedido
            $productosDelPedido = array();
            for ($i=0; $i < count($arrayPedidosProductos); $i++) { 
                if ($arrayPedidosProductos[$i]->id_pedido == $parametros['id_pedido']) {
                    array_push($productosDelPedido, $arrayPedidosProductos[$i]);
                }
            }
            //verifico si todos los productos estan tomados y listos
            $flag = true;
            for ($i=0; $i < count($productosDelPedido); $i++) { 
                if ($productosDelPedido[$i]->estado == 0 || $productosDelPedido[$i]->estado == 1) {
                    $flag = false;
                }
            }
            if ($flag) {
                //calculo tiempo de demora y guardo
                $maxTiempo = $productosDelPedido[0]->tiempo_preparacion;
                for ($i=0; $i < count($productosDelPedido); $i++) { 
                    for ($j=0; $j < count($productosDelPedido); $j++) { 
                        if ($productosDelPedido[$i]->tiempo_preparacion < $productosDelPedido[$j]->tiempo_preparacion) {
                            $maxTiempo = $productosDelPedido[$j]->tiempo_preparacion;
                            break;
                        }
                    }
                }
                //listo para servir
                if(pedidoPDO::ModificarEstadoFinal($parametros['id_pedido'], $maxTiempo) != null)
                {
                    $nueva = new stdclass();
                    $nueva->respuesta = "Pedido listo para servir";
                    $nueva = $response->withJson($nueva, 200);
                }
                else
                {
                    $nueva = new stdclass();
                    $nueva->respuesta = "Ocurrio un error";
                    $nueva = $response->withJson($nueva, 200);
                }
            }
            return $nueva;
        }

    }

    public static function VerPedidoCliente($request, $response, $args)
    {
        if (isset($args['id']) != null) {
            $id_pedido = $args['id'];
            $pedido = pedidoPDO::traerUnPedido($id_pedido);
            //var_dump($pedido);
            //die();
            /*$pedidoCliente = new Pedido();
            $pedidoCliente->codigo = $pedido[0]->codigo;
            $pedidoCliente->nombre_cliente = $pedido[0]->nombre_cliente;
            $pedidoCliente->id_mesa = $pedido[0]->codigo_mesa;
            $pedidoCliente->fecha = $pedido[0]->fecha;
            
            $nueva = $response->withJson($pedidoCliente, 200);*/

            //veo si existe foto
            $ruta1 = './fotos_mesas/' . $id_pedido . '_' . $pedido->codigo_mesa . '.jpg';
            $ruta2 = './fotos_mesas/' . $id_pedido . '_' . $pedido->codigo_mesa . '.png';
            if (isset($ruta1) != null) {
                $pedido->foto = $ruta1;   
            }
            else if(isset($ruta2) != null)
            {
                $pedido->foto = $ruta2;                
            }

            $nueva = $response->withJson($pedido, 200);
        }
        else
        {
            $retorno->respuesta = "No existe el pedido";
            $nueva = $response->withJson($retorno, 200);
        }
        return $nueva;
    }



}

?>