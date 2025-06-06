<?php
    if($peticionAjax){
        require_once "../core/mainModel.php";
    }else{
        require_once "./core/mainModel.php";    
    }
    
    class cotizacionModelo extends mainModel{        
        protected function agregar_cotizacion_modelo($datos){
            $conexion = mainModel::connection();
            
            try {
                $conexion->autocommit(false);
                
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
                return true;
                
            } catch(Exception $e) {
                $conexion->rollback();
                // Registrar el error completo
                error_log("ERROR EN agregar_cotizacion_modelo: ".$e->getMessage());
                return $e->getMessage(); // Devuelve el mensaje de error en lugar de false
            }
        }
        
        protected function agregar_detalle_cotizacion($datos){
            $conexion = mainModel::connection();
            
            try {
                // Desactivar autocommit para la transacción
                $conexion->autocommit(false);
                
                // Obtener el próximo ID disponible
                $cotizacion_detalle_id = mainModel::correlativo("cotizacion_detalle_id", "cotizacion_detalles");
                
                $stmt = $conexion->prepare("INSERT INTO cotizacion_detalles VALUES(?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iiidddd", 
                    $cotizacion_detalle_id,
                    $datos['cotizacion_id'], 
                    $datos['productos_id'], 
                    $datos['cantidad'], 
                    $datos['precio'], 
                    $datos['isv_valor'], 
                    $datos['descuento']
                );
                
                $ejecutado = $stmt->execute();
                
                if(!$ejecutado) {
                    throw new Exception($stmt->error);
                }
                
                // Confirmar la transacción
                $conexion->commit();
                
                return true;
                
            } catch(Exception $e) {
                // Revertir la transacción en caso de error
                $conexion->rollback();
                return false;
            }
        }
        
        protected function actualizar_detalle_cotizacion($datos){
            $conexion = mainModel::connection();
            
            try {
                // Desactivar autocommit para la transacción
                $conexion->autocommit(false);
                
                $stmt = $conexion->prepare("UPDATE cotizacion_detalles 
                    SET cantidad = ?, precio = ?, isv_valor = ?, descuento = ? 
                    WHERE cotizacion_id = ? AND productos_id = ?");
                    
                $stmt->bind_param("ddddii", 
                    $datos['cantidad'], 
                    $datos['precio'], 
                    $datos['isv_valor'], 
                    $datos['descuento'], 
                    $datos['cotizacion_id'], 
                    $datos['productos_id']
                );
                
                $ejecutado = $stmt->execute();
                
                if(!$ejecutado) {
                    throw new Exception($stmt->error);
                }
                
                // Confirmar la transacción
                $conexion->commit();
                
                return true;
                
            } catch(Exception $e) {
                // Revertir la transacción en caso de error
                $conexion->rollback();
                return false;
            }
        }
        
        protected function actualizar_cotizacion_importe($datos){
            $conexion = mainModel::connection();
            
            try {
                // Desactivar autocommit para la transacción
                $conexion->autocommit(false);
                
                $stmt = $conexion->prepare("UPDATE cotizacion SET importe = ? WHERE cotizacion_id = ?");
                $stmt->bind_param("di", 
                    $datos['importe'], 
                    $datos['cotizacion_id']
                );
                
                $ejecutado = $stmt->execute();
                
                if(!$ejecutado) {
                    throw new Exception($stmt->error);
                }
                
                // Confirmar la transacción
                $conexion->commit();
                
                return true;
                
            } catch(Exception $e) {
                // Revertir la transacción en caso de error
                $conexion->rollback();
                return false;
            }
        }
        
        protected function cancelar_cotizacion_modelo($cotizacion_id){
            $conexion = mainModel::connection();
            
            try {
                // Desactivar autocommit para la transacción
                $conexion->autocommit(false);
                
                $estado = 4; // COTIZACIÓN CANCELADA
                
                $stmt = $conexion->prepare("UPDATE cotizacion SET estado = ? WHERE cotizacion_id = ?");
                $stmt->bind_param("ii", 
                    $estado, 
                    $cotizacion_id
                );
                
                $ejecutado = $stmt->execute();
                
                if(!$ejecutado) {
                    throw new Exception($stmt->error);
                }
                
                // Confirmar la transacción
                $conexion->commit();
                
                return true;
                
            } catch(Exception $e) {
                // Revertir la transacción en caso de error
                $conexion->rollback();
                return false;
            }
        }
        
        protected function validDetalleCotizacion($cotizacion_id, $productos_id){
            $conexion = mainModel::connection();
            
            try {
                $stmt = $conexion->prepare("SELECT cotizacion_detalle_id 
                    FROM cotizacion_detalles 
                    WHERE cotizacion_id = ? AND productos_id = ?");
                    
                $stmt->bind_param("ii", $cotizacion_id, $productos_id);
                $stmt->execute();
                
                $result = $stmt->get_result();
                $stmt->close();
                
                return $result;
                
            } catch(Exception $e) {
                return false;
            }
        }
        
        protected function getISV_modelo(){
            return mainModel::getISV('Facturas');
        }
        
        protected function getISVEstadoProducto_modelo($productos_id){
            return mainModel::getISVEstadoProducto($productos_id);
        }

        protected function getTotalCotizacionesRegistradas() {
            try {
                $conexion = $this->connection();
                $primerDiaMes = date('Y-m-01');
                $ultimoDiaMes = date('Y-m-t');
        
                $query = "SELECT COUNT(cotizacion_id) AS total 
                          FROM cotizacion 
                          WHERE estado = 1
                          AND CAST(fecha_registro AS DATE) BETWEEN '$primerDiaMes' AND '$ultimoDiaMes'";
        
                $resultado = $conexion->query($query);
                $fila = $resultado->fetch_assoc();
                return (int)$fila['total'];
            } catch (Exception $e) {
                error_log("Error en getTotalCotizacionesRegistradas: " . $e->getMessage());
                return 0;
            }
        }
    }