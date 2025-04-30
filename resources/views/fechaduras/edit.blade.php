<!DOCTYPE html>
<html>
<head>
    <title>Editar {{ $fechadura->local }}</title>
</head>
<body>
    <h1>Editar Fechadura</h1>
    
    <a href="/fechaduras/{{ $fechadura->id }}">← Voltar</a>

    <form method="POST" action="/fechaduras/{{ $fechadura->id }}" style="margin-top: 20px;">
        @csrf
        @method('PUT')
        
        <div style="margin: 10px 0;">
            <label>Local: 
                <input type="text" name="local" value="{{ $fechadura->local }}" required>
            </label>
        </div>
        
        <div style="margin: 10px 0;">
            <label>IP: 
                <input type="text" name="ip" value="{{ $fechadura->ip }}" required>
            </label>
        </div>
        
        <div style="margin: 10px 0;">
            <label>Usuário API: 
                <input type="text" name="usuario" value="{{ $fechadura->usuario }}" required>
            </label>
        </div>
        
        <div style="margin: 10px 0;">
            <label>Senha API: 
                <input type="password" name="senha" placeholder="Digite a senha">
            </label>
        </div>
        
        <button type="submit">Atualizar</button>
    </form>
</body>
</html>