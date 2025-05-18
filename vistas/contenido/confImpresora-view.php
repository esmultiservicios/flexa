<div class="container-fluid">
    <nav class="breadcrumb-container bg-white py-3 mb-4 shadow-sm">
        <div class="container-fluid">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a class="text-decoration-none text-primary" href="<?php echo htmlspecialchars(SERVERURL, ENT_QUOTES, 'UTF-8'); ?>dashboard/">Dashboard</a>
                </li>
                <li class="breadcrumb-item active fw-bold">Impresora</li>
            </ol>
        </div>
    </nav>
	
    <div class="card mb-4">
		<div class="card mb-4">
			<div class="card-header">
				<i class="fa-solid fa-print mr-1"></i>
				Impresora
			</div>
			<div class="card-body"> 
				<div class="table-responsive">
					<table id="dataTableConfImpresora" class="table table-striped table-condensed table-hover" style="width:100%">
						<thead>
							<tr>
								<th>Descripcion</th>
                                <th>Estado</th>
								<th>Acciones</th>
							</tr>
						</thead>
					</table>  
				</div>                   
				</div>
			<div class="card-footer small text-muted">
 			<?php
				require_once "./core/mainModel.php";
				
				$insMainModel = new mainModel();
				$entidad = "impresora";
				
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
	$insMainModel->guardar_historial_accesos("Ingreso al modulo Configurar Almacén");
?>