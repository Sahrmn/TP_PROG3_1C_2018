<?php 

class Encuesta
{
	public $codigo_pedido;
	public $p_mesa;
	public $p_restaurante;
	public $p_mozo;
	public $p_cocinero;
	public $comentarios;

	public function __construct($pedido, $pmesa, $presto, $pmozo, $pcocinero, $coments)
	{
		$this->pedido = $pedido;
		$this->p_mesa = $pmesa;
		$this->p_restaurante = $presto;
		$this->p_mozo = $pmozo;
		$this->p_cocinero = $pcocinero;
		$this->comentarios = $coments;
	}

}

?>