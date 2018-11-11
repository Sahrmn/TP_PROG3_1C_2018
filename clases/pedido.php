<?php 

class Pedido
{
	public $codigo;
	public $id_mesa;
	public $nombre_cliente;
	public $productos; //array de objetos tipo producto
	/*public $tiempo_preparacion;
	public $tiempo_inicial;*/
	public $foto; //armar el metodo para guardar la foto 
	//private static $cod = 10;

	/*public function __construct($ArrayDeParametros)
	{
		$this->crearCodigo($ArrayDeParametros['mesa']); //creo codigo de pedido a partir del nombre de la mesa
		$this->cliente = $ArrayDeParametros['cliente'];
		$this->mesa = $ArrayDeParametros['mesa'];
		$this->tiempo_inicial = getFecha();
		$this->tiempo_inicial = $this->tiempo_inicial . getHora();
		$this->productos = array();
	}*/ 

	public function crearCodigo()
	{
		$arrayPedidos = Pedido::TraerPedidos();
		$ultimoPedido = $arrayPedidos[count($arrayPedidos)-1]->codigo;
		$ultimoPedido = substr($ultimoPedido, -3); //devuelvo solo la parte del numero de pedido

		$this->codigo = substr($this->nombre_cliente, 0, 2); //obtengo las primeras 2 letras del nombre del cliente
		$this->codigo = $this->codigo . $ultimoPedido++; 
		$this->codigo = strtoupper($this->codigo); //convierto en mayuscula
	}

	public static function TraerPedidos()
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("select * from pedidos");
		$consulta->execute();			
		return $consulta->fetchAll(PDO::FETCH_CLASS, "pedido");
	}

	public static function getFecha(){
       $anio = date('Y');
       $mes = date('m');
       $dia = date('d');
       return $dia . '-' . $mes . '-' . $anio;
    } 

    public static function getHora(){
       $hora = date('H');
       $minutos = date('i');
       return '-' . $hora . ':' . $minutos;
    } 

    public static function tomarPedido($request, $response)
    {
    	$ArrayDeParametros = new stdclass();
    	$ArrayDeParametros = $request->getParsedBody();
    	//por lo menos tiene que tener un producto 
    	if($ArrayDeParametros['id_mesa'] != null && $ArrayDeParametros['cliente'] != null && $ArrayDeParametros['id_producto1'] != null && $ArrayDeParametros['cantidad_producto1'] != null)
    	{
    		$pedido = new Pedido();
    		$pedido->id_mesa = $ArrayDeParametros['id_mesa'];
    		$pedido->nombre_cliente = $ArrayDeParametros['cliente'];
    		$pedido->crearCodigo();
    		$pedido->productos = array();

    		$mesa = new Mesa($ArrayDeParametros['id_mesa']);

    		//veo cuantos productos hay ingresados
    		$num = 1;
    		while($num)
    		{
    			$prod = "id_producto" . $num;
    			if(isset($ArrayDeParametros[$prod]))
    				$num++;
    			else
    				break;
    				//$num = -1;
    		}
    		for ($i=0; $i < $num-1; $i++) { 
    			$indice = $i+1;
    			$id = $ArrayDeParametros["id_producto" . $indice];
    			$cant = $ArrayDeParametros["cantidad_producto" . $indice];
    			$producto = Producto::RellenarDatos($id, $cant);
    			array_push($pedido->productos, $producto);	
    		}
    	}
    	else
    	{
    		throw new Exception("Parametros incorrectos o faltantes", 500);
    		
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
    	return $retorno;
    }



}

?>