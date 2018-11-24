<?php 
include_once 'mesaPDO.php'; 
class Mesa
{
	public $codigo;
	public $nombre;

	public static function RellenarDatos($id)
	{
		$arrayMesas = mesaPDO::traerMesas();
		$flag = false;
		for ($i=0; $i < count($arrayMesas); $i++) { 
			if($arrayMesas[$i]->codigo == $id)
			{
				$this->codigo = $arrayMesas[$i]->codigo;
				$this->nombre = $arrayMesas[$i]->nombre;
				$flag = true;
			}
		}
		if ($flag == false) {
			$nueva = new stdclass();
	       	$nueva->respuesta = "No existe la mesa";
	        $retorno = json_encode($nueva, 500);
	        return $retorno;
		}	
	}

	public static function CrearMesa($request, $response)
	{
		$parametros = $request->getParsedBody();

		if(isset($parametros['nombre']) != null)
		{
			$mesa = new Mesa();
			$mesa->nombre = $parametros['nombre'];
			$mesa->crearCodigo();

			if (mesaPDO::InsertarMesaBD($mesa) != null) {
				$retorno = $response->withJson("Mesa creada correctamente", 200);
			}
			else
			{
				throw new Exception("Error SQL", 500);
			}
		}	
		else
		{
			$nueva = new stdclass();
	        $nueva->respuesta = "Parametros incorrectos y faltantes";
	       	$retorno = $response->withJson($nueva, 500);
		}
		return $retorno;
	}

	public function crearCodigo()
	{
		$arrayMesas = mesaPDO::traerMesas();
		$ultimaMesa = $arrayMesas[count($arrayMesas)-1]->codigo;
		$ultimaMesa = substr($ultimaMesa, -2); //devuelvo solo la parte del numero 
		$this->codigo = "MES";
		$num = (integer)$ultimaMesa + 1;
		if(strlen($num) < 2)
		{
			$num = "0" . $num;
		}
		$this->codigo = $this->codigo . $num; 
		//var_dump($this->codigo);
		$this->codigo = strtoupper($this->codigo); //convierto en mayuscula
	}


	//consultar si se tiene que guardar en db las mesas cada vez que cambia de estado -> si
	

	public static function BajaMesa($request, $response, $args)
	{
		if(isset($args['id']) != null)
		{
			$respuesta = new stdclass();
			if(mesaPDO::BorrarMesaBD($id) > 0) 
			{	
				$respuesta->resultado = "Baja exitosa";
			}
			else
			{
				throw new Exception("Ocurrio un error al realizar la baja de usuario", 500);
			}
			$nueva = $response->withJson($respuesta, 200);
		}
		else
		{
			$respuesta->resultado = "Se necesita un id";	
			$nueva = $response->withJson($respuesta, 200);
		}
		return $nueva;
	}

	
	public static function ModificarMesa($request, $response, $args)
	{
		$param = $request->getParsedBody();
		$respuesta = new stdclass();
		if(isset($args['id']) != null)
		{
			//traigo mesa
			$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
	        $consulta =$objetoAccesoDato->RetornarConsulta("select * from mesas where codigo = :id");
	        $consulta->bindValue(':id', $args['id'], PDO::PARAM_STR);
			$consulta->execute();			
			$mesa = $consulta->fetchObject('mesa'); 

			if ($mesa != null) {
				//modifico atributos 
				if (isset($param['nombre'])) {
					$mesa->nombre = $param['nombre'];
				}
				//guardo
				if(mesaPDO::ModificarMesaBD($mesa))
				{
					//$nueva = $response->withJson($mesa, 200);
					$respuesta->resultado = "Mesa modificada correctamente";
					$nueva = $response->withJson($respuesta, 200);
				}
				else
				{
					throw new Exception("No se pudo guardar", 500);
				}
			}
			else
			{
	        	$nueva->respuesta = "No existe la mesa";
	        	$nueva = $response->withJson($nueva, 200);
			}
		}
		else
		{
	       	$nueva->respuesta = "Se necesita un id";
	        $nueva = $response->withJson($nueva, 200);
		}
		return $nueva;
	}

	public static function CerrarMesa($request, $response, $args)
	{
		if (isset($args['id']) != null) {
			if (mesaPDO::ModificarEstado($args['id'], "Cerrada") != null) {
				$nueva->respuesta = "Mesa cerrada";
	        	$nueva = $response->withJson($nueva, 200);
			}
			else
			{
				throw new Exception("No se pudo modificar", 500);
			}
		}
		else
		{
			$nueva->respuesta = "Se necesita un id";
	        $nueva = $response->withJson($nueva, 200);
		}
		return $nueva;
	}

}

?>