<?php
if($peticionAjax){
    require_once "../modelos/cotizacionModelo.php";
}else{
    require_once "./modelos/cotizacionModelo.php";
}

class cotizacionControlador extends cotizacionModelo{
    public function agregar_cotizacion_controlador(){
        // Validar sesión primero
        $validacion = mainModel::validarSesion();
        if($validacion['error']) {
            return mainModel::showNotification([
                "title" => "Error de sesión",
                "text" => $validacion['mensaje'],
                "type" => "error",
                "funcion" => "window.location.href = '".$validacion['redireccion']."'"
            ]);
        }

        $mainModel = new mainModel();
        $planConfig = $mainModel->getPlanConfiguracionMainModel();
        
        // Solo validar si existe configuración de plan
        if (isset($planConfig['cotizaciones'])) {
            $limiteCotizaciones = (int)$planConfig['cotizaciones'];
            
            // Caso 1: Límite es 0 (sin permisos)
            if ($limiteCotizaciones === 0) {
                return $mainModel->showNotification([
                    "type" => "error",
                    "title" => "Acceso restringido",
                    "text" => "Su plan no incluye la creación de cotizaciones."
                ]);
            }
            
            // Caso 2: Validar disponibilidad
            $totalRegistradas = (int)cotizacionModelo::getTotalCotizacionesRegistradas();
            
            if ($totalRegistradas >= $limiteCotizaciones) {
                return $mainModel->showNotification([
                    "type" => "error",
                    "title" => "Límite alcanzado",
                    "text" => "Ha excedido el límite mensual de cotizaciones (Máximo: $limiteCotizaciones)."
                ]);
            }
        }

        $usuario = $_SESSION['colaborador_id_sd'];
        $empresa_id = $_SESSION['empresa_id_sd'];            
        // ENCABEZADO DE COTIZACIÓN
        $clientes_id = mainModel::cleanString($_POST['cliente_id'] ?? '');
        $colaborador_id = mainModel::cleanString($_POST['colaborador_id'] ?? '');
        $notas = mainModel::cleanStringConverterCase($_POST['notesQuote'] ?? '');
        $fecha = mainModel::cleanString($_POST['fecha'] ?? date('Y-m-d'));
        $fecha_dolar = !empty($_POST['fecha_dolar']) ? $_POST['fecha_dolar'] : date('Y-m-d');
        $fecha_registro = date("Y-m-d H:i:s");
        $estado = 1; // ACTIVO
        $cotizacion_id = mainModel::correlativo("cotizacion_id", "cotizacion");
        $numero = mainModel::correlativo("number", "cotizacion");
        $tipo_factura = 1;
        
        // Validar vigencia con valor por defecto 1 si no se recibe
        $vigencia_cotizacion_id = isset($_POST['vigencia_quote']) && is_numeric($_POST['vigencia_quote']) ?  (int)$_POST['vigencia_quote'] : 0;
    

        $tipo_entrega = mainModel::cleanString($_POST['tipo_entrega'] ?? '');
        
        // Validaciones básicas
        if(empty($clientes_id) || empty($colaborador_id)){
            return mainModel::showNotification([
                "type" => "error",
                "title" => "Error en registros",
                "text" => "El cliente y el vendedor no pueden quedar en blanco, por favor corregir"
            ]);
        }
        
        // VERIFICAR SI HAY PRODUCTOS EN LA TABLA
        $tamano_tabla = 0;
        if(isset($_POST['productNameQuote']) && is_array($_POST['productNameQuote']) && 
           !empty($_POST['productNameQuote']) && isset($_POST['productosQuote_id'][0]) && 
           !empty($_POST['productNameQuote'][0]) && isset($_POST['quantityQuote'][0]) && 
           isset($_POST['priceQuote'][0])) {
            $tamano_tabla = count($_POST['productNameQuote']);
        }
        
        if($tamano_tabla <= 0){
            return mainModel::showNotification([
                "type" => "error",
                "title" => "Error en registros",
                "text" => "No ha seleccionado productos en el detalle de la cotización, debe seleccionar al menos un producto"
            ]);
        }
        
        $datos = [
            "cotizacion_id" => $cotizacion_id,
            "clientes_id" => $clientes_id,                
            "tipo_factura" => $tipo_factura,                
            "numero" => $numero,
            "colaboradores_id" => $colaborador_id,
            "importe" => 0,
            "notas" => $notas,
            "fecha" => $fecha,                
            "estado" => $estado,
            "usuario" => $usuario,
            "fecha_registro" => $fecha_registro,
            "empresa_id" => $empresa_id, // Corregido de "empresa" a "empresa_id"
            "vigencia_cotizacion_id" => $vigencia_cotizacion_id,                    
            "fecha_dolar" => $fecha_dolar,
            "tipo_entrega" => $tipo_entrega
        ];
        
        $query = cotizacionModelo::agregar_cotizacion_modelo($datos);
        
        if($query !== true){
            $error_msg = is_string($query) ? $query : "Error desconocido";
            return mainModel::showNotification([
                "type" => "error",
                "title" => "Error",
                "text" => "No se pudo procesar la solicitud de cotización: ".$error_msg
            ]);
        }
        
        // ALMACENAR DETALLES DE LA COTIZACIÓN
        $total_valor = 0;
        $descuentos = 0;
        $isv_neto = 0;
        $item = count($_POST['productNameQuote']);
        
        for ($i = 0; $i < $item; $i++){
            $productos_id = $_POST['productosQuote_id'][$i] ?? '';
            $productName = $_POST['productNameQuote'][$i] ?? '';
            $quantity = $_POST['quantityQuote'][$i] ?? 0;
            $price = $_POST['priceQuote'][$i] ?? 0;
            $discount = isset($_POST['discountQuote'][$i]) && $_POST['discountQuote'][$i] != "" ? 
                         (float)$_POST['discountQuote'][$i] : 0;
            $total = $_POST['totalQuote'][$i] ?? 0;
            $isv_valor = isset($_POST['valorQuote_isv'][$i]) && $_POST['valorQuote_isv'][$i] != "" ? 
                         (float)$_POST['valorQuote_isv'][$i] : 0;
            
            if(!empty($productos_id) && !empty($productName) && !empty($quantity) && !empty($price)){
                $datos_detalles_cotizacion = [
                    "cotizacion_id" => $cotizacion_id,
                    "productos_id" => $productos_id,
                    "cantidad" => $quantity,                
                    "precio" => $price,
                    "isv_valor" => $isv_valor,
                    "descuento" => $discount,                
                ];
                
                $total_valor += ($price * $quantity);
                $descuentos += $discount;
                $isv_neto += $isv_valor;
                
                $detalle_result = cotizacionModelo::agregar_detalle_cotizacion($datos_detalles_cotizacion);
                
                if(!$detalle_result){
                    return mainModel::showNotification([
                        "type" => "error",
                        "title" => "Error",
                        "text" => "Error al guardar el detalle de la cotización"
                    ]);
                }
            }
        }
        
        $total_despues_isv = ($total_valor + $isv_neto) - $descuentos;
        
        // ACTUALIZAR EL IMPORTE EN LA COTIZACIÓN
        $datos_factura = [
            "cotizacion_id" => $cotizacion_id,
            "importe" => $total_despues_isv        
        ];
    
        $update_result = cotizacionModelo::actualizar_cotizacion_importe($datos_factura);
        
        if(!$update_result){
            return mainModel::showNotification([
                "type" => "error",
                "title" => "Error",
                "text" => "Error al actualizar el importe de la cotización"
            ]);
        }
        
        // Registrar en historial
        mainModel::guardarHistorial([
            "modulo" => 'Cotizaciones',
            "colaboradores_id" => $usuario,
            "status" => "Registro",
            "observacion" => "Se registró la cotización #{$numero}",
            "fecha_registro" => $fecha_registro
        ]);
        
        return mainModel::showNotification([
            "type" => "success",
            "title" => "Registro exitoso",
            "text" => "La cotización se ha registrado correctamente",
            "form" => "quoteForm",
            "funcion" => "limpiarTablaQuote();printQuote(".$cotizacion_id.");mailQuote(".$cotizacion_id.");getConsumidorFinal();getCajero();cleanFooterValueQuote();resetRow();"
        ]);
    }
        
