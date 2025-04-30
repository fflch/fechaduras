<!DOCTYPE html>
<html>
<head>
    <title>Cadastrar Nova Fechadura</title>
</head>
<body>
    <h1>Nova Fechadura</h1>
    
    <a href="/fechaduras">← Voltar</a>

    <form method="POST" action="/fechaduras" style="margin-top: 20px;">
        @csrf
        
        <div style="margin: 10px 0;">
            <label>Local: 
                <input type="text" name="local" required style="width: 300px;">
            </label>
        </div>
        
        <div style="margin: 10px 0;">
            <label>IP: 
                <input type="text" name="ip" required placeholder="Ex: 10.172.2.143">
            </label>
        </div>
        
        <div style="margin: 10px 0;">
            <label>Usuário API: 
                <input type="text" name="usuario" required placeholder="Ex: admin">
            </label>
        </div>
        
        <div style="margin: 10px 0;">
            <label>Senha API: 
                <input type="password" name="senha">
            </label>
        </div>
        
        <button type="submit" style="margin-top: 10px;">Salvar</button>
    </form>
</body>
</html>