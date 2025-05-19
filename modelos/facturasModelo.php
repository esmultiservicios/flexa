<?php
if($peticionAjax){
    require_once "../core/mainModel.php";
}else{
    require_once "./core/mainModel.php";    
}

class facturasModelo extends mainModel {        
    protected function guardar_facturas_modelo($datos) {
        $check = "SELECT COUNT(*) as count FROM facturas 
                  WHERE facturas_id = '".$datos['facturas_id']."'";
        $result_check = mainModel::connection()->query($check) or die(mainModel::connection()->error);
        $row = $result_check->fetch_assoc();
    
        if ($row['count'] > 0) {
            $query = "UPDATE facturas SET
                        `clientes_id` = '".$datos['clientes_id']."',
                        `secuencia_facturacion_id` = '".$datos['secuencia_facturacion_id']."',
                        `apertura_id` = '".$datos['apertura_id']."',
                        `number` = '".$datos['numero']."',
                        `tipo_factura` = '".$datos['tipo_factura']."',
                        `colaboradores_id` = '".$datos['colaboradores_id']."',
                        `importe` = '".$datos['importe']."',
                        `notas` = '".$datos['notas']."',
                        `fecha` = '".$datos['fecha']."',
                        `estado` = '".$datos['estado']."',
                        `usuario` = '".$datos['usuario']."',
                        `empresa_id` = '".$datos['empresa']."',
                        `fecha_registro` = '".$datos['fecha_registro']."',
                        `fecha_dolar` = '".$datos['fecha_dolar']."'
                    WHERE `facturas_id` = '".$datos['facturas_id']."'";
        } else {
            $query = "INSERT INTO facturas (
                        `facturas_id`, 
                        `clientes_id`, 
                        `secuencia_facturacion_id`, 
                        `apertura_id`, 
                        `number`, 
                        `tipo_factura`, 
                        `colaboradores_id`, 
                        `importe`, 
                        `notas`, 
                        `fecha`, 
                        `estado`, 
                        `usuario`, 
                        `empresa_id`, 
                        `fecha_registro`, 
                        `fecha_dolar`,
						`no_orden`,
						`constancia`,
						`identificativo_sag`,
						`numero_interno`						
                    )
                    VALUES (
                        '".$datos['facturas_id']."',
                        '".$datos['clientes_id']."',
                        '".$datos['secuencia_facturacion_id']."',
                        '".$datos['apertura_id']."',
                        '".$datos['numero']."',
                        '".$datos['tipo_factura']."',
                        '".$datos['colaboradores_id']."',
                        '".$datos['importe']."',
                        '".$datos['notas']."',
                        '".$datos['fecha']."',
                        '".$datos['estado']."',
                        '".$datos['usuario']."',
                        '".$datos['empresa']."',
                        '".$datos['fecha_registro']."',
                        '".$datos['fecha_dolar']."',
						'".$datos['no_orden']."',
						'".$datos['constancia']."',
						'".$datos['identificativo_sag']."',
						'".$datos['numero_interno']."'						
                    )";
        }
    
