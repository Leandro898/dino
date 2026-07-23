<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifica tu Correo - Bari Rider</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        table, td {
            border-collapse: collapse;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f3f4f6;
            padding: 40px 20px;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.025);
            overflow: hidden;
        }
        .header {
            text-align: center;
            padding: 32px 24px 20px 24px;
            border-bottom: 1px solid #f3f4f6;
        }
        .logo-text {
            font-size: 32px;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin: 0;
        }
        .logo-bari {
            color: #4f46e5;
        }
        .logo-rider {
            color: #7c3aed;
            font-weight: 300;
        }
        .content {
            padding: 32px 24px;
            color: #374151;
            line-height: 1.6;
            font-size: 15px;
        }
        .content h2 {
            font-size: 20px;
            font-weight: 700;
            color: #1f2937;
            margin-top: 0;
            margin-bottom: 16px;
        }
        .content p {
            margin-top: 0;
            margin-bottom: 16px;
        }
        .btn-wrapper {
            text-align: center;
            margin: 28px 0;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            background-color: #4f46e5;
            color: #ffffff !important;
            font-weight: 600;
            font-size: 15px;
            text-decoration: none;
            padding: 14px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.25);
            text-align: center;
        }
        .footer {
            background-color: #f9fafb;
            padding: 24px;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
            border-top: 1px solid #f3f4f6;
        }
        .footer p {
            margin: 0 0 8px 0;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <!-- Header -->
            <div class="header">
                <h1 class="logo-text">
                    <span class="logo-bari">Bari</span><span class="logo-rider">Rider</span>
                </h1>
            </div>

            <!-- Content -->
            <div class="content">
                <h2>¡Hola, {{ $name }}!</h2>
                <p>Te damos la bienvenida a <strong>Bari Rider</strong>.</p>
                <p>Para poder comenzar a entregar pedidos y ver las alertas disponibles en tu zona, primero necesitas verificar tu dirección de correo electrónico presionando el botón de abajo:</p>
                
                <div class="btn-wrapper">
                    <a href="{{ $url }}" class="btn">Verificar Correo Electrónico</a>
                </div>
                
                <p style="font-size: 13px; color: #6b7280; margin-top: 24px;">Si no solicitaste crear esta cuenta, no te preocupes, puedes ignorar o eliminar este correo de forma segura.</p>
                
                <p style="margin-top: 32px; margin-bottom: 0;">
                    Saludos,<br>
                    <strong>El equipo de BariTienda</strong>
                </p>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p>&copy; {{ date('Y') }} BariTienda. Todos los derechos reservados.</p>
            </div>
        </div>
    </div>
</body>
</html>
