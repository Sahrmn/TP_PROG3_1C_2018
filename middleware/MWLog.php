<?php 

class MWLog
{
	public static function LogActividades($request, $response, $next)
	{
		$arrayConToken = $request->getHeader('token');
		if($arrayConToken != null)
		{
			$token = $arrayConToken[0];		
			$payload = AutentificadorJWT::ObtenerData($token);
			$user = $payload->nombre;
		}
		else
		{
			//guardar log con usuario desconocido
			$user = "Desconocido";
		}
		$method = $request->getMethod();
		$rute = $request->getUri()->getPath();
		$fecha = date('Y-m-d H:i:s');
		if(MWLog::GuardarEnDB($user, $method, $rute, $fecha) != null)
		{
			//$resp->log = "log creado";
			//$respuesta = $response->withJson($resp, 200);
			$respuesta = $next($request, $response);
		}
		else
		{
			$nueva = new stdclass();
        	$nueva->respuesta = "Ocurrio un error.";
        	$newResponse = json_encode($nueva, 500);			
		}
		return $respuesta;
	}

	public static function GuardarEnDB($user, $method, $rute, $fecha)
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("INSERT into log (usuario, metodo, ruta, fecha)values(:user, :method, :rute, :fecha)");
		$consulta->bindValue(':user', $user, PDO::PARAM_STR);
		$consulta->bindValue(':method', $method, PDO::PARAM_STR);
		$consulta->bindValue(':rute', $rute, PDO::PARAM_STR);
		$consulta->bindValue(':fecha', $fecha, PDO::PARAM_STR);
		$consulta->execute();
		return $objetoAccesoDato->RetornarUltimoIdInsertado();
	}
}


?>