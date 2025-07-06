<?php
if($peticionAjax){
    require_once "../core/mainModel.php";
}else{
    require_once "./core/mainModel.php";    
}

class cotizacionModelo extends mainModel{        
    protected function agregar_cotizacion_modelo($datos){
        $conexion = mainModel::connection();
        $resultado = ['success' => false, 'message' => ''];
        
        try {
            $conexion->autocommit(false);
            
            // Validar campos requeridos
            if(!isset($datos['vigencia_cotizacion_id']) || $datos['vigencia_cotizacion_id'] === ''){
                throw new Exception("El campo vigencia_cotizacion_id es requerido");
            }
            
            $sql = "INSERT INTO cotizacion (
                cotizacion_id, 
                clientes_id, 
                number, 
                tipo_factura, 
                colaboradores_id, 
                importe, 
                notas, 
                fecha, 
                estado, 
                vigencia_cotizacion_id, 
                usuario, 
                empresa_id, 
                fecha_registro,
                fecha_dolar,
                tipo_entrega
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conexion->prepare($sql);
            
            if(!$stmt) {
                throw new Exception("Error preparando consulta: ".$conexion->error);
            }
            
            // Asegurar tipos de datos correctos
            $datos['cotizacion_id'] = (int)$datos['cotizacion_id'];
            $datos['clientes_id'] = (int)$datos['clientes_id'];
            $datos['numero'] = (int)$datos['numero'];
            $datos['tipo_factura'] = (int)$datos['tipo_factura'];
            $datos['colaboradores_id'] = (int)$datos['colaboradores_id'];
            $datos['importe'] = (float)$datos['importe'];
            $datos['estado'] = (int)$datos['estado'];
            $datos['vigencia_cotizacion_id'] = (int)$datos['vigencia_cotizacion_id'];
            $datos['usuario'] = (int)$datos['usuario'];
            $datos['empresa_id'] = (int)$datos['empresa_id'];
            
            $stmt->bind_param(
                "iisiidssiiissss", 
                $datos['cotizacion_id'], 
                $datos['clientes_id'], 
                $datos['numero'], 
                $datos['tipo_factura'], 
                $datos['colaboradores_id'], 
                $datos['importe'], 
                $datos['notas'], 
                $datos['fecha'], 
                $datos['estado'], 
                $datos['vigencia_cotizacion_id'], 
                $datos['usuario'], 
                $datos['empresa_id'], 
                $datos['fecha_registro'],
                $datos['fecha_dolar'],
                $datos['tipo_entrega']                    
            );
            
            if(!$stmt->execute()) {
                throw new Exception("Error ejecutando consulta: ".$stmt->error);
            }
            
            $conexion->commit();
            $stmt->close();
            return true;
            
        } catch(Exception $e) {
            $conexion->rollback();
            if(isset($stmt)) $stmt->close();
            error_log("ERROR EN agregar_cotizacion_modelo: ".$e->getMessage());
            return $e->getMessage();
        } finally {
            $conexion->autocommit(true);
        }
    }
    
    protected function agregar_detalle_cotizacion($datos){
        $conexion = mainModel::connection();
        $resultado = false;
        
        try {
            $conexion->autocommit(false);
            
            // Obtener el próximo ID disponible
            $cotizacion_detalle_id = mainModel::correlativo("cotizacion_detalle_id", "cotizacion_detalles");
            
            $stmt = $conexion->prepare("INSERT INTO cotizacion_detalles VALUES(?, ?, ?, ?, ?, ?, ?)");
            
            if(!$stmt) {
                throw new Exception("Error preparando consulta: ".$conexion->error);
            }
            
            // Asegurar tipos de datos correctos
            $datos['cotizacion_id'] = (int)$datos['cotizacion_id'];
            $datos['productos_id'] = (int)$datos['productos_id'];
            $datos['cantidad'] = (float)$datos['cantidad'];
            $datos['precio'] = (float)$datos['precio'];
            $datos['isv_valor'] = (float)$datos['isv_valor'];
            $datos['descuento'] = (float)$datos['descuento'];
            
            $stmt->bind_param("iiidddd", 
                $cotizacion_detalle_id,
                $datos['cotizacion_id'], 
                $datos['productos_id'], 
                $datos['cantidad'], 
                $datos['precio'], 
                $datos['isv_valor'], 
                $datos['descuento']
            );
            
            $resultado = $stmt->execute();
            
            if(!$resultado) {
                throw new Exception($stmt->error);
            }
            
            $conexion->commit();
            
        } catch(Exception $e) {
            $conexion->rollback();
            error_log("ERROR EN agregar_detalle_cotizacion: ".$e->getMessage());
            $resultado = false;
        } finally {
            if(isset($stmt)) $stmt->close();
            $conexion->autocommit(true);
            return $resultado;
        }
    }
    
