<?php 

class usuarioPDO
{
	public static function InsertarUsuarioBD($employee)
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("INSERT into usuarios (nombre, clave, tipo, activo) VALUES('$employee->nombre','$employee->clave', '$employee->tipo', '$employee->activo')");
		$consulta->execute();	
		return $objetoAccesoDato->RetornarUltimoIdInsertado();
	}

	public static function BorrarUsuarioBD($id)
	{	
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("
			DELETE 
			from usuarios 				
			WHERE id = :id");	
		$consulta->bindValue(':id',$id, PDO::PARAM_INT);		
		$consulta->execute();
		return $consulta->rowCount();
	}

	public static function ModificarUsuarioBD($employee)
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("
			UPDATE usuarios 
			set nombre = '$employee->nombre',
			tipo = '$employee->tipo',
			clave = '$employee->clave',
			activo = '$employee->activo'
			WHERE id = '$employee->id'");
		return $consulta->execute();
	}	

	public static function DarDeBajaUsuario($id)
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("
			UPDATE usuarios 
			set activo = 0
			WHERE id = '$id'");
		return $consulta->execute();
	}	
}


?>