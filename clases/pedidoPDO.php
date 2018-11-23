<?php 

class PedidoPDO
{
	public static function traerPedidos()
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta = $objetoAccesoDato->RetornarConsulta("select * from pedidos");
		$consulta->execute();			
		return $consulta->fetchAll(PDO::FETCH_CLASS, "pedido");
	}

	public static function traerUnPedido($id)
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta = $objetoAccesoDato->RetornarConsulta("select * from pedidos WHERE codigo = '$id'");
		$consulta->execute();			
		return $consulta->fetchAll(PDO::FETCH_CLASS, "pedido");
	}

	public static function InsertarPedido($pedido)
	{
		if(isset($pedido) != null)
		{
			$fecha = date("Y-m-d H:i:s");
			
			//guardo en bd
			$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
	        $consulta =$objetoAccesoDato->RetornarConsulta("INSERT into pedidos (codigo, nombre_cliente, codigo_mesa, estado, fecha) VALUES('$pedido->codigo','$pedido->nombre_cliente', '$pedido->id_mesa', '$pedido->estado', :fecha)");
	        $consulta->bindValue(':fecha', $fecha , PDO::PARAM_STR);
			$consulta->execute();	
			$retorno = $objetoAccesoDato->RetornarUltimoIdInsertado();
			for ($i=0; $i < count($pedido->productos); $i++) { 
				if(PedidoPDO::InsertarPedidoProducto($pedido->codigo, $pedido->productos[$i]) != true)
				{
					/*$nueva = new stdclass();
		        	$nueva->respuesta = "Ocurrio un error al insertar productos pedidos";
		        	$newResponse = json_encode($nueva, 200);
		        	return $newResponse;*/
		        	throw new Exception("Error al insertar en la bd", 500);
		        	
				}
			}
			return $retorno;

		}
		else
		{
			$nueva = new stdclass();
        	$nueva->respuesta = "Ocurrio un error";
        	$newResponse = json_encode($nueva, 200);
        	return $newResponse;
		}
	}

	public static function InsertarPedidoProducto($id_pedido, $producto)
	{
		if(isset($id_pedido) != null && isset($producto) != null)
		{
			$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
	        $consulta =$objetoAccesoDato->RetornarConsulta("INSERT into pedidos_productos (id_pedido, id_producto, cantidad) VALUES('$id_pedido','$producto->id', '$producto->cantidad')");
			$consulta->execute();	               
			//return $objetoAccesoDato->RetornarUltimoIdInsertado();
			return true;
		}
		else
		{
			$nueva = new stdclass();
        	$nueva->respuesta = "Ocurrio un error";
        	$newResponse = json_encode($nueva, 200);
        	return $newResponse;
		}
	}

	public static function ModificarEstadoPedidoBD($id, $estado)
	{
		$fecha = date("Y-m-d H:i:s");
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta = $objetoAccesoDato->RetornarConsulta("
			UPDATE pedidos 
			set estado = '$estado',
			fecha = '$fecha'
			WHERE codigo = '$id'");
		return $consulta->execute();
	}	

	public static function ModificarEstadoFinal($id, $demora)
	{
		$estado = "Listo para servir";
		$hora = date("H:i:s");
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta = $objetoAccesoDato->RetornarConsulta("
			UPDATE pedidos 
			set estado = '$estado',
			demora = '$demora',
			hora_fin = '$hora'
			WHERE codigo = '$id'");
		return $consulta->execute();
	}	

	
}


?>