<?php 
include_once 'usuarioPDO.php';
class Usuario
{
	public $id;
	public $nombre;
	public $tipo;
	public $clave;
	public $activo; //activo/suspendido -> 1/0
	//public $pendientes; //array de pedidos pendientes a atender

	public function __construct($nom = null, $pass = null, $type = null, $activo = null)
	{
		if(func_num_args() != 0)
		{
			$this->nombre = $nom;
			$this->tipo = $type;
			$this->clave = $pass;
			$this->activo = $activo;
		}
	}

	public static function verificarCrearToken($ArrayDeParametros)
	{
		$datos = array('nombre' => $ArrayDeParametros['nombre'], 'clave' => $ArrayDeParametros['clave']);
        //verificar en bd
        $employee = new Usuario($datos['nombre'], $datos['clave']);
        $response = $employee->VerificarUsuario(); 
        if($response == false)
        {
        	$nueva = new stdclass();
        	$nueva->respuesta = "El usuario no existe.";
        	$newResponse = json_encode($nueva, 200);
        }
        else
        {  
          	$token = AutentificadorJWT::CrearToken($response); 
          	$newResponse = json_encode($token, 200); 
        }
        return $newResponse;
	}

	public function VerificarUsuario()
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta =$objetoAccesoDato->RetornarConsulta("select id, nombre, tipo, activo from usuarios where nombre = :nombreUsuario AND clave = :clave");
        $consulta->bindValue(':nombreUsuario', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $this->clave, PDO::PARAM_STR);
		$consulta->execute();			
		$employee = $consulta->fetchObject('usuario'); //nombre de la clase
        if($employee != NULL)
        {
            $nueva = $employee;
        }
        else
        {
            $nueva = false;
        }
        return $nueva;
	}

	public static function CrearUsuario($request, $response) //solo socios
	{
		$parametros = $request->getParsedBody();

		if(isset($parametros['nombre']) != null && isset($parametros['clave']) != null && isset($parametros['tipo']) != null && isset($parametros['activo']) != null)
		{
			$employee = new Usuario($parametros['nombre'], $parametros['clave'], $parametros['tipo'], $parametros['activo']);
			if (usuarioPDO::InsertarUsuarioBD($employee) != null) {
				$retorno = $response->withJson("Usuario creado", 200);
			}
			else
			{
	        	throw new Exception("Error al insertar en base de datos", 500);
			}
		}	
		else
		{
			$nueva = new stdclass();
	       	$nueva->respuesta = "Parametros incorrectos o faltantes";
	        $retorno = $response->withJson($nueva, 401);
		}
		return $retorno;
	}

	public static function SuspenderUsuario($request, $response, $args)
	{
		$id = $args['id'];
		$respuesta = new stdclass();
		//if(usuarioPDO::BorrarUsuarioBD($id) > 0) 
		if(usuarioPDO::DarDeBajaUsuario($id) > 0)
		{	
			$respuesta->resultado = "Baja exitosa";
		}
		else
		{
			throw new Exception("Ocurrio un error", 500);
		}
		$nueva = $response->withJson($respuesta, 200);
		return $nueva;
	}	

	public static function BajaUsuario($request, $response, $args)
	{
		$id = $args['id'];
		$respuesta = new stdclass();
		if(usuarioPDO::BorrarUsuarioBD($id) > 0) 
		{	
			$respuesta->resultado = "Baja exitosa";
		}
		else
		{
			throw new Exception("Ocurrio un error", 500);
		}
		$nueva = $response->withJson($respuesta, 200);
		return $nueva;
	}	

	public static function ModificarUsuario($request, $response, $args)
	{
		$id = $args['id'];
		$param = $request->getParsedBody();
		if($id != null)
		{
			//traigo usuario
			$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
	        $consulta =$objetoAccesoDato->RetornarConsulta("select * from usuarios where id = :id");
	        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
			$consulta->execute();			
			$employee = $consulta->fetchObject('usuario'); 

			if ($employee != null) {
				//modifico atributos del usuario
				if (isset($param['nombre'])) {
					$employee->nombre = $param['nombre'];
				}
				if (isset($param['tipo'])) {
					$employee->tipo = $param['tipo'];	
				}
				if (isset($param['clave'])) {
					$employee->clave = $param['clave'];
				}
				if (isset($param['activo'])) {
					$employee->activo = $param['activo'];
				}
				//guardo
				$respuesta = new stdclass();
				if(usuarioPDO::ModificarUsuarioBD($employee))
				{
					//$nueva = $response->withJson($employee, 200);
					$respuesta->resultado = "Usuario modificado correctamente";
					$nueva = $response->withJson($respuesta, 200);
				}
				else
				{
					throw new Exception("Error al insertar en base de datos", 500);
				}
			}
			else
			{
				$nueva = new stdclass();
	        	$nueva->respuesta = "No existe el usuario";
	        	$nueva = $response->withJson($nueva, 200);
			}
		}
		else
		{
			$nueva = new stdclass();
	       	$nueva->respuesta = "Se necesita un id";
	        $nueva = $response->withJson($nueva, 200);
		}
		return $nueva;
	}



}


?>