@extends("main")
@section("content")

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3>Fechaduras Cadastradas</h3>
        @can('admin')
        <a href="/fechaduras/create" class="btn btn-primary">
            + Nova Fechadura
        </a>
        @endcan
    </div>
    
    <div class="card-body">
        @can('admin')
            <div class="alert alert-info mb-3">
                <i class="fas fa-shield-alt"></i> <strong>Administrador geral</strong> 
            </div>
        @else
            @if($fechaduras->isNotEmpty())
                <div class="alert alert-warning mb-3">
                    <i class="fas fa-user-shield"></i> <strong>Administrador de fechadura</strong> 
                </div>
            @endif
        @endcan

        @if($fechaduras->isEmpty())
            <div class="alert alert-info text-center">
                <i class="fas fa-door-open fa-2x mb-3"></i>
                @can('admin')
                    <h4>Nenhuma fechadura cadastrada</h4>
                    <p class="mb-0">Clique em "Nova Fechadura" acima para adicionar</p>
                @else
                    <h4>Nenhuma fechadura disponível</h4>
                    <p class="mb-0">Você não é administrador de nenhuma fechadura</p>
                @endcan
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="thead-fflch" style="background-color: #002a5e; color: white;">
                    <tr>
                        <th>Local</th>
                        <th>IP</th>
                        <th>Porta</th> 
                        <th width="200px">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($fechaduras as $fechadura)
                    <tr>
                        <td>{{ $fechadura->local }}</td>
                        <td>{{ $fechadura->ip }}</td>
                        <td>{{ $fechadura->porta }}</td>
                        <td class="d-flex gap-2">
                            <a href="/fechaduras/{{ $fechadura->id }}" class="btn btn-sm btn-info">Ver</a>
                            
                            @can('admin')
                                <a href="/fechaduras/{{ $fechadura->id }}/edit" class="btn btn-sm btn-warning">Editar</a>
                                <form action="/fechaduras/{{ $fechadura->id }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir esta fechadura?')">Excluir</button>
                                </form>
                            @endcan
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