<?php 

class Producto
{
	public $nombre;
	public $precio;
	public $precio_compra;
	public $atendido_por; //bartender, cocinero, mozo, etc.

	public function __construct($nom, $precio, $precioc, $atendido)
	{
		$this->nombre = $nom;
		$this->precio = $precio;
		$this->precio_compra = $precioc;
		$this->atendido_por = $atendido;
	}


	//llamo a metodo cada vez que se agrega un producto a la base de datos
	public static function traerDB()
	{
		//consultar si nos manejamos con json o con la base de datos nomas
	}

	public static function InsertarProducto($param)
	{
		if($param['nombre'] != null && $param['precio_venta'] != null && $param['precio_compra'] != null && $param['atendido'] != null)
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
			throw new Exception("Faltan parametros o son incorrectos.");
		}
	}
}

?>