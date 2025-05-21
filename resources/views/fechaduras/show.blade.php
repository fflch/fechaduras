@extends('main')
@section('content')
    <div class="card">
        <div class="card-header">
            <h2>{{ $fechadura->local }}</h2>
        </div>

        <div class="card-body">
            <p><strong>IP:</strong> {{ $fechadura->ip }}</p>
            <p><strong>Usuário API:</strong> {{ $fechadura->usuario }}</p>

            <div class="mt-4">
                <a href="/fechaduras" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
                <a href="/fechaduras/{{ $fechadura->id }}/edit" class="btn btn-warning">Editar</a>
                <a href="/fechaduras/{{ $fechadura->id }}/logs" class="btn btn-info"><i class="fas fa-history"></i> Histórico
                    de acesso</a>
            </div>
        </div>

    </div>

    @if(session('success'))
    <div class="alert alert-success" role="alert">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger" role="alert">
        {{ session('error') }}
    </div>
    @endif

    <div class="mt-2">
        <form method="post" action="/fechaduras/{{ $fechadura->id }}/sincronizar">
            @csrf
            <label for="setor" class="form-label">Escolha um setor para sincronização</label>
            <select name="setores[]" class="select2 form-control" multiple="multiple">
                @foreach (\App\Services\ReplicadoService::retornaSetores() as $setor)
                    <option value="{{ $setor['codset'] }}">
                        {{ $setor['nomabvset'] }} - {{ $setor['nomset'] }}</option>
                @endforeach
            </select>
            <button class="btn btn-primary" style="margin-top:5px;">
                <i class="fas fa-sync-alt"></i> Sincronizar dados
            </button>
        </form>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h3>Usuários Cadastrados ({{ count($usuarios) }})</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="thead-fflch" style="background-color: #002a5e; color: white;">
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
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.form-control').select2();
        });
    </script>
@endsection