    public function cancelar_cotizacion_controlador(){
        // Validar sesión primero
        $validacion = mainModel::validarSesion();
        if($validacion['error']) {
            return mainModel::showNotification([
                "title" => "Error de sesión",
                "text" => $validacion['mensaje'],
                "type" => "error",
                "funcion" => "window.location.href = '".$validacion['redireccion']."'"
            ]);
        }
        
        $cotizacion_id = mainModel::cleanString($_POST['cotizacion_id'] ?? '');
        
        if(empty($cotizacion_id)){
            return mainModel::showNotification([
                "type" => "error",
                "title" => "Error",
                "text" => "ID de cotización no válido"
            ]);
        }
        
        // Obtener información de la cotización para el historial
        $campos = ['numero'];
        $tabla = "cotizacion";
        $condicion = "cotizacion_id = '".$cotizacion_id."'";
        
        $cotizacion = mainModel::consultar_tabla($tabla, $campos, $condicion);
        $numero = $cotizacion[0]['numero'] ?? 'desconocido';
        
        $cancel_result = cotizacionModelo::cancelar_cotizacion_modelo($cotizacion_id);
        
        if(!$cancel_result){
            return mainModel::showNotification([
                "type" => "error",
                "title" => "Error",
                "text" => "No se pudo cancelar la cotización"
            ]);
        }
        
        // Registrar en historial
        mainModel::guardarHistorial([
            "modulo" => 'Cotizaciones',
            "colaboradores_id" => $_SESSION['colaborador_id_sd'],
            "status" => "Cancelación",
            "observacion" => "Se canceló la cotización #{$numero}",
            "fecha_registro" => date("Y-m-d H:i:s")
        ]);
        
        return mainModel::showNotification([
            "type" => "success",
            "title" => "Cancelación exitosa",
            "text" => "La cotización ha sido cancelada correctamente",
            "funcion" => "listar_cotizaciones();"
        ]);
    }
}