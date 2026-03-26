<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f8f8f8; padding: 40px 0; }
        .container { max-width: 560px; margin: 0 auto; background: #fff; border-radius: 8px; padding: 40px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        h1 { color: #333; font-size: 22px; margin-bottom: 16px; }
        p { color: #555; font-size: 15px; line-height: 1.6; }
        .btn { display: inline-block; background: #734A32; color: #fff !important; text-decoration: none; padding: 14px 32px; border-radius: 30px; font-weight: bold; font-size: 15px; margin: 24px 0; }
        .footer { margin-top: 32px; padding-top: 16px; border-top: 1px solid #eee; color: #999; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Verifica tu email</h1>
        <p>Hola <strong>{{ $user->name }}</strong>,</p>
        <p>Gracias por registrarte en la Asociacion de Artesanos. Hace click en el siguiente boton para verificar tu direccion de email:</p>
        <p style="text-align: center;">
            <a href="{{ $verifyUrl }}" class="btn">Verificar Email</a>
        </p>
        <p>Si no creaste una cuenta, podes ignorar este email.</p>
        <div class="footer">
            <p>Asociacion de Artesanos</p>
        </div>
    </div>
</body>
</html>
