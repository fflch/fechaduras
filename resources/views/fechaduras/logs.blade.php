@extends("main")
@section("content")
<div class="card">
    <div class="card-header">
        <h2>{{ $fechadura->local }}</h2>
    </div>
    
    <div class="card-body">
        <p><strong>IP:</strong> {{ $fechadura->ip }}</p>
        <p><strong>Porta:</strong> {{ $fechadura->porta }}</p>
        <p><strong>Usuário API:</strong> {{ $fechadura->usuario }}</p>

        <div class="mt-4">
            <a href="/fechaduras/{{ $fechadura->id }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            @can('admin')
            <form action="/fechaduras/{{ $fechadura->id }}/logs" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sync-alt"></i> Atualizar Logs
                </button>
            </form>
            @endcan
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3>Histórico de Acessos</h3>
    </div>

    <div class="card-body">
        @if($logs->isEmpty())
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                Nenhum registro encontrado no banco de dados local.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="thead-fflch" style="background-color: #002a5e; color: white;">
                        <tr>
                            <th>Data/Hora</th>
                            <th>Nº USP</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                        <tr>
                            <td>{{ $log->datahora->format('d/m/Y H:i:s') }}</td>
                            <td>{{ $log->codpes }}</td>
                            <td>
                                @if($log->codpes == 0)
                                    <span class="badge bg-secondary">Não identificado</span>
                                @elseif($log->event == 7)
                                    <span class="badge bg-success">Liberado</span>
                                @else
                                    <span class="badge bg-danger">
                                        Negado                
                                            ({{ $log->event }})                                 
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="d-flex justify-content-center">
                    {{ $logs->links('pagination::bootstrap-4') }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection