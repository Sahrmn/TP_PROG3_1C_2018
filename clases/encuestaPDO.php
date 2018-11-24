<?php 

class encuestaPDO
{
	public static function Insertar($encuesta)
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("INSERT into encuestas (codigo_pedido, p_mesa, p_restaurante, p_mozo, p_cocinero, comentarios) VALUES('$encuesta->codigo_pedido', '$encuesta->p_mesa', '$encuesta->p_restaurante', '$encuesta->p_mozo', '$encuesta->p_cocinero', '$encuesta->comentarios')");
		$consulta->execute();	
		return $objetoAccesoDato->RetornarUltimoIdInsertado();
	}
}

?>