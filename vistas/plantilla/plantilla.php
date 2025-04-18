<?php
if(!isset($_SESSION)){ 
    session_start(['name'=>'SD']); 
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title><?php echo htmlspecialchars(COMPANY, ENT_QUOTES, 'UTF-8');?></title>
    <link href="<?php echo htmlspecialchars(SERVERURL, ENT_QUOTES, 'UTF-8'); ?>ajax/bootstrap/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" />
    <link href="<?php echo htmlspecialchars(SERVERURL, ENT_QUOTES, 'UTF-8'); ?>ajax/bootstrap/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?php echo htmlspecialchars(SERVERURL, ENT_QUOTES, 'UTF-8'); ?>vistas/plantilla/css/styles.css" rel="stylesheet" />
    <link href="<?php echo htmlspecialchars(SERVERURL, ENT_QUOTES, 'UTF-8'); ?>vistas/plantilla/css/my_styles.css" rel="stylesheet" />
    <link href="<?php echo htmlspecialchars(SERVERURL, ENT_QUOTES, 'UTF-8'); ?>vistas/plantilla/css/main_cards.css" rel="stylesheet" />
    <link href="<?php echo htmlspecialchars(SERVERURL, ENT_QUOTES, 'UTF-8'); ?>ajax/bootstrap/css/bootstrap-select.min.css" rel="stylesheet" crossorigin="anonymous" />
    <link rel="stylesheet" href="<?php echo htmlspecialchars(SERVERURL, ENT_QUOTES, 'UTF-8'); ?>fontawesome/css/all.min.css">
    <link rel="shortcut icon" href="<?php echo htmlspecialchars(SERVERURL, ENT_QUOTES, 'UTF-8'); ?>vistas/plantilla/img/icono.png">
<!--     <link href="<?php echo htmlspecialchars(SERVERURL, ENT_QUOTES, 'UTF-8'); ?>ajax/sweetalert/sweetalert.css"
        rel="stylesheet" crossorigin="anonymous" /> -->
</head>

<body class="sb-nav-fixed">
    <?php
    if (SISTEMA_PRUEBA=="SI"){ //CAJA
    ?>
    <span class="container-fluid prueba-sistema">SISTEMA DE PRUEBA</span>
    <?php
    }
    ?>

    <?php
    $peticionAjax = false;
    require_once "./controladores/vitasControlador.php";
    $vt = new vistasControlador();
    $vistasR = $vt->getVistasControlador();
    if($vistasR=="login" || $vistasR=="404"):
        if($vistasR=="login"){
            require_once "./vistas/contenido/login-view.php";
        }else{
            require_once "./vistas/contenido/404-view.php";
        } 
    else:		   
        require_once "./controladores/loginControlador.php";
        $lc = new loginControlador();
        if(!isset($_SESSION['token_sd']) || !isset($_SESSION['user_sd'])){
            $lc->forzar_cierre_sesion_controlador();
        }   
        $ruta = explode("/", htmlspecialchars($_GET['views'], ENT_QUOTES, 'UTF-8'));//DIVIDIMOS EN PARTES LA VARIABLE           
    ?>

    <!-- Navbar Top -->
    <?php
    $prefixes = array("clinicarehn_", "clientes_");
    $nombre_db_final = str_replace($prefixes, "", $GLOBALS['db']);
    echo htmlspecialchars($_SESSION['modo_soporte'], ENT_QUOTES, 'UTF-8') ."***";
    if ($_SESSION['modo_soporte'] === "SI") {
        echo '<span class="modo_soporte"> <i class="fas fa-headset"></i> Usted está en modo soporte para el cliente: '.htmlspecialchars($nombre_db_final, ENT_QUOTES, 'UTF-8').'</span>';
    }
    ?>

    <?php require_once "./vistas/plantilla/modulos/navbartop.php";?>
    <!-- fin Navbar Top -->

    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <!-- Navbar Lateral -->
            <?php require_once "./vistas/plantilla/modulos/navbarlateral.php";?>
            <!-- Fin Navbar Lateral -->
        </div>

        <div id="layoutSidenav_content">
            <main>
                <!-- Contenido -->
                <?php 			
                require_once htmlspecialchars($vistasR, ENT_QUOTES, 'UTF-8');
                ?>
                <!-- Fin Contenido -->

                <?php 
                if(is_file("./vistas/plantilla/modulos/".htmlspecialchars($ruta[0], ENT_QUOTES, 'UTF-8').".php")){
                    require_once "./vistas/plantilla/modulos/".htmlspecialchars($ruta[0], ENT_QUOTES, 'UTF-8').".php"; 
                }else{
                    require_once "./vistas/plantilla/modulos/footer.php";
                }  			
                ?>
            </main>
        </div>
    </div>

    <?php 
    if(is_file("./vistas/contenido/modals/".htmlspecialchars($ruta[0], ENT_QUOTES, 'UTF-8').".php")){
        require_once "./vistas/contenido/modals/".htmlspecialchars($ruta[0], ENT_QUOTES, 'UTF-8').".php"; 
    }       
    //VENTANAS MODALES
    require_once "./vistas/contenido/modals/vistasModals.php";   
    //Scripts
    require_once "./vistas/plantilla/modulos/script.php";
    //CIERRE DE SESIÓN
    require_once "./vistas/plantilla/modulos/logoutScript.php";
    //SCRIPT VENTANAS MODALES
    require_once "./ajax/js/main.php";
    //LLAMAMOS EL AJAX SEGUN LA VISTA 
    if(is_file("./ajax/js/".htmlspecialchars($ruta[0], ENT_QUOTES, 'UTF-8').".php")){
        require_once "./ajax/js/".htmlspecialchars($ruta[0], ENT_QUOTES, 'UTF-8').".php"; 
    }                    
    endif; 		
    ?>
</body>

</html>