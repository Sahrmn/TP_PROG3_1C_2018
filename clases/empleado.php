<?php 

class Empleado
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
        $employee = new Empleado($datos['nombre'], $datos['clave']);
        $response = $employee->VerificarUsuario(); 
        if($response == false)
        {
          throw new Exception("El empleado no existe.");
        }
        else
        {  
          $token= AutentificadorJWT::CrearToken($response); 
          $newResponse = json_encode($token, 200); 
          return $newResponse;
        }
	}

	public function VerificarUsuario()
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta =$objetoAccesoDato->RetornarConsulta("select id, nombre, tipo, activo from empleados where nombre = :nombreEmpleado AND clave = :clave");
        $consulta->bindValue(':nombreEmpleado', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $this->clave, PDO::PARAM_STR);
		$consulta->execute();			
		$employee = $consulta->fetchObject('empleado'); //nombre de la clase
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
		$nom = $parametros['nombre'];
		$clave = $parametros['clave'];
		$tipo = $parametros['tipo'];
		$activo = $parametros['activo'];

		if($nom != null && $clave != null && $tipo != null && $activo != null)
		{
			$employee = new Empleado($nom, $clave, $tipo, $activo);
			if (Empleado::InsertarEmpleadoBD($employee) != null) {
				$retorno = $response->withJson("Usuario creado", 200);
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

	public static function InsertarEmpleadoBD($employee)
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("INSERT into empleados (nombre, clave, tipo, activo) VALUES('$employee->nombre','$employee->clave', '$employee->tipo', '$employee->activo')");
		$consulta->execute();	
		return $objetoAccesoDato->RetornarUltimoIdInsertado();
	}

	public static function BorrarUsuarioBD($id)
	{	
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("
			DELETE 
			from empleados 				
			WHERE id = :id");	
		$consulta->bindValue(':id',$id, PDO::PARAM_INT);		
		$consulta->execute();
		return $consulta->rowCount();
	}

	public static function BajaUsuario($request, $response, $args)
	{
		$id = $args['id'];
		$respuesta = new stdclass();
		if(Empleado::BorrarUsuarioBD($id) > 0) 
		{	
			$respuesta->resultado = "Baja exitosa";
		}
		else
		{
			$respuesta->resultado = "Ocurrio un error al realizar la baja de usuario";	
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
	        $consulta =$objetoAccesoDato->RetornarConsulta("select * from empleados where id = :id");
	        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
			$consulta->execute();			
			$employee = $consulta->fetchObject('empleado'); 

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
				if(Empleado::ModificarUsuarioBD($employee))
				{
					$nueva = $response->withJson($employee, 200);
				}
				else
				{
					$respuesta->resultado = "No se pudo guardar el usuario";
					$nueva = $response->withJson($respuesta, 200);
				}
			}
			else
			{
				throw new Exception("No existe el usuario", 500);
				
			}
		}
		else
		{
			throw new Exception("Se necesita un id");
			
		}
		return $nueva;
	}

	public static function ModificarUsuarioBD($employee)
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("
			UPDATE empleados 
			set nombre = '$employee->nombre',
			tipo = '$employee->tipo',
			clave = '$employee->clave',
			activo = '$employee->activo'
			WHERE id = '$employee->id'");
		return $consulta->execute();
	}	
}


?>