    protected function actualizar_detalle_cotizacion($datos){
        $conexion = mainModel::connection();
        $resultado = false;
        
        try {
            $conexion->autocommit(false);
            
            $stmt = $conexion->prepare("UPDATE cotizacion_detalles 
                SET cantidad = ?, precio = ?, isv_valor = ?, descuento = ? 
                WHERE cotizacion_id = ? AND productos_id = ?");
            
            if(!$stmt) {
                throw new Exception("Error preparando consulta: ".$conexion->error);
            }
            
            // Asegurar tipos de datos correctos
            $datos['cantidad'] = (float)$datos['cantidad'];
            $datos['precio'] = (float)$datos['precio'];
            $datos['isv_valor'] = (float)$datos['isv_valor'];
            $datos['descuento'] = (float)$datos['descuento'];
            $datos['cotizacion_id'] = (int)$datos['cotizacion_id'];
            $datos['productos_id'] = (int)$datos['productos_id'];
                
            $stmt->bind_param("ddddii", 
                $datos['cantidad'], 
                $datos['precio'], 
                $datos['isv_valor'], 
                $datos['descuento'], 
                $datos['cotizacion_id'], 
                $datos['productos_id']
            );
            
            $resultado = $stmt->execute();
            
            if(!$resultado) {
                throw new Exception($stmt->error);
            }
            
            $conexion->commit();
            
        } catch(Exception $e) {
            $conexion->rollback();
            error_log("ERROR EN actualizar_detalle_cotizacion: ".$e->getMessage());
            $resultado = false;
        } finally {
            if(isset($stmt)) $stmt->close();
            $conexion->autocommit(true);
            return $resultado;
        }
    }
    
    protected function actualizar_cotizacion_importe($datos){
        $conexion = mainModel::connection();
        $resultado = false;
        
        try {
            $conexion->autocommit(false);
            
            $stmt = $conexion->prepare("UPDATE cotizacion SET importe = ? WHERE cotizacion_id = ?");
            
            if(!$stmt) {
                throw new Exception("Error preparando consulta: ".$conexion->error);
            }
            
            // Asegurar tipos de datos correctos
            $datos['importe'] = (float)$datos['importe'];
            $datos['cotizacion_id'] = (int)$datos['cotizacion_id'];
            
            $stmt->bind_param("di", 
                $datos['importe'], 
                $datos['cotizacion_id']
            );
            
            $resultado = $stmt->execute();
            
            if(!$resultado) {
                throw new Exception($stmt->error);
            }
            
            $conexion->commit();
            
        } catch(Exception $e) {
            $conexion->rollback();
            error_log("ERROR EN actualizar_cotizacion_importe: ".$e->getMessage());
            $resultado = false;
        } finally {
            if(isset($stmt)) $stmt->close();
            $conexion->autocommit(true);
            return $resultado;
        }
    }
    
    protected function cancelar_cotizacion_modelo($cotizacion_id){
        $conexion = mainModel::connection();
        $resultado = false;
        
        try {
            $conexion->autocommit(false);
            
            $estado = 4; // COTIZACIÓN CANCELADA
            
            $stmt = $conexion->prepare("UPDATE cotizacion SET estado = ? WHERE cotizacion_id = ?");
            
            if(!$stmt) {
                throw new Exception("Error preparando consulta: ".$conexion->error);
            }
            
            $cotizacion_id = (int)$cotizacion_id;
            
            $stmt->bind_param("ii", 
                $estado, 
                $cotizacion_id
            );
            
            $resultado = $stmt->execute();
            
            if(!$resultado) {
                throw new Exception($stmt->error);
            }
            
            $conexion->commit();
            
        } catch(Exception $e) {
            $conexion->rollback();
            error_log("ERROR EN cancelar_cotizacion_modelo: ".$e->getMessage());
            $resultado = false;
        } finally {
            if(isset($stmt)) $stmt->close();
            $conexion->autocommit(true);
            return $resultado;
        }
    }
    
    protected function validDetalleCotizacion($cotizacion_id, $productos_id){
        $conexion = mainModel::connection();
        $resultado = false;
        
        try {
            $stmt = $conexion->prepare("SELECT cotizacion_detalle_id 
                FROM cotizacion_detalles 
                WHERE cotizacion_id = ? AND productos_id = ?");
            
            if(!$stmt) {
                throw new Exception("Error preparando consulta: ".$conexion->error);
            }
            
            $cotizacion_id = (int)$cotizacion_id;
            $productos_id = (int)$productos_id;
            
            $stmt->bind_param("ii", $cotizacion_id, $productos_id);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $resultado = $result->num_rows > 0;
            
        } catch(Exception $e) {
            error_log("ERROR EN validDetalleCotizacion: ".$e->getMessage());
            $resultado = false;
        } finally {
            if(isset($stmt)) $stmt->close();
            return $resultado;
        }
    }
    
    protected function getISV_modelo(){
        try {
            return mainModel::getISV('Facturas');
        } catch(Exception $e) {
            error_log("ERROR EN getISV_modelo: ".$e->getMessage());
            return 0;
        }
    }
    
    protected function getISVEstadoProducto_modelo($productos_id){
        try {
            return mainModel::getISVEstadoProducto($productos_id);
        } catch(Exception $e) {
            error_log("ERROR EN getISVEstadoProducto_modelo: ".$e->getMessage());
            return 0;
        }
    }

    protected function getTotalCotizacionesRegistradas() {
        try {
            $conexion = $this->connection();
            $primerDiaMes = date('Y-m-01');
            $ultimoDiaMes = date('Y-m-t');
    
            $query = "SELECT COUNT(cotizacion_id) AS total 
                      FROM cotizacion 
                      WHERE estado = 1
                      AND CAST(fecha_registro AS DATE) BETWEEN ? AND ?";
            
            $stmt = $conexion->prepare($query);
            
            if(!$stmt) {
                throw new Exception("Error preparando consulta: ".$conexion->error);
            }
            
            $stmt->bind_param("ss", $primerDiaMes, $ultimoDiaMes);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $fila = $result->fetch_assoc();
            $total = (int)$fila['total'];
            
            $stmt->close();
            return $total;
            
        } catch (Exception $e) {
            error_log("Error en getTotalCotizacionesRegistradas: " . $e->getMessage());
            return 0;
        } finally {
            if(isset($stmt)) $stmt->close();
        }
    }
}