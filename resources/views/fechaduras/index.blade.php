@extends("main")
@section("content")
<form action="/sincronizar" method="POST">
    @csrf
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-sync-alt"></i> Sincronizar
    </button>
</form>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3>Fechaduras Cadastradas</h3>
        <a href="/fechaduras/create" class="btn btn-primary">
            + Nova Fechadura
        </a>
        
    </div>
    
    <div class="card-body">
        @if($fechaduras->isEmpty())
        <div class="alert alert-info text-center">
            <i class="fas fa-door-open fa-2x mb-3"></i>
            <h4>Nenhuma fechadura cadastrada</h4>
            <p class="mb-0">Clique em "Nova Fechadura" acima para adicionar</p>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover">
            <thead class="thead-fflch" style="background-color: #002a5e; color: white;">
                    <tr>
                        <th>Local</th>
                        <th>IP</th>
                        <th width="200px">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($fechaduras as $fechadura)
                    <tr>
                        <td>{{ $fechadura->local }}</td>
                        <td>{{ $fechadura->ip }}</td>
                        <td class="d-flex gap-2">
                            <a href="/fechaduras/{{ $fechadura->id }}" class="btn btn-sm btn-info">Ver</a>
                            <a href="/fechaduras/{{ $fechadura->id }}/edit" class="btn btn-sm btn-warning">Editar</a>
                            <form action="/fechaduras/{{ $fechadura->id }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza?')">Excluir</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection