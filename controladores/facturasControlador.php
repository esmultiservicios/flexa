<?php
if($peticionAjax){
    require_once "../modelos/facturasModelo.php";
}else{
    require_once "./modelos/facturasModelo.php";
}

class facturasControlador extends facturasModelo {
    public function agregar_facturas_controlador() {
        if(!isset($_SESSION['user_sd'])){ 
            session_start(['name'=>'SD']); 
        }
        
        $usuario = $_SESSION['colaborador_id_sd'];
        $empresa_id = $_SESSION['empresa_id_sd'];        
        $clientes_id = $_POST['cliente_id'];
        $colaborador_id = $_POST['colaborador_id'];        
        $tipo_factura = $_POST['facturas_activo'] ?? 2; //1. CONTADO, 2. CREDITO
        $tipo_documento = $_POST['facturas_proforma'] ?? 0; //0. FACTURA ELECTRONICA, 1. FACTURA PROFORMA

		//DATOS DE EXONERACION
		$no_orden = $_POST['no-orden'];
		$constancia = $_POST['constancia'];
		$identificativo_sag = $_POST['identificativo-sag'];
		$numero_interno = $_POST['numero-interno'];

        $documento_id = "1";
        $documento_nombre = "Factura Electronica";

        if($tipo_documento === "1"){
            $documento_id = "4";
            $documento_nombre = "Factura Proforma";
        }        

        // Obtener número de factura con manejo de concurrencia
        $resultado_secuencia = $this->obtenerNumeroFactura($empresa_id, $documento_id);
        
        if($resultado_secuencia['error']) {
            return mainModel::sweetAlert([
                "alert" => "simple",
                "title" => "Error",
                "text" => $resultado_secuencia['mensaje'],
                "type" => "error",
                "btn-class" => "btn-danger",
            ]);
        }

        $secuencia_data = $resultado_secuencia['data'];
        $secuencia_facturacion_id = $secuencia_data['secuencia_facturacion_id'];
        $numero = $secuencia_data['numero'];
        $incremento = $secuencia_data['incremento'];
        $conexion = $secuencia_data['conexion'];

        $notas = mainModel::cleanString($_POST['notesBill']);
        $fecha = $_POST['fecha'];
        $fecha_dolar = $_POST['fecha_dolar'];
        $fecha_registro = date("Y-m-d H:i:s");
        $fac_guardada = false;

        if (isset($_POST['facturas_id']) && $_POST['facturas_id'] != "") {
            $facturas_id = $_POST['facturas_id'];
            $fac_guardada = true;
        } else {
            $facturas_id = mainModel::correlativo("facturas_id", "facturas");
        }                

        $estado = 2; // Estado por defecto para facturas
        
        // Obtener apertura de caja
        $apertura = $this->obtenerAperturaID($usuario, $fecha);
        if(!$apertura) {
            return mainModel::sweetAlert([
                "alert" => "simple",
                "title" => "Error",
                "text" => "No se encontró una apertura de caja activa para esta fecha",
                "type" => "error",
                "btn-class" => "btn-danger",
            ]);
        }
        $apertura_id = $apertura['apertura_id'];

        // Validaciones básicas
        if(empty($clientes_id) || empty($colaborador_id)) {
            return mainModel::sweetAlert([
                "alert" => "simple",
                "title" => "Error Registros en Blanco",
                "text" => "El cliente y el vendedor no pueden quedar en blanco",
                "type" => "error",
                "btn-class" => "btn-danger",
            ]);
        }

        // Validar productos en el detalle
        if(empty($_POST['productName'])) {
            return mainModel::sweetAlert([
                "alert" => "simple",
                "title" => "Error Registros en Blanco",
                "text" => "Debe seleccionar al menos un producto para la factura",
                "type" => "error",
                "btn-class" => "btn-danger",
            ]);
        }

        try {
            $conexion->begin_transaction();

            // Guardar encabezado de factura
            $datos_factura = [
                "facturas_id" => $facturas_id,
                "clientes_id" => $clientes_id,
                "secuencia_facturacion_id" => $secuencia_facturacion_id,
                "apertura_id" => $apertura_id,                
                "tipo_factura" => $tipo_factura,                
                "numero" => $numero,
                "colaboradores_id" => $colaborador_id,
                "importe" => 0,
                "notas" => $notas,
                "fecha" => $fecha,                
                "estado" => $estado,
                "usuario" => $usuario,
                "fecha_registro" => $fecha_registro,
                "empresa" => $empresa_id,
                "fecha_dolar" => $fecha_dolar,
				"no_orden" => $no_orden,
				"constancia" => $constancia,
				"identificativo_sag" => $identificativo_sag,
				"numero_interno" => $numero_interno
            ];
            
            $guardar_factura = $this->guardar_facturas_modelo($datos_factura);
            
            if(!$guardar_factura) {
                throw new Exception("Error al guardar la factura");
            }

            // Procesar detalles de la factura
            $total_valor = 0;
            $descuentos = 0;
            $isv_neto = 0;
            
            foreach($_POST['productName'] as $i => $productName) {
                if(empty($_POST['productos_id'][$i])) continue;
                
                $productos_id = $_POST['productos_id'][$i];
                $quantity = $_POST['quantity'][$i];
                $medida = $_POST['medida'][$i];
                $price = $_POST['price'][$i];
                $price_anterior = $_POST['precio_real'][$i];
                $bodega = $_POST['bodega'][$i];
                $referenciaProducto = $_POST['referenciaProducto'][$i] ?? '';
                $discount = $_POST['discount'][$i] ?? 0;
                $isv_valor = $_POST['valor_isv'][$i] ?? 0;
                $total = $_POST['total'][$i];

                // Guardar detalle de factura
                $datos_detalle = [
                    "facturas_id" => $facturas_id,
                    "productos_id" => $productos_id,
                    "cantidad" => $quantity,                
                    "precio" => $price,
                    "isv_valor" => $isv_valor,
                    "descuento" => $discount,
                    "medida" => $medida,    
                ];
                
                $this->agregar_detalle_facturas_modelo($datos_detalle);

                // Actualizar totales
                $total_valor += ($price * $quantity);
                $descuentos += $discount;
                $isv_neto += $isv_valor;

                // Procesar salida de inventario si es producto
                $this->procesar_salida_inventario($productos_id, $quantity, $medida, $bodega, $clientes_id, $empresa_id, $facturas_id, $i);
                
                // Registrar cambio de precio si aplica
                if(!empty($referenciaProducto)) {
                    $this->registrar_cambio_precio($facturas_id, $productos_id, $clientes_id, $fecha, $referenciaProducto, $price_anterior, $price, $fecha_registro);
                }
            }

            $total_despues_isv = ($total_valor + $isv_neto) - $descuentos;
            
            // Actualizar importe total de la factura
            $this->actualizar_factura_importe([
                "facturas_id" => $facturas_id,
                "importe" => $total_despues_isv        
            ]);

            // Registrar cuenta por cobrar
            $this->registrar_cuenta_cobrar($clientes_id, $facturas_id, $fecha, $total_despues_isv, 
                                          $tipo_factura == 1 ? 3 : 1, $usuario, $empresa_id, $fecha_registro);

            // Si es proforma, guardar registro adicional
            if($documento_nombre === "Factura Proforma") {
                $this->guardar_factura_proforma($facturas_id, $clientes_id, $secuencia_facturacion_id, 
                                              $numero, $total_despues_isv, $colaborador_id, $empresa_id, $fecha_registro);
            }

            // Actualizar secuencia de facturación
            $this->actualizar_secuencia_facturacion_modelo($secuencia_facturacion_id, $numero + $incremento);

            // Registrar en historial
            $this->registrar_historial_factura($clientes_id, $numero, $tipo_factura);

            $conexion->commit();

            // Respuesta exitosa
            $alert = [
                "alert" => "save_simple",
                "title" => "Registro almacenado",
                "text" => "El registro se ha almacenado correctamente",
                "type" => "success",
                "btn-class" => "btn-primary",
                "btn-text" => "¡Bien Hecho!",
                "form" => "invoice-form",    
                "id" => "proceso_factura",
                "valor" => "Registro",
                "funcion" => $documento_nombre === "Factura Proforma" ? 
                    "limpiarTablaFactura();getCajero();printBill(".$facturas_id.");getConsumidorFinal();getEstadoFactura();cleanFooterValueBill();resetRow();" :
                    "limpiarTablaFactura();pago(".$facturas_id.");getCajero();getConsumidorFinal();getEstadoFactura();cleanFooterValueBill();resetRow();",
                "modal" => "",
            ];

            return mainModel::sweetAlert($alert);

        } catch (Exception $e) {
            $conexion->rollback();
            error_log("Error al procesar factura: " . $e->getMessage());
            
            return mainModel::sweetAlert([
                "alert" => "simple",
                "title" => "Ocurrio un error inesperado",
                "text" => "No hemos podido procesar su solicitud: " . $e->getMessage(),
                "type" => "error",
                "btn-class" => "btn-danger",                    
            ]);
        }
    }

