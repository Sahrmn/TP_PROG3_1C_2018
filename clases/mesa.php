<?php 

class Mesa
{
	public $codigo;
	public $nombre;
	public $estado; // mozo->"cliente esperando pedido" mozo->"clientes comiendo" mozo->"clientes pagando"  socio->"cerrada"
	public static $cod = 000;
	
	/*public function __construct($cod, $nom)
	{
		$this->codigo = $cod;
		$this->nombre = $nom;
		$this->estado = "Cliente esperando pedido"; 
	}*/

	public function crearCodigo()
	{
		$this->cod = $this->cod++;
		$this->codigo = substr($nombre, 0, 3); //obtengo las primeras 3 letras del nombre de la mesa
		$this->codigo = $this->codigo . $this->cod; 
	}


	//consultar si se tiene que guardar en db las mesas cada vez que cambia de estado
	public static function clienteEsperando($nom)
	{
		$mesa = new Mesa();
		$mesa->nombre = $nom;
		$mesa->codigo = crearCodigo();
		$mesa->estado = "Cliente esperando";
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