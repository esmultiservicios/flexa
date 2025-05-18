<div class="container-fluid">	
	<nav class="breadcrumb-container bg-white py-3 mb-4 shadow-sm">
		<div class="container-fluid">
			<ol class="breadcrumb mb-0">
				<li class="breadcrumb-item">
					<a class="text-decoration-none text-primary" href="<?php echo htmlspecialchars(SERVERURL, ENT_QUOTES, 'UTF-8'); ?>dashboard/">Dashboard</a>
				</li>
				<li class="breadcrumb-item active fw-bold">Cajas</li>
			</ol>
		</div>
	</nav>	
	
    <div class="card mb-4">
        <div class="card-body">
            <form class="form-inline" id="formMainCajas" action="" method="POST" data-form="" autocomplete="off"
                enctype="multipart/form-data">
                <div class="form-group mx-sm-3 mb-1">
                    <div class="form-group mx-sm-3 mb-1">
                        <div class="input-group">
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <div class="sb-nav-link-icon"></div>Estado
                                </span>
                            </div>
                            <select id="estado_cajas" name="estado_cajas" class="selectpicker" data-toggle="tooltip"
                                data-placement="top" title="Estado" data-live-search="true">
                                <option value="1">Activas</option>
                                <option value="2">Cerrada</option>
                            </select>
                        </div>
                    </div>
                    <div class="input-group">
                        <div class="input-group-append">
                            <span class="input-group-text">
                                <div class="sb-nav-link-icon"></div>Fecha Inicial
                            </span>
                        </div>
                        <input type="date" class="form-control" id="fecha_cajas" name="fecha_cajas"
                            value="<?php echo date('Y-m-d');?>">
                    </div>
                </div>
                <div class="form-group mx-sm-3 mb-1">
                    <div class="input-group">
                        <div class="input-group-append">
                            <span class="input-group-text">
                                <div class="sb-nav-link-icon"></div>Fecha Final
                            </span>
                        </div>
                        <input type="date" class="form-control" id="fecha_cajas_f" name="fecha_cajas_f"
                            value="<?php echo date('Y-m-d');?>">
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-cash-register mr-1"></i>
            Cajas
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="dataTableCajas" class="table table-striped table-condensed table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>Acción</th>
                            <th>Comprobante</th>
                            <th>Fecha</th>
                            <th>Usuario</th>
                            <th>Factura Inicial</th>
                            <th>Factura Final</th>
                            <th>Monto Apertura</th>
                            <th>Venta del Día</th>
                            <th>Neto</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
        <div class="card-footer small text-muted">
            <?php
				require_once "./core/mainModel.php";
				
				$insMainModel = new mainModel();
				$entidad = "facturas";
				
				if($insMainModel->getlastUpdate($entidad)->num_rows > 0){
					$consulta_last_update = $insMainModel->getlastUpdate($entidad)->fetch_assoc();
					$fecha_registro = htmlspecialchars($consulta_last_update['fecha_registro'], ENT_QUOTES, 'UTF-8');
					$hora = htmlspecialchars(date('g:i:s a', strtotime($fecha_registro)), ENT_QUOTES, 'UTF-8');
					echo "Última Actualización ".htmlspecialchars($insMainModel->getTheDay($fecha_registro, $hora), ENT_QUOTES, 'UTF-8');
				} else {
					echo "No se encontraron registros ";
				}				
			?>
        </div>
    </div>
</div>
<?php
	$insMainModel->guardar_historial_accesos("Ingreso al modulo Cajas");
?>