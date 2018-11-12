<?php 

class Mesa
{
	public $codigo;
	public $nombre;
	public $estado; // mozo->"cliente esperando pedido" mozo->"clientes comiendo" mozo->"clientes pagando"  socio->"cerrada"

	public static function RellenarDatos($id)
	{
		$arrayMesas = Mesa::traerMesas();
		$flag = false;
		for ($i=0; $i < count($arrayMesas); $i++) { 
			if($arrayMesas[$i]->codigo == $id)
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

	public static function CrearMesa($request, $response)
	{
		$parametros = $request->getParsedBody();
		$nom = $parametros['nombre'];

		if($nom != null)
		{
			$mesa = new Mesa();
			$mesa->nombre = $nom;
			$mesa->crearCodigo();

			if (Mesa::InsertarMesaBD($mesa) != null) {
				$retorno = $response->withJson("Mesa creada correctamente", 200);
			}
			else
			{
				throw new Exception("Error al insertar en base de datos");
			}
		}	
		else
		{
			throw new Exception("Parametros incorrectos y faltantes", 500);
		}
		return $retorno;
	}

	public function crearCodigo()
	{
		$arrayMesas = Mesa::traerMesas();
		$ultimaMesa = $arrayMesas[count($arrayMesas)-1]->codigo;
		$ultimaMesa = substr($ultimaMesa, -2); //devuelvo solo la parte del numero 
		$this->codigo = "MES";
		//$num = $ultimaMesa+1;
		var_dump($this->codigo);
		$this->codigo = $this->codigo . $ultimaMesa++; 
		var_dump($this->codigo);
		die();
		$this->codigo = strtoupper($this->codigo); //convierto en mayuscula
	}

	public static function InsertarMesaBD($mesa)
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("INSERT into mesas (codigo, nombre) VALUES('$mesa->codigo', '$mesa->nombre')");
		$consulta->execute();	
		return $objetoAccesoDato->RetornarUltimoIdInsertado();
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