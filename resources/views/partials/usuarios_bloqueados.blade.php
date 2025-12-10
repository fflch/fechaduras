<div class="mt-2">
    <div class="card">
        <div class="card-header" type="button" data-toggle="collapse" data-target="#collapseBloqueados{{ $fechadura->id }}"
            aria-expanded="false" aria-controls="collapseBloqueados{{ $fechadura->id }}">
            <b>Usuários bloqueados ({{ $fechadura->usuariosBloqueados->count() }})</b>
            <i class="fas fa-plus-square"></i>
        </div>
    </div>
    <div class="collapse" id="collapseBloqueados{{ $fechadura->id }}">
        <div class="card card-body">
            @if($fechadura->usuariosBloqueados->isEmpty())
                <div class="alert alert-info text-center mb-0">
                    Nenhum usuário bloqueado.
                </div>
            @else
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Nº USP</th>
                            <th>Nome</th>
                            <th>Motivo</th>
                            <th>Bloqueado por</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($fechadura->usuariosBloqueados as $bloqueado)
                            
                            <tr>
                                <td>{{ $bloqueado->codpes }}</td>
                                <td>{{ $bloqueado->usuario->name }}</td>
                                <td>{{ $bloqueado->motivo ?? 'Sem motivo' }}</td>
                                <td>{{ $bloqueado->bloqueadoPor->name ?? 'Sistema' }}</td>
                                <td>{{ $bloqueado->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <form method="POST" action="/fechaduras/{{ $fechadura->id }}/bloquear-usuario/{{ $bloqueado->id }}"
                                          class="d-inline"
                                          onsubmit="return confirm('Deseja realmente desbloquear este usuário?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-check"></i> Desbloquear
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>