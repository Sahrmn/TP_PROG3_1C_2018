<?php 
require_once 'pedidoPDO.php';
class Pedido
{
	public $codigo;
	public $id_mesa;
	public $nombre_cliente;
	public $productos; //array de objetos tipo producto
    public $estado; // mozo->"cliente esperando pedido" mozo->"clientes comiendo" mozo->"clientes pagando"  socio->"cerrada"
	/*public $tiempo_preparacion;
	public $tiempo_inicial;*/
	public $foto; //armar el metodo para guardar la foto 

	public function crearCodigo()
	{
		$arrayPedidos = Pedido::TraerPedidos();
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

	public static function TraerPedidos()
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("select * from pedidos");
		$consulta->execute();			
		return $consulta->fetchAll(PDO::FETCH_CLASS, "pedido");
	}

    public static function tomarPedido($request, $response)
    {
    	$ArrayDeParametros = new stdclass();
    	$ArrayDeParametros = $request->getParsedBody();
    	//por lo menos tiene que tener un producto 
    	if(isset($ArrayDeParametros['id_mesa']) != null && isset($ArrayDeParametros['cliente']) != null && isset($ArrayDeParametros['id_producto1']) != null && isset($ArrayDeParametros['cantidad_producto1']) != null)
    	{
    		$pedido = new Pedido();
    		$pedido->id_mesa = $ArrayDeParametros['id_mesa'];
    		$pedido->nombre_cliente = $ArrayDeParametros['cliente'];
    		$pedido->crearCodigo();
    		$pedido->productos = array();
            $pedido->estado = "Cliente esperando pedido";

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

        			$nombre = $pedido->nombre_cliente . "_" . $pedido->id_mesa . "_" . Pedido::getFecha();
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
        if (pedidoPDO::InsertarPedido($pedido) == NULL) {
            $respuesta->resultado = "Ocurrio un error al insertar en la base de datos";    
            $retorno = $response->withJson($respuesta, 500);
        }
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
            if($arrayPedidos[$i]->estado == "Cliente esperando pedido")
            {
                array_push($arrayPedidosActivos, $arrayPedidos[$i]);
            }
        }
        
        $arrayPP = pedidoPDO::traerPedidosProductos();

        $arrayProductosActivos = array();
        for ($i=0; $i < count($arrayPP); $i++) { //reviso todos los elementos de la tabla pedidos_productos
            for ($j=0; $j < count($arrayPedidosActivos); $j++) { //verifico que este contenido dentro de los pedidos activos
                if ($arrayPP[$i]->id_pedido == $arrayPedidosActivos[$j]->codigo) {
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

    public static function clienteComiendo()
    {

    }

    public static function clientePagando()
    {

    }

    public static function mesaCerrada()
    {

    }


}

?>