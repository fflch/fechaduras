<!DOCTYPE html>
<html>
<head>
    <title>Fechaduras Cadastradas</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .actions a { margin-right: 10px; }
    </style>
</head>
<body>
    <h1>Fechaduras</h1>
    
    <a href="/fechaduras/create" style="display: inline-block; margin-bottom: 20px;">
        <button>+ Adicionar Fechadura</button>
    </a>

    <table>
        <thead>
            <tr>
                <th>Local</th>
                <th>IP</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($fechaduras as $fechadura)
            <tr>
                <td>{{ $fechadura->local }}</td>
                <td>{{ $fechadura->ip }}</td>
                <td class="actions">
                    <a href="/fechaduras/{{ $fechadura->id }}">Ver</a>
                    <a href="/fechaduras/{{ $fechadura->id }}/edit">Editar</a>
                    <form action="/fechaduras/{{ $fechadura->id }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" onclick="return confirm('Tem certeza?')">Excluir</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>