    // Métodos auxiliares reutilizables
    
	public function obtenerAperturaID($colaborador_id, $fecha) {
		$datos_apertura = [
			"colaboradores_id" => $colaborador_id,
			"fecha" => $fecha,
			"estado" => 1,
		];
		return $this->getAperturaIDModelo($datos_apertura)->fetch_assoc();
	}
    
    protected function procesar_salida_inventario($productos_id, $quantity, $medida, $bodega, $clientes_id, $empresa_id, $facturas_id, $indice) {
        $tipo_producto = $this->getTipoProducto($productos_id);
        
        if($tipo_producto == "Producto") {
            $documento = "Factura ".$facturas_id."_".$indice;
            
            $datos_salida = [
                "productos_id" => $productos_id,
                "empresa" => $empresa_id,
                "clientes_id" => $clientes_id ?: 0,
                "comentario" => "Salida de inventario por venta",
                "almacen_id" => $bodega ?: 0,
                "cantidad" => $quantity,
                "empresa_id" => $empresa_id,
                "documento" => $documento
            ];
            
            $this->registrar_salida_lote_modelo($datos_salida);
            
            // Procesar productos padre/hijo si aplica
            $this->procesar_relacion_productos($productos_id, $quantity, $medida, $bodega, $clientes_id, $empresa_id, $facturas_id, $indice);
        }
    }
    
