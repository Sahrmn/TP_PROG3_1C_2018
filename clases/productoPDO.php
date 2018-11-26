<?php 

class productoPDO
{
	public static function traerProductos()
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta = $objetoAccesoDato->RetornarConsulta("select * from productos");
		$consulta->execute();			
		return $consulta->fetchAll(PDO::FETCH_CLASS, "producto");
	}

	public static function traerUno($id)
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta = $objetoAccesoDato->RetornarConsulta("select * from productos WHERE id = '$id'");
		$consulta->execute();			
		return $consulta->fetchAll(PDO::FETCH_CLASS, "producto");
	}

	public static function eliminar($id)
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
		$consulta = $objetoAccesoDato->RetornarConsulta("DELETE from productos WHERE id = '$id'");
		$consulta->execute();
		return $consulta->rowCount();
	}

	public static function modificar($producto)
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta = $objetoAccesoDato->RetornarConsulta("
			UPDATE productos 
			SET nombre = '$producto->nombre',
			precio = '$producto->precio',
			precio_compra = '$producto->precio_compra',
			atendido_por = '$producto->atendido_por'
			WHERE id = '$producto->id'");
		return $consulta->execute();
	}


}



?>