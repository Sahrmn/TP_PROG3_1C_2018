<?php 

class Producto
{
	public $id;
	public $cantidad;
	//lo demas se llena con data de la db
	public $nombre;
	public $precio;
	public $precio_compra;
	public $atendido_por; //bartender, cocinero, mozo, etc.

	public function __construct($nom = null, $precio = null, $precioc = null, $atendido = null)
	{
		if(func_num_args() != 0)
		{
			$this->nombre = $nom;
			$this->precio = $precio;
			$this->precio_compra = $precioc;
			$this->atendido_por = $atendido;
		}
	}

	public static function InsertarProducto($param)
	{
		if(isset($param['nombre']) != null && isset($param['precio_venta']) != null && isset($param['precio_compra']) != null && isset($param['atendido']) != null)
		{
			$prod = new Producto($param['nombre'], $param['precio_venta'], $param['precio_compra'], $param['atendido']);
			//guardo en bd
			$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
	        $consulta =$objetoAccesoDato->RetornarConsulta("INSERT into productos (nombre, precio, precio_compra, atendido_por) VALUES('$prod->nombre','$prod->precio', '$prod->precio_compra', '$prod->atendido_por')");
			$consulta->execute();	
			return $objetoAccesoDato->RetornarUltimoIdInsertado();
		}
		else
		{
			$nueva = new stdclass();
        	$nueva->respuesta = "Faltan parametros o son incorrectos.";
        	$newResponse = json_encode($nueva, 200);
        	return $newResponse;
		}
	}

	public static function RellenarDatos($id, $cantidad)
	{	
		$arrayProductos = Producto::traerProductos();
		$flag = false;
		for ($i=0; $i < count($arrayProductos); $i++) { 
			if($arrayProductos[$i]->id == $id)
			{
				/*$this->nombre = $arrayProductos[$i]->nombre;
				$this->precio = $arrayProductos[$i]->precio;
				$this->precio_compra = $arrayProductos[$i]->precio_compra;
				$this->atendido_por = $arrayProductos[$i]->atendido_por;*/
				$producto = new Producto($arrayProductos[$i]->nombre, $arrayProductos[$i]->precio, $arrayProductos[$i]->precio_compra, $arrayProductos[$i]->atendido_por);
				$producto->id = $id;
				$producto->cantidad = $cantidad;
				
				$flag = true;
			}
		}
		if ($flag == false) {
			throw new Exception("No existe el producto", 500);
		}
		return $producto;
	}

	public static function traerProductos()
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta = $objetoAccesoDato->RetornarConsulta("select * from productos");
		$consulta->execute();			
		return $consulta->fetchAll(PDO::FETCH_CLASS, "producto");
	}
}

?>