    protected function procesar_relacion_productos($productos_id, $quantity, $medida, $bodega, $clientes_id, $empresa_id, $facturas_id, $indice) {
        $producto_padre = $this->cantidad_producto_modelo($productos_id)->fetch_assoc();
        $producto_padre_id = $producto_padre['id_producto_superior'];
        $medidaName = strtolower($medida);
        
        if($producto_padre_id == 0) {
            // Es producto padre, buscar hijos
            $hijos = $this->total_hijos_segun_padre_modelo($productos_id);
            while($hijo = $hijos->fetch_assoc()) {
                $cantidad_convertida = $this->convertir_cantidad($quantity, $medidaName, true);
                $this->registrar_salida_hijo($hijo['productos_id'], $cantidad_convertida, $bodega, $clientes_id, $empresa_id, $facturas_id, $indice);
            }
        } else {
            // Es producto hijo, buscar padre
            $padre = $this->cantidad_producto_modelo($productos_id);
            while($p = $padre->fetch_assoc()) {
                $cantidad_convertida = $this->convertir_cantidad($quantity, $medidaName, false);
                $this->registrar_salida_hijo($p['id_producto_superior'], $cantidad_convertida, $bodega, $clientes_id, $empresa_id, $facturas_id, $indice);
            }
        }
    }
    
    protected function convertir_cantidad($quantity, $medidaName, $esPadre) {
        if($medidaName == "ton" && $esPadre) {
            return $quantity * 2204.623;
        } elseif($medidaName == "lbs" && $esPadre) {
            return $quantity / 2204.623;
        }
        return $quantity;
    }
    
    protected function registrar_salida_hijo($producto_id, $quantity, $bodega, $clientes_id, $empresa_id, $facturas_id, $indice) {
        $documento = "Factura ".$facturas_id."_".$indice;
        
        $datos = [
            "productos_id" => $producto_id,
            "empresa" => $empresa_id,
            "clientes_id" => $clientes_id ?: 0,
            "comentario" => "Salida de inventario por venta",
            "almacen_id" => $bodega ?: 0,
            "cantidad" => $quantity,
            "empresa_id" => $empresa_id,
            "documento" => $documento
        ];
        
        $this->registrar_salida_lote_modelo($datos);
    }
    
    protected function registrar_cambio_precio($facturas_id, $productos_id, $clientes_id, $fecha, $referencia, $precio_anterior, $precio_nuevo, $fecha_registro) {
        $datos_precio = [
            "facturas_id" => $facturas_id,
            "productos_id" => $productos_id,
            "clientes_id" => $clientes_id,                
            "fecha" => $fecha,
            "referencia" => $referencia,
            "precio_anterior" => $precio_anterior,
            "precio_nuevo" => $precio_nuevo,                                            
            "fecha_registro" => $fecha_registro                                            
        ];
        
        if($this->valid_precio_factura_modelo($datos_precio)->num_rows == 0) {
            $this->agregar_precio_factura_clientes($datos_precio);
        }
    }
    
    protected function registrar_cuenta_cobrar($clientes_id, $facturas_id, $fecha, $saldo, $estado, $usuario, $empresa_id, $fecha_registro) {
        if($this->validar_cobrarClientes_modelo($facturas_id)->num_rows == 0) {
            $datos_cobro = [
                "clientes_id" => $clientes_id,
                "facturas_id" => $facturas_id,
                "fecha" => $fecha,                
                "saldo" => $saldo,
                "estado" => $estado,
                "usuario" => $usuario,
                "fecha_registro" => $fecha_registro,
                "empresa" => $empresa_id
            ];
            
            $this->agregar_cuenta_por_cobrar_clientes($datos_cobro);
        }
    }
    
