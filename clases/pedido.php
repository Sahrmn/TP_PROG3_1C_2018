<?php 

class Pedido
{
	public $codigo;
	public $cliente;
	public $productos;//array asociativo con producto y cantidad
	public $estado;
	public $mesa;
	public $tiempo_preparacion;
	public $tiempo_inicial;
	public $foto; //armar el metodo para guardar la foto 
	public static $cod = 000;

	/*public function __construct($ArrayDeParametros)
	{
		$this->crearCodigo($ArrayDeParametros['mesa']); //creo codigo de pedido a partir del nombre de la mesa
		$this->cliente = $ArrayDeParametros['cliente'];
		$this->mesa = $ArrayDeParametros['mesa'];
		$this->tiempo_inicial = getFecha();
		$this->tiempo_inicial = $this->tiempo_inicial . getHora();
		$this->productos = array();
	}*/

	public function crearCodigo($mesa)
	{
		$this->cod = $this->cod++;
		$this->codigo = substr($mesa, 0, 3); //obtengo las primeras 3 letras del nombre de la mesa
		$this->codigo = $this->codigo . $this->cod; 
	}

	public static function getFecha(){
       $anio = date('Y');
       $mes = date('m');
       $dia = date('d');
       return $dia . '-' . $mes . '-' . $anio;
    } 

    public static function getHora(){
       $hora = date('H');
       $minutos = date('i');
       return '-' . $hora . ':' . $minutos;
    } 

    public static function tomarPedido($ArrayDeParametros)
    {
    	if($ArrayDeParametros['mesa'] != null && $ArrayDeParametros['cliente'] != null)
    	{
    		$mesa = new Mesa();
    		$pedido = new Pedido($ArrayDeParametros);

    	}
    }



}

?>