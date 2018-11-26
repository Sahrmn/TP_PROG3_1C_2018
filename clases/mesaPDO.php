<?php 

class mesaPDO
{
	public static function InsertarMesaBD($mesa)
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("INSERT into mesas (codigo, nombre) VALUES('$mesa->codigo', '$mesa->nombre')");
		$consulta->execute();	
		return $objetoAccesoDato->RetornarUltimoIdInsertado();
	}

	public static function traerMesas()
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("select * from mesas");
		$consulta->execute();			
		return $consulta->fetchAll(PDO::FETCH_CLASS, "mesa");	
	}

	public static function BorrarMesaBD($id)
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("
			DELETE 
			from mesas 				
			WHERE codigo = :id");	
		$consulta->bindValue(':id',$id, PDO::PARAM_STR);		
		$consulta->execute();
		
		return $consulta->rowCount();
	}

	public static function ModificarMesaBD($mesa)
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("
			UPDATE mesas 
			set nombre = '$mesa->nombre'
			WHERE codigo = '$mesa->codigo'");
		return $consulta->execute();
	}

	public static function ModificarEstado($id, $estado)
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("
			UPDATE mesas 
			set estado = '$estado'
			WHERE codigo = '$id'");
		return $consulta->execute();
	}
}


?>