    protected function guardar_factura_proforma($facturas_id, $clientes_id, $secuencia_facturacion_id, $numero, $importe, $usuario, $empresa_id, $fecha_registro) {
        $datos_proforma = [
            "facturas_id" => $facturas_id,
            "clientes_id" => $clientes_id,
            "secuencia_facturacion_id" => $secuencia_facturacion_id,                
            "numero" => $numero,                                    
            "importe" => $importe,    
            "usuario" => $usuario,
            "empresa_id" => $empresa_id,    
            "estado" => 0,
            "fecha_creacion" => $fecha_registro
        ];
        
        $this->agregar_facturas_proforma_modelo($datos_proforma);
        $this->actualizar_estado_factura_modelo($facturas_id);
    }
    
    protected function registrar_historial_factura($clientes_id, $numero_factura, $tipo_factura) {
        $cliente = mainModel::consultar_tabla('clientes', ['nombre', 'rtn'], "clientes_id = {$clientes_id}")[0];
        $nombre = $cliente['nombre'] ?? '';
        $rtn = $cliente['rtn'] ?? '';
        
        $tipo = $tipo_factura == 1 ? "al contado" : "al crédito";
        
        $datos_historial = [
            "modulo" => 'Facturas',
            "colaboradores_id" => $_SESSION['colaborador_id_sd'],        
            "status" => "Registro",
            "observacion" => "Se registro la factura {$numero_factura} {$tipo} para el cliente {$nombre} con el RTN {$rtn}",
            "fecha_registro" => date("Y-m-d H:i:s")
        ];
        
        mainModel::guardarHistorial($datos_historial);
    }
    
    protected function obtenerNumeroFactura($empresa_id, $documento_id) {
        $conexion = mainModel::connection();
        $conexion->begin_transaction();
        
        try {
            // Obtener y bloquear la secuencia
            $secuenciaData = facturasModelo::bloquear_y_obtener_secuencia_modelo($empresa_id, $documento_id);
            
            if(!$secuenciaData) {
                $conexion->rollback();
                return [
                    'error' => true,
                    'mensaje' => 'No se encontró una secuencia de facturación activa'
                ];
            }
            
            // Verificar rango final
            $siguiente_numero = $secuenciaData['siguiente'] + $secuenciaData['incremento'];
            if($siguiente_numero > $secuenciaData['rango_final']) {
                $conexion->rollback();
                return [
                    'error' => true,
                    'mensaje' => 'Se ha alcanzado el límite del rango autorizado de facturación'
                ];
            }
            
            // Si todo está bien, confirmar la transacción
            $conexion->commit();
            
            return [
                'error' => false,
                'data' => [
                    'secuencia_facturacion_id' => $secuenciaData['secuencia_facturacion_id'],
                    'numero' => $secuenciaData['siguiente'],
                    'incremento' => $secuenciaData['incremento'],
                    'prefijo' => $secuenciaData['prefijo'],
                    'relleno' => $secuenciaData['relleno'],
                    'rango_final' => $secuenciaData['rango_final'],
                    'conexion' => $conexion
                ]
            ];
        } catch (Exception $e) {
            $conexion->rollback();
            error_log("Error al obtener número de factura: " . $e->getMessage());
            return [
                'error' => true,
                'mensaje' => 'Error al generar el número de factura'
            ];
        }
    }
    
    public function agregar_facturas_open_controlador() {
        // Similar a agregar_facturas_controlador pero con estado diferente
        // Implementación similar pero con estado de borrador
    }
    
    public function cancelar_facturas_controlador() {
        $facturas_id = $_POST['facturas_id'];        
        $factura = mainModel::consultar_tabla('facturas', ['number'], "facturas_id = {$facturas_id}")[0];
        $numero_factura = $factura['number'] ?? '';
        
        $query = $this->cancelar_facturas_modelo($facturas_id);
        
        if($query) {
            // Registrar en historial
            $datos = [
                "modulo" => 'Facturas',
                "colaboradores_id" => $_SESSION['colaborador_id_sd'],        
                "status" => "Cancelar",
                "observacion" => "Se cancelo la factura {$numero_factura}",
                "fecha_registro" => date("Y-m-d H:i:s")
            ];    
            
            mainModel::guardarHistorial($datos);

            $alert = [
                "alert" => "clear",
                "title" => "Registro eliminado",
                "text" => "El registro se ha eliminado correctamente",
                "type" => "success",
                "btn-class" => "btn-primary",
                "btn-text" => "¡Bien Hecho!",
                "form" => "",    
                "id" => "",
                "valor" => "Cancelar",
                "funcion" => "",
                "modal" => "",
            ];                
        } else {
            $alert = [
                "alert" => "simple",
                "title" => "Ocurrio un error inesperado",
                "text" => "No hemos podido procesar su solicitud",
                "type" => "error",
                "btn-class" => "btn-danger",                    
            ];                    
        }
        
        return mainModel::sweetAlert($alert);            
    }
}