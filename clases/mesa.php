<?php 

class Mesa
{
	public $codigo;
	public $nombre;
	public $estado; // mozo->"cliente esperando pedido" mozo->"clientes comiendo" mozo->"clientes pagando"  socio->"cerrada"
	
	public function __construct($id_mesa = null)
	{
		if(func_num_args() != 0)
		{
			$arrayMesas = Mesa::traerMesas();
			$flag = false;
			for ($i=0; $i < count($arrayMesas); $i++) { 
				if($arrayMesas[$i]->codigo == $id_mesa)
				{
					$this->codigo = $arrayMesas[$i]->codigo;
					$this->nombre = $arrayMesas[$i]->nombre;
					//$this->estado = "Cliente esperando pedido"; 
					$this->clienteEsperando();
					$flag = true;
				}
			}
			if ($flag == false) {
				throw new Exception("No existe la mesa", 500);
				
			}
		}
	}

	//consultar si se tiene que guardar en db las mesas cada vez que cambia de estado
	public function clienteEsperando()
	{
		$this->estado = "Cliente esperando pedido";
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

	public static function traerMesas()
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("select * from mesas");
		$consulta->execute();			
		return $consulta->fetchAll(PDO::FETCH_CLASS, "mesa");	
	}

}

?>