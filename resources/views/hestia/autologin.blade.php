<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acessando painel</title>
</head>
<body onload="document.getElementById('hestia-login').submit()">
    <form id="hestia-login" method="POST" action="{{ $url }}">
        <input type="hidden" name="user" value="{{ $user }}">
        <input type="hidden" name="password" value="{{ $password }}">
        <input type="hidden" name="login" value="Log in">
        <noscript>
            <button type="submit">Acessar painel</button>
        </noscript>
    </form>
</body>
</html>
