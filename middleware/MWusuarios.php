<?php

require_once "./clases/AutentificadorJWT.php";

class MWusuarios
{
	//solo ingresan socios
	public function AccesoSocio($request, $response, $next) {
         
		$objDelaRespuesta= new stdclass();
		$objDelaRespuesta->respuesta = "";
		
		//tomo el token del header
		$arrayConToken = $request->getHeader('token');
		$token = $arrayConToken[0];		
		
		$objDelaRespuesta->esValido = true; 
		try 
		{
			AutentificadorJWT::verificarToken($token);
			$objDelaRespuesta->esValido = true;      
		}
		catch (Exception $e) {  

			//guardar en un log
			$objDelaRespuesta->excepcion = $e->getMessage();
			$objDelaRespuesta->esValido = false;     
		}

		if($objDelaRespuesta->esValido)
		{						
				$payload = AutentificadorJWT::ObtenerData($token);
				if($payload->tipo == 'socio')
				{
					$nueva = $next($request, $response);
				}
				else
				{
					$objDelaRespuesta->respuesta = "Sin autorización. No es socio";
					$nueva = $response->withJson($objDelaRespuesta, 401);  
				}
		}    
		else
		{
			$objDelaRespuesta->respuesta = "Solo usuarios registrados";
			$objDelaRespuesta->elToken = $token;
			$nueva = $response->withJson($objDelaRespuesta, 401);
		}  		  
		return $nueva;
	}

	public function AccesoUsuarioRegistrado($request, $response, $next) {
         
		$objDelaRespuesta= new stdclass();
		$objDelaRespuesta->respuesta = "";
		
		//tomo el token del header
		if($request->getHeader('token') != null)
		{		
			$nueva = $next($request, $response);
		}
		else
		{
			//solo si se quiere logear, pasa
			if($request->isPost() && $request->getUri()->getPath() == 'login/')
			{
				$nueva = $next($request, $response);		
			}
			//si es un usuario no registrado (cliente) y quiere ver el estado de su pedido -> pasa
			else if($request->isGet())// && $request->getUri()->getPath() == '/pedido/ver/')
			{
				$path = $request->getUri()->getPath();
				$path = explode('PD', $path);
				
				if ($path[0] == 'pedido/ver/') {
					$nueva = $next($request, $response);		
				}
			}
			else
			{
				$objDelaRespuesta->respuesta = "Solo usuarios registrados";
				$nueva = $response->withJson($objDelaRespuesta, 401);
			}
		}
		return $nueva;
	}
}