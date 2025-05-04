<?php
$peticionAjax = true;
require_once "././core/configAPP.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso al Sistema</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous">
    <link href="<?php echo htmlspecialchars(SERVERURL, ENT_QUOTES, 'UTF-8'); ?>ajax/bootstrap/css/bootstrap-select.min.css" rel="stylesheet" crossorigin="anonymous" />
    <link href="<?php echo htmlspecialchars(SERVERURL, ENT_QUOTES, 'UTF-8'); ?>ajax/sweetalert/sweetalert.css" rel="stylesheet" crossorigin="anonymous" />
    <style>
        /* Estilos Base y Reset */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --main-bg: #f4f7fe;
            --card-bg: #ffffff;
            --input-bg: #f7f9ff;
            --input-focus-bg: #ffffff;
            --input-border: #e4e8f7;
            --input-focus-border: #5469d4;
            --text-primary: #1a1f36;
            --text-secondary: #606b85;
            --text-tertiary: #8792a2;
            --accent: #5469d4;
            --accent-hover: #4054b2;
            --error: #ff4d4f;
            --success: #52c41a;
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 8px 24px rgba(0, 0, 0, 0.08);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --transition: all 0.3s ease;
            --font-primary: 'SF Pro Display', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        body {
            font-family: var(--font-primary);
            background-color: var(--main-bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: var(--text-primary);
            line-height: 1.6;
        }

        /* Layout Principal */
        .auth-container {
            display: flex;
            width: 100%;
            max-width: 1000px;
            min-height: 600px;
            background: var(--card-bg);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-md);
        }

        .auth-sidebar {
            flex: 0 0 40%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            position: relative;
            color: white;
            text-align: center;
        }

        .auth-sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.08' fill-rule='evenodd'/%3E%3C/svg%3E");
            opacity: 0.6;
        }

        .sidebar-content {
            position: relative;
            z-index: 1;
        }

        .sidebar-logo {
            width: 80px;
            height: 80px;
            margin-bottom: 30px;
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
        }

        .sidebar-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 16px;
            letter-spacing: -0.5px;
        }

        .sidebar-text {
            font-size: 16px;
            margin-bottom: 30px;
            opacity: 0.8;
        }

        .sidebar-features {
            width: 100%;
            text-align: left;
            padding-left: 20px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 16px;
            font-size: 15px;
        }

        .feature-icon {
            margin-right: 12px;
            color: rgba(255, 255, 255, 0.9);
            font-size: 18px;
        }

        .auth-forms {
            flex: 1;
            padding: 40px;
            overflow-y: auto;
            position: relative;
        }

        #logreg-forms {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
        }

        /* Estilos de Formularios */
        .form-signin, .form-reset, .form-signup {
            display: none;
            opacity: 0;
            transform: translateY(20px);
            transition: var(--transition);
        }

        .form-signin {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }

        .form-header {
            margin-bottom: 32px;
            text-align: center;
        }

        .form-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
            color: var(--text-primary);
        }

        .form-subtitle {
            font-size: 15px;
            color: var(--text-secondary);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
            color: var(--text-secondary);
        }

        /* INPUT GROUPS - REVISADO Y CORREGIDO */
        .input-group {
            position: relative;
            display: flex;
            align-items: stretch;
            width: 100%;
        }

        .input-group-prepend {
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            z-index: 4;
            display: flex;
            align-items: center;
            padding-left: 12px;
            pointer-events: none;
        }

        .input-group-append {
            position: absolute;
            right: 0;
            top: 0;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding-right: 12px;
            z-index: 5;
        }
        
        .input-group-icon {
            color: var(--text-tertiary);
            font-size: 16px;
        }

        .input-group-toggle {
            background: transparent;
            border: none;
            padding: 0;
            margin: 0;
            color: var(--text-tertiary);
            cursor: pointer;
            font-size: 16px;
            width: 24px;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .input-group-toggle:hover {
            color: var(--accent);
        }

        .form-control {
            width: 100%;
            padding-right: 40px !important;
            font-size: 15px;
            border: 1px solid var(--input-border);
            border-radius: var(--radius-sm);
            background-color: var(--input-bg);
            color: var(--text-primary);
            transition: var(--transition);
            height: 44px;
            position: relative;
            z-index: 3;
        }

        /* Ajustes para inputs con botón de mostrar contraseña */
        .input-group-toggle + .form-control {
            padding-right: 40px;
        }

        .form-control::placeholder {
            opacity: 1;
            color: var(--text-tertiary);
            transition: opacity 0.2s ease;
        }

        .form-control:focus::placeholder {
            opacity: 0.5;
        }

        .form-control:focus {
            border-color: var(--input-focus-border);
            background-color: var(--input-focus-bg);
            outline: 0;
            box-shadow: 0 0 0 2px rgba(84, 105, 212, 0.1);
        }

        /* Cliente/PIN Group */
        .multi-field-group {
            display: flex;
            gap: 10px;
        }

        .multi-field-group .input-group {
            flex: 1;
        }

        /* Botones */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 12px 20px;
            font-size: 15px;
            font-weight: 600;
            text-align: center;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background-color: var(--accent);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--accent-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(84, 105, 212, 0.25);
        }

        .btn-primary:active {
            transform: translateY(0);
            box-shadow: none;
        }

        .btn-icon {
            margin-right: 8px;
            font-size: 16px;
        }

        .link-button {
            background: none;
            border: none;
            color: var(--accent);
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            padding: 0;
            text-decoration: none;
            margin-top: 16px;
            display: inline-flex;
            align-items: center;
            transition: var(--transition);
            text-decoration: none;
        }

        .link-button:hover {
            color: var(--accent-hover);
            text-decoration: underline;
            text-decoration: none;
        }

        .link-icon {
            margin-right: 6px;
            font-size: 14px;
        }

        .action-links {
            margin-top: 16px;
            text-align: center;
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 24px 0;
            color: var(--text-tertiary);
            font-size: 14px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background-color: var(--input-border);
        }

        .divider::before {
            margin-right: 16px;
        }

        .divider::after {
            margin-left: 16px;
        }

        /* Mensajes de respuesta */
        .RespuestaAjax {
            padding: 12px 16px;
            border-radius: var(--radius-sm);
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
        }

        .RespuestaAjax.error {
            background-color: rgba(255, 77, 79, 0.08);
            border-left: 3px solid var(--error);
            color: var(--error);
        }

        .RespuestaAjax.success {
            background-color: rgba(82, 196, 26, 0.08);
            border-left: 3px solid var(--success);
            color: var(--success);
        }

        /* Footer */
        .footer-copyright {
            text-align: center;
            font-size: 13px;
            color: var(--text-tertiary);
            margin-top: 40px;
        }

        /* Animaciones y transiciones adicionales */
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(84, 105, 212, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(84, 105, 212, 0); }
            100% { box-shadow: 0 0 0 0 rgba(84, 105, 212, 0); }
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .auth-container {
                flex-direction: column;
                max-width: 600px;
            }
            
            .auth-sidebar {
                flex: 0 0 auto;
                padding: 30px;
            }
            
            .sidebar-logo {
                width: 60px;
                height: 60px;
                margin-bottom: 15px;
            }
            
            .sidebar-title {
                font-size: 22px;
                margin-bottom: 10px;
            }
            
            .sidebar-text, .sidebar-features {
                display: none;
            }
        }

        @media (max-width: 576px) {
            .auth-container {
                box-shadow: none;
                background: transparent;
            }
            
            .auth-sidebar {
                border-radius: var(--radius-lg) var(--radius-lg) 0 0;
                padding: 20px;
            }
            
            .auth-forms {
                padding: 30px 20px;
                background: var(--card-bg);
                border-radius: 0 0 var(--radius-lg) var(--radius-lg);
            }
            
            .form-title {
                font-size: 20px;
            }
            
            .multi-field-group {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <!-- Panel lateral con información -->
        <div class="auth-sidebar">
            <div class="sidebar-content">
                <img src="<?php echo htmlspecialchars(SERVERURL, ENT_QUOTES, 'UTF-8'); ?>vistas/plantilla/img/logo.svg" alt="Logo" class="sidebar-logo">
                <h1 class="sidebar-title">Bienvenido a su Portal</h1>
                <p class="sidebar-text">Acceda a todas sus herramientas y servicios desde un solo lugar.</p>
                
                <div class="sidebar-features">
                    <div class="feature-item">
                        <i class="fas fa-shield-alt feature-icon"></i>
                        <span>Seguridad avanzada</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-tachometer-alt feature-icon"></i>
                        <span>Interfaz optimizada</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-sync feature-icon"></i>
                        <span>Actualizaciones automáticas</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Área de formularios -->
        <div class="auth-forms">
            <div id="logreg-forms">
                <!-- Formulario Inicio de Sesión -->
                <form class="form-signin" id="loginform" action="" method="POST" autocomplete="off">
                    <div class="form-header">
                        <h2 class="form-title">Iniciar Sesión</h2>
                        <p class="form-subtitle">Ingrese sus credenciales para continuar</p>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="inputEmail">Correo electrónico</label>
                        <div class="input-group">
                            <input type="email" id="inputEmail" name="inputEmail" class="form-control" placeholder="nombre@empresa.com" required autofocus tabindex="1">
                        </div>
                    </div>
                                        
                    <div class="form-group">
                        <label class="form-label" for="inputPassword">Contraseña</label>
                        <div class="input-group">
                            <input type="password" id="inputPassword" name="inputPassword" class="form-control" placeholder="Ingrese su contraseña" required>
                            <div class="input-group-append">
                                <button id="show_password" class="input-group-toggle" type="button">
                                    <span id="icon" class="fa fa-eye-slash"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group" id="groupDB" style="display: none;">
                        <label class="form-label">Información adicional</label>
                        <div class="multi-field-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-icon"><i class="fas fa-user"></i></span>
                                </div>
                                <input type="number" class="form-control" value="" placeholder="Cliente" aria-label="Cliente" tabindex="4" id="inputCliente" name="inputCliente">
                            </div>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-icon"><i class="fas fa-key"></i></span>
                                </div>
                                <input type="number" class="form-control" value="" placeholder="PIN" aria-label="PIN" tabindex="5" id="inputPin" name="inputPin">
                            </div>
                        </div>
                    </div>
                    
                    <div class="RespuestaAjax"></div>
                    
                    <button class="btn btn-primary" type="submit" id="enviar" tabindex="6">
                        <i class="fas fa-sign-in-alt btn-icon"></i>Acceder
                    </button>
                    
                    <div class="action-links">
                        <button type="button" id="forgot_pswd" class="link-button" tabindex="7">
                            <i class="fas fa-question-circle link-icon"></i>¿Olvidó su contraseña?
                        </button>
                    </div>
                    
                    <div class="divider">o</div>
                    
                    <button class="btn btn-primary" type="button" id="btn-signup">
                        <i class="fas fa-user-plus btn-icon"></i>Crear una nueva cuenta
                    </button>
                </form>

                <!-- Formulario Resetear Contraseña -->
                <form class="form-reset" id="forgot_form" autocomplete="off">
                    <div class="form-header">
                        <h2 class="form-title">Recuperar Contraseña</h2>
                        <p class="form-subtitle">Ingrese su correo para recibir instrucciones</p>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="usu_forgot">Correo electrónico</label>
                        <input type="email" class="form-control" placeholder="nombre@empresa.com" required autofocus name="usu_forgot" id="usu_forgot" tabindex="1">
                    </div>
                    
                    <div class="RespuestaAjax"></div>
                    
                    <button class="btn btn-primary" type="submit" tabindex="2">
                        <i class="fas fa-paper-plane btn-icon"></i>Enviar instrucciones
                    </button>
                    
                    <div class="action-links">
                        <button type="button" id="cancel_reset" class="link-button" tabindex="3">
                            <i class="fas fa-arrow-left link-icon"></i>Volver al inicio de sesión
                        </button>
                    </div>
                </form>

                <!-- Formulario Registro -->
                <form class="form-signup" id="form_registro" autocomplete="off">
                    <div class="form-header">
                        <h2 class="form-title">Crear Nueva Cuenta</h2>
                        <p class="form-subtitle">Complete el formulario para registrarse</p>
                    </div>
                                        
                    <div class="form-group">
                        <label class="form-label" for="user_name">Empresa o Nombre</label>
                        <input type="text" id="user_name" name="user_name" class="form-control" placeholder="Nombre de su empresa" required autofocus data-toggle="tooltip" data-placement="top" title="Ingrese la empresa o su nombre completo" tabindex="1">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="user_telefono">Teléfono</label>
                        <input type="number" id="user_telefono" name="user_telefono" class="form-control" placeholder="Número de contacto" required tabindex="2">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="mail">Correo Electrónico</label>
                        <input type="email" class="form-control" placeholder="nombre@empresa.com" id="mail" name="email" required tabindex="3">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="user-pass">Contraseña</label>
                        <div class="input-group">
                            <input type="password" id="user-pass" name="user-pass" class="form-control" placeholder="Ingrese su contraseña" required tabindex="4">
                            <div class="input-group-append">
                                <button id="show_password1" class="input-group-toggle" type="button" tabindex="5">
                                    <span id="icon1" class="fa fa-eye-slash"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                                        
                    <div class="form-group">
                        <label class="form-label" for="user-repeatpass">Confirmar contraseña</label>
                        <div class="input-group">
                            <input type="password" id="user-repeatpass" name="user-repeatpass" class="form-control" placeholder="Ingrese su contraseña" required tabindex="6">
                            <div class="input-group-append">
                                <button id="show_password2" class="input-group-toggle" type="button" tabindex="7">
                                    <span id="icon2" class="fa fa-eye-slash"></span>
                                </button>
                            </div>
                        </div>
                    </div>                
                    
                    <button class="btn btn-primary" type="button" id="registrarse" tabindex="8">
                        <i class="fas fa-user-check btn-icon"></i>Completar registro
                    </button>
                    
                    <div class="action-links">
                        <button type="button" id="cancel_signup" class="link-button" tabindex="9">
                            <i class="fas fa-arrow-left link-icon"></i>Volver al inicio de sesión
                        </button>
                    </div>
                </form>

                <!-- Footer -->
                <div class="footer-copyright">
                    © 2021 - <?php echo date("Y"); ?> Todos los derechos reservados.
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="<?php echo htmlspecialchars(SERVERURL, ENT_QUOTES, 'UTF-8'); ?>ajax/query/jquery-3.5.1.min.js" crossorigin="anonymous"></script>
    <script src="<?php echo htmlspecialchars(SERVERURL, ENT_QUOTES, 'UTF-8'); ?>ajax/popper/popper.min.js" crossorigin="anonymous"></script>
    <script src="<?php echo htmlspecialchars(SERVERURL, ENT_QUOTES, 'UTF-8'); ?>ajax/bootstrap/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <script src="<?php echo htmlspecialchars(SERVERURL, ENT_QUOTES, 'UTF-8'); ?>ajax/bootstrap/js/bootstrap-select.min.js" crossorigin="anonymous"></script>
    <script src="<?php echo htmlspecialchars(SERVERURL, ENT_QUOTES, 'UTF-8'); ?>ajax/sweetalert/sweetalert.min.js" crossorigin="anonymous"></script>
    <script src="<?php echo htmlspecialchars(SERVERURL, ENT_QUOTES, 'UTF-8'); ?>ajax/js/script_login.js" crossorigin="anonymous"></script>
    <script>
        $(document).ready(function() {
            // Animaciones entre formularios
            function showForm(formToShow) {
                $('.form-signin, .form-reset, .form-signup').css({
                    'display': 'none',
                    'opacity': '0',
                    'transform': 'translateY(20px)'
                });
                
                setTimeout(function() {
                    $(formToShow).css({
                        'display': 'block'
                    });
                    
                    setTimeout(function() {
                        $(formToShow).css({
                            'opacity': '1',
                            'transform': 'translateY(0)'
                        });
                    }, 50);
                }, 200);
            }
            
            // Control de navegación entre formularios
            $('#forgot_pswd').click(function(e) {
                e.preventDefault();
                showForm('.form-reset');
            });
            
            $('#btn-signup').click(function(e) {
                e.preventDefault();
                showForm('.form-signup');
            });
            
            $('#cancel_reset, #cancel_signup').click(function(e) {
                e.preventDefault();
                showForm('.form-signin');
            });
            
            // Toggle ver/ocultar contraseña
            $('.input-group-toggle').click(function(e) {
                e.preventDefault();
                let button = $(this);
                let icon = button.find('span');
                let input = button.closest('.input-group').find('input');
                
                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                } else {
                    input.attr('type', 'password');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                }
            });
            
            // Efecto focus en inputs
            $('.form-control').focus(function() {
                $(this).closest('.input-group').addClass('focused');
            }).blur(function() {
                $(this).closest('.input-group').removeClass('focused');
            });
            
            // Inicializar tooltips
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
    <?php
    require_once "./ajax/js/login.php";
    ?>
</body>
</html>