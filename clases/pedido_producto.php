<?php 

class pedido_producto 
{
	public $id_pedido;
	public $id_producto;
	public $cantidad;
	public $estado; //1 si esta tomado por un empleado/ 0 si nadie lo tomo todavia/ 2 producto listo para servir

	public static function traerPedidosProductos()
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta = $objetoAccesoDato->RetornarConsulta("select * from pedidos_productos");
		$consulta->execute();			
		return $consulta->fetchAll(PDO::FETCH_CLASS, "pedido_producto");
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

	public static function ModificarPedidoProducto($id_pedido, $id_producto, $estado, $tiempo)
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("
			UPDATE pedidos_productos 
			set estado = '$estado',
			tiempo_preparacion = '$tiempo'
			WHERE id_pedido = '$id_pedido' AND id_producto = '$id_producto'");
		return $consulta->execute();
	}

	public static function ModificarPedidoProductoEstado($id_pedido, $id_producto, $estado)
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("
			UPDATE pedidos_productos 
			set estado = '$estado'
			WHERE id_pedido = '$id_pedido' AND id_producto = '$id_producto'");
		return $consulta->execute();
	}

}



?>