<?php 

class Empleado
{
	public $nombre;
	public $tipo;
	public $clave;
	public $estado; //activo/suspendido -> true/false

	public function __construct($nom = null, $pass = null, $type = null, $estado = null)
	{
		if(func_num_args() != 0)
		{
			$this->nombre = $nom;
			$this->tipo = $type;
			$this->clave = $pass;
			$this->estado = $estado;
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
          $token= AutentificadorJWT::CrearToken($response); //verificar no crear token con password de usuario
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
		/*var_dump($employee);
		die();*/
        if($employee != NULL)
        {
            return $employee;
        }
        else
        {
            return false;
        }
	}
}


?>