        $result = mainModel::connection()->query($query) or die(mainModel::connection()->error);
        return $result ? true : false;
    }
    
    protected function agregar_detalle_facturas_modelo($datos) {
        $check = "SELECT COUNT(*) as count FROM facturas_detalles 
                  WHERE facturas_id = '".$datos['facturas_id']."' 
                  AND productos_id = '".$datos['productos_id']."'";
        $result_check = mainModel::connection()->query($check) or die(mainModel::connection()->error);
        $row = $result_check->fetch_assoc();
    
        if ($row['count'] > 0) {
            $update = "UPDATE facturas_detalles SET
                        `cantidad` = '".$datos['cantidad']."',
                        `precio` = '".$datos['precio']."',
                        `isv_valor` = '".$datos['isv_valor']."',
                        `descuento` = '".$datos['descuento']."',
                        `medida` = '".$datos['medida']."'
                    WHERE `facturas_id` = '".$datos['facturas_id']."' 
                    AND `productos_id` = '".$datos['productos_id']."'";
            $result = mainModel::connection()->query($update);
        } else {
            $facturas_detalle_id = mainModel::correlativo("facturas_detalle_id", "facturas_detalles");
            $insert = "INSERT INTO facturas_detalles (
                            `facturas_detalle_id`, 
                            `facturas_id`, 
                            `productos_id`, 
                            `cantidad`, 
                            `precio`, 
                            `isv_valor`, 
                            `descuento`, 
                            `medida`
                        )
                        VALUES (
                            '$facturas_detalle_id',
                            '".$datos['facturas_id']."',
                            '".$datos['productos_id']."',
                            '".$datos['cantidad']."',
                            '".$datos['precio']."',
                            '".$datos['isv_valor']."',
                            '".$datos['descuento']."',
                            '".$datos['medida']."'
                        )";
            $result = mainModel::connection()->query($insert);
        }
    
        return $result ? true : false;
    }    
    
    protected function agregar_cuenta_por_cobrar_clientes($datos){
        $cobrar_clientes_id = mainModel::correlativo("cobrar_clientes_id", "cobrar_clientes");
        $insert = "INSERT INTO cobrar_clientes (
                        `cobrar_clientes_id`, 
                        `clientes_id`, 
                        `facturas_id`, 
                        `fecha`, 
                        `saldo`, 
                        `estado`, 
                        `usuario`, 
                        `empresa_id`, 
                        `fecha_registro`
                    )
                    VALUES (
                        '$cobrar_clientes_id',
                        '".$datos['clientes_id']."',
                        '".$datos['facturas_id']."',
                        '".$datos['fecha']."',
                        '".$datos['saldo']."',
                        '".$datos['estado']."',
                        '".$datos['usuario']."',
                        '".$datos['empresa']."',
                        '".$datos['fecha_registro']."'
                    )";
    
        $result = mainModel::connection()->query($insert) or die(mainModel::connection()->error);
        return $result;                
    }        

    protected function agregar_precio_factura_clientes($datos){
        $precio_factura_id = mainModel::correlativo("precio_factura_id", "precio_factura");
        $insert = "INSERT INTO precio_factura (
                        `precio_factura_id`, 
                        `facturas_id`, 
                        `productos_id`, 
                        `clientes_id`, 
                        `fecha`, 
                        `referencia`, 
                        `precio_anterior`, 
                        `precio_nuevo`, 
                        `fecha_registro`
                    )
                    VALUES (
                        '$precio_factura_id',
                        '".$datos['facturas_id']."',
                        '".$datos['productos_id']."',
                        '".$datos['clientes_id']."',
                        '".$datos['fecha']."',
                        '".$datos['referencia']."',
                        '".$datos['precio_anterior']."',
                        '".$datos['precio_nuevo']."',
                        '".$datos['fecha_registro']."'
                    )";
        
        $result = mainModel::connection()->query($insert) or die(mainModel::connection()->error);
        return $result;                
    } 
    
    protected function agregar_facturas_proforma_modelo($datos){
        $facturas_proforma_id = mainModel::correlativo("facturas_proforma_id", "facturas_proforma");
        $conexion = mainModel::connection();
    
        $insert = "INSERT INTO facturas_proforma (
                        facturas_proforma_id,
                        facturas_id,
                        clientes_id,
                        secuencia_facturacion_id,
                        numero,
                        importe,
                        usuario,
                        empresa_id,
                        estado,
                        fecha_creacion
                    ) VALUES (
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        ?
                    )";
        
        $stmt = $conexion->prepare($insert);
    
        if (!$stmt) {
            die("Error al preparar la consulta: " . $conexion->error);
        }
    
        $stmt->bind_param("iiisisisss", 
            $facturas_proforma_id,
            $datos['facturas_id'],
            $datos['clientes_id'],
            $datos['secuencia_facturacion_id'],
            $datos['numero'],
            $datos['importe'],
            $datos['usuario'],
            $datos['empresa_id'],
            $datos['estado'],
            $datos['fecha_creacion']
        );
    
        $result = $stmt->execute();
    
        if (!$result) {
            die("Error al ejecutar la consulta: " . $stmt->error);
        }
    
        $stmt->close();
        return $result;            
    }

    protected function actualizar_detalle_facturas($datos){
        $update = "UPDATE facturas_detalles
                    SET 
                        cantidad = '".$datos['cantidad']."',
                        precio = '".$datos['precio']."',
                        isv_valor = '".$datos['isv_valor']."',
                        descuento = '".$datos['descuento']."'
                    WHERE facturas_id = '".$datos['facturas_id']."' AND productos_id = '".$datos['productos_id']."'";        
    
        $result = mainModel::connection()->query($update) or die(mainModel::connection()->error);        
        return $result;                    
    }
    
    protected function actualizar_factura_importe($datos){
        $update = "UPDATE facturas
                    SET
                        importe = '".$datos['importe']."'
                    WHERE facturas_id = '".$datos['facturas_id']."'";
    
        $result = mainModel::connection()->query($update) or die(mainModel::connection()->error);
        return $result;                
    }

    protected function actualizar_estado_factura_modelo($facturas_id){
        $update = "UPDATE facturas
            SET
                estado = '2'
            WHERE facturas_id = '$facturas_id'";
        $result = mainModel::connection()->query($update) or die(mainModel::connection()->error);    

        return $result;                
    }            
                        
    protected function actualizar_secuencia_facturacion_modelo($secuencia_facturacion_id, $numero){
        $update = "UPDATE secuencia_facturacion
                    SET
                        siguiente = '$numero'
                    WHERE secuencia_facturacion_id = '$secuencia_facturacion_id'";
        $result = mainModel::connection()->query($update) or die(mainModel::connection()->error);    
    
        return $result;                
    }
    
    protected function cancelar_facturas_modelo($facturas_id){
        $estado = 4; //FACTURA CANCELADA
        $update = "UPDATE facturas
                    SET
                        estado = '$estado'
                    WHERE facturas_id = '$facturas_id'";
        $result = mainModel::connection()->query($update) or die(mainModel::connection()->error);
    
        return $result;            
    }

    public static function bloquear_y_obtener_secuencia_modelo($empresa_id, $documento_id) {
        if(empty($empresa_id)) {
            error_log("Error: empresa_id no definido");
            return false;
        }
    
        $conexion = mainModel::staticConnection();
        
        try {
            $conexion->query("SET innodb_lock_wait_timeout = 5");
            
            $sql = "SELECT * FROM secuencia_facturacion 
                    WHERE empresa_id = ? 
                    AND documento_id = ? 
                    AND activo = 1
                    LIMIT 1
                    FOR UPDATE";
            
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("ii", $empresa_id, $documento_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if($result->num_rows == 0) {
                $stmt->close();
                return false;
            }
            
            $secuencia = $result->fetch_assoc();
            $stmt->close();
            
            return $secuencia;
        } catch (Exception $e) {
            error_log("Error en secuencia facturación: " . $e->getMessage());
            return false;
        }
    }
    
    protected function validDetalleFactura($facturas_id, $productos_id){
        $query = "SELECT facturas_id
                FROM facturas_detalles
                WHERE facturas_id = '$facturas_id' AND productos_id  = '$productos_id'";
        
        $result = mainModel::connection()->query($query) or die(mainModel::connection()->error);
        
        return $result;            
    }

    protected function validar_cobrarClientes_modelo($facturas_id){
        $query = "SELECT cobrar_clientes_id
                FROM cobrar_clientes
                WHERE facturas_id = '$facturas_id'";
        
        $result = mainModel::connection()->query($query) or die(mainModel::connection()->error);
        
        return $result;            
    }        
    
    protected function valid_precio_factura_modelo($datos){
        $query = "SELECT precio_factura_id
                    FROM precio_factura
                    WHERE facturas_id = '".$datos['facturas_id']."'";
        
        $result = mainModel::connection()->query($query) or die(mainModel::connection()->error);
        
        return $result;                
    }    

    protected function cantidad_producto_modelo($productos_id){
        $result = mainModel::getCantidadProductos($productos_id);
        
        return $result;            
    }    
	
	protected function getAperturaIDModelo($datos){
		$query = "SELECT apertura_id
				  FROM apertura
				  WHERE colaboradores_id = '".$datos['colaboradores_id']."' 
				  AND fecha = '".$datos['fecha']."' 
				  AND estado = '".$datos['estado']."'";            
		
		$result = mainModel::connection()->query($query) or die(mainModel::connection()->error);
		
		return $result;            
	}

    protected function total_hijos_segun_padre_modelo($productos_id){
        $result = mainModel::getTotalHijosporPadre($productos_id);
        
        return $result;            
    }
    
    protected function registrar_salida_lote_modelo($datos) {
        $mysqli = mainModel::connection();
        
        // Verificar si existe un lote activo para el producto
        $checkLoteQuery = $mysqli->prepare("SELECT lote_id, cantidad FROM lotes 
                                            WHERE productos_id = ? AND estado = 'Activo' 
                                            ORDER BY fecha_vencimiento ASC LIMIT 1");
        $checkLoteQuery->bind_param("i", $datos['productos_id']);
        $checkLoteQuery->execute();
        $resultLote = $checkLoteQuery->get_result();
    
        if ($resultLote->num_rows > 0) {
            $lote = $resultLote->fetch_assoc();
            $lote_id = $lote['lote_id'];
            $saldo = $lote['cantidad'];
        } else {
            $resultSaldo = $this->getSaldoProductosMovimientos($datos['productos_id']);

            if ($resultSaldo->num_rows > 0) {
                $consulta = $resultSaldo->fetch_assoc();
                $saldo = $consulta['saldo'];
            } else {
                $saldo = 0;
            }

            $nuevoSaldo = $saldo + $datos['cantidad'];
            $lote_id = 0;
        }
    
        if ($saldo >= $datos['cantidad']) {
            $cantidad_salida = $datos['cantidad'];
            $nuevo_saldo = $saldo - $datos['cantidad'];
    
            $insertMovimiento = "INSERT INTO movimientos (productos_id, cantidad_entrada, cantidad_salida, saldo, empresa_id, fecha_registro, almacen_id, lote_id, clientes_id, documento, comentario) 
                                 VALUES (?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?)";
    
            $cantidadEntrada = 0;

            $stmtMovimiento = $mysqli->prepare($insertMovimiento);
            $stmtMovimiento->bind_param("iiiiiiiiss", 
                $datos['productos_id'], 
                $cantidadEntrada,
                $cantidad_salida, 
                $nuevo_saldo, 
                $datos['empresa_id'], 
                $datos['almacen_id'], 
                $lote_id,
                $datos['clientes_id'],
                $datos['documento'],
                $datos['comentario']
            );
    
            if ($stmtMovimiento->execute()) {
                $movimientos_id = $mysqli->insert_id;
    
                if ($lote_id > 0) {
                    $updateLote = $mysqli->prepare("UPDATE lotes SET cantidad = ? WHERE lote_id = ?");
                    $updateLote->bind_param("ii", $nuevo_saldo, $lote_id);
                    $updateLote->execute();
    
                    if ($nuevo_saldo == 0) {
                        $updateEstadoLote = $mysqli->prepare("UPDATE lotes SET estado = 'Inactivo' WHERE lote_id = ?");
                        $updateEstadoLote->bind_param("i", $lote_id);
                        $updateEstadoLote->execute();
                    }
                }
    
                return ["status" => "success", "message" => "Movimiento registrado con éxito", "movimientos_id" => $movimientos_id];
            } else {
                return ["status" => "error", "message" => "Error al registrar el movimiento: " . $stmtMovimiento->error];
            }
        } else {
            return ["status" => "error", "message" => "Saldo insuficiente para la salida"];
        }
    }
    
    public function getTipoProducto($productos_id) {
        $result = mainModel::getTipoProducto($productos_id);        
        if($result->num_rows > 0) {
            $consulta = $result->fetch_assoc();
            return $consulta["tipo_producto"];
        }
        return "";
    }
    
    public function getSaldoProductosMovimientos($productos_id) {
        return mainModel::getSaldoProductosMovimientos($productos_id);
    }
}