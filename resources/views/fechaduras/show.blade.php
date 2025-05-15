<!DOCTYPE html>
<html>
<head>
    <title>{{ $fechadura->local }} - Detalhes</title>
    <style>
        .card { 
            border: 1px solid #ddd; 
            padding: 20px; 
            margin-bottom: 20px;
            max-width: 500px;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
    </style>
</head>
<body>
    <div class="card">
        <h2>{{ $fechadura->local }}</h2>
        <p><strong>IP:</strong> {{ $fechadura->ip }}</p>
        <p><strong>Usuário API:</strong> {{ $fechadura->usuario }}</p>
    </div>

    <div style="margin-top: 20px;">
        <a href="/fechaduras/{{ $fechadura->id }}/edit">Editar</a> |
        <a href="/fechaduras">Voltar para lista</a>
    </div>
    <form method="post" action="/fechaduras/{{$fechadura->id}}/sincronizar">
        @csrf
        <button>Sincronizar dados</button>
    </form>
    <h3>Usuários Cadastrados na Fechadura</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
            </tr>
        </thead>
        <tbody>
            @forelse($usuarios as $usuario)
                <tr>
                    <td>{{ $usuario['id'] ?? 'N/A' }}</td>
                    <td>{{ $usuario['name'] ?? 'Sem nome' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2">Nenhum usuário encontrado</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>