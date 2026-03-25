<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f8f8f8; padding: 40px 0; }
        .container { max-width: 560px; margin: 0 auto; background: #fff; border-radius: 8px; padding: 40px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        h1 { color: #333; font-size: 20px; margin-bottom: 20px; }
        .field { margin-bottom: 16px; }
        .label { font-size: 12px; color: #999; text-transform: uppercase; font-weight: bold; margin-bottom: 4px; }
        .value { font-size: 15px; color: #333; line-height: 1.5; }
        .message-box { background: #f9f7f2; border-radius: 8px; padding: 20px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Nuevo mensaje de contacto</h1>
        <div class="field">
            <div class="label">Nombre</div>
            <div class="value">{{ $contactName }}</div>
        </div>
        <div class="field">
            <div class="label">Email</div>
            <div class="value">{{ $contactEmail }}</div>
        </div>
        <div class="field">
            <div class="label">Asunto</div>
            <div class="value">{{ $contactSubject }}</div>
        </div>
        <div class="message-box">
            <div class="label">Mensaje</div>
            <div class="value">{!! nl2br(e($contactMessage)) !!}</div>
        </div>
    </div>
</body>
</html>
