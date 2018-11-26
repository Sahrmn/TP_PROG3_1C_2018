<?php 

class Producto
{
	public $id;
	public $cantidad;
	public $tiempo;
	//lo demas se llena con data de la db
	public $nombre;
	public $precio;
	public $precio_compra;
	public $atendido_por; //bartender, cocinero, mozo, etc.
	public $foto;

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

	public static function InsertarProducto($request, $response)
	{
		$param = $request->getParsedBody();
		if(isset($param['nombre']) != null && isset($param['precio_venta']) != null && isset($param['precio_compra']) != null && isset($param['atendido']) != null)
		{
			$prod = new Producto($param['nombre'], $param['precio_venta'], $param['precio_compra'], $param['atendido']);
			//guardo en bd
			$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
	        $consulta =$objetoAccesoDato->RetornarConsulta("INSERT into productos (nombre, precio, precio_compra, atendido_por) VALUES('$prod->nombre','$prod->precio', '$prod->precio_compra', '$prod->atendido_por')");
			$consulta->execute();	
			$ultimoId = $objetoAccesoDato->RetornarUltimoIdInsertado();
			
			//verifico si existe foto
        	if($request->getUploadedFiles() != null)
        	{
        		$archivo = $request->getUploadedFiles();
        		if(isset($archivo['foto']) != null)
        		{
        			$destino = "./fotos_productos/";
        			//creo carpeta si no existe
        			if (file_exists($destino) == false) {
        				mkdir($destino, 0777);
        			}

        			$nombre = $ultimoId . "_" . $prod->nombre;
        			$extension = $archivo['foto']->getClientFilename();
        			$extension = explode(".", $extension);
        			$extension = $extension[1];
        			$prod->foto = $nombre . "." . $extension;
        			$archivo['foto']->moveTo($destino . $nombre . "." . $extension);
        		}
        	}

			$newResponse = $response->withJson($ultimoId, 200);
			//return $objetoAccesoDato->RetornarUltimoIdInsertado();
		}
		else
		{
			$nueva = new stdclass();
        	$nueva->respuesta = "Faltan parametros o son incorrectos.";
        	$newResponse = $response->withJson($nueva, 200);
        	return $newResponse;
		}
		return $newResponse;
	}

	public static function BajaProducto($request, $response, $args)
	{
		if (isset($args['id']) != null) {
			$id_producto = $args['id'];
			if (productoPDO::eliminar($id_producto) > 0) {
				$nueva->respuesta = "Producto eliminado.";
        		$newResponse = $response->withJson($nueva, 200);		
			}
			else
			{
				throw new Exception("Ocurrio un error.", 500);
			}
		}
		else
		{
			$nueva->respuesta = "Id necesario.";
        	$newResponse = $response->withJson($nueva, 200);
		}
		return $newResponse;
	}

	public static function ModificarProducto($request, $response, $args)
	{
		$param = $request->getParsedBody();

		if (isset($args['id']) != null) {
			$prod = productoPDO::traerUno($args['id']);
			if ($prod != null) {
				$product = $prod[0];
				if (isset($param['nombre'])) {
					$product->nombre = $param['nombre'];
				}
				if (isset($param['precio_venta'])) {
					$product->precio = $param['precio_venta'];	
				}
				if (isset($param['precio_compra'])) {
					$product->precio_compra = $param['precio_compra'];
				}
				if (isset($param['atendido_por'])) {
					$product->atendido_por = $param['atendido_por'];
				}
				//guardo
				if (productoPDO::modificar($product) != null) {
					$nueva->respuesta = "Producto modificado.";
        			$newResponse = $response->withJson($nueva, 200);	
				}
				else
				{
					throw new Exception("Ocurrio un error", 500);
				}
			}
			else
			{
				$nueva->respuesta = "No existe el producto.";
        		$newResponse = $response->withJson($nueva, 200);		
			}
		}
		else
		{
			$nueva->respuesta = "Id necesario.";
        	$newResponse = $response->withJson($nueva, 200);
		}
		return $newResponse;
	}


	public static function RellenarDatos($id, $cantidad)
	{	
		$arrayProductos = productoPDO::traerProductos();
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
			$nueva->respuesta = "No existe el producto.";
        	$newResponse = json_encode($nueva, 200);
        	return $newResponse;
		}
		return $producto;
	}

	

	
}

?>