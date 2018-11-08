<?php

require_once "./clases/AutentificadorJWT.php";

class MWparaAutentificar
{
 /**
   * @api {any} /MWparaAutenticar/  Verificar Usuario
   * @apiVersion 0.1.0
   * @apiName VerificarUsuario
   * @apiGroup MIDDLEWARE
   * @apiDescription  Por medio de este MiddleWare verifico las credeciales antes de ingresar al correspondiente metodo 
   *
   * @apiParam {ServerRequestInterface} request  El objeto REQUEST.
 * @apiParam {ResponseInterface} response El objeto RESPONSE.
 * @apiParam {Callable} next  The next middleware callable.
   *
   * @apiExample Como usarlo:
   *    ->add(\MWparaAutenticar::class . ':VerificarUsuario')
   */
	public function VerificarUsuario($request, $response, $next) {
         
		$objDelaRespuesta= new stdclass();
		$objDelaRespuesta->respuesta="";
		
		//tomo el token del header
		$arrayConToken = $request->getHeader('token');
		$token=$arrayConToken[0];		
		
		$objDelaRespuesta->esValido=true; 
		try 
		{
			AutentificadorJWT::verificarToken($token);
			$objDelaRespuesta->esValido=true;      
		}
		catch (Exception $e) {  

			//guardar en un log
			$objDelaRespuesta->excepcion=$e->getMessage();
			$objDelaRespuesta->esValido=false;     
		}

		if($objDelaRespuesta->esValido)
		{						
				$payload=AutentificadorJWT::ObtenerData($token);
				if($payload->tipo == 'socio')
				{
					$response = $next($request, $response);
					return $response;
				}
				else
				{
					$objDelaRespuesta->respuesta = "Sin autorización. No es socio";
					$nueva = $response->withJson($objDelaRespuesta, 401);  
					return $nueva;
				}
		}    
		else
		{
			$objDelaRespuesta->respuesta="Solo usuarios registrados";
			$objDelaRespuesta->elToken=$token;
			return $objDelaRespuesta;
		}  		  
	}
}