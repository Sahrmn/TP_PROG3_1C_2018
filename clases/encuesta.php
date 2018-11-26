<?php 

class Encuesta
{
	public $codigo_pedido;
	public $p_mesa;
	public $p_restaurante;
	public $p_mozo;
	public $p_cocinero;
	public $comentarios;

	public function __construct($pedido = null, $pmesa = null, $presto = null, $pmozo = null, $pcocinero = null, $coments = null)
	{
		if (func_num_args() > 0) {
			$this->codigo_pedido = $pedido;
			$this->p_mesa = $pmesa;
			$this->p_restaurante = $presto;
			$this->p_mozo = $pmozo;
			$this->p_cocinero = $pcocinero;
			$this->comentarios = $coments;
		}
	}

	public static function CargarEncuesta($request, $response, $args)
	{
		if (isset($args['id_pedido']) != null) 
		{
			$id_pedido = $args['id_pedido'];
			$param = $request->getParsedBody();

			if (isset($param['p_mesa']) != null && isset($param['p_restaurante']) != null && isset($param['p_mozo']) != null && isset($param['p_cocinero']) != null && isset($param['comentarios']) != null) 
				{
				$encuesta = new Encuesta($id_pedido, $param['p_mesa'], $param['p_restaurante'], $param['p_mozo'], $param['p_cocinero'], $param['comentarios']);
				
				//guardo
				if (encuestaPDO::Insertar($encuesta) > 0) {
					$nueva->respuesta = "Encuesta guardada.";
        			$newResponse = $response->withJson($nueva, 200);		
				}
				else
				{
					throw new Exception("Ocurrio un error", 500);
				}
			}
			else
			{
				$nueva->respuesta = "Paramatros faltantes o incorrectos.";
        		$newResponse = $response->withJson($nueva, 200);
			}
		}
		else
		{
			$nueva->respuesta = "Id faltante.";
        	$newResponse = $response->withJson($nueva, 200);
		}
		return $newResponse;
	}

}

?>