<div class="mt-2">
    <div class="card">
        <div class="card-header" type="button" data-toggle="collapse" data-target="#collapseExternos{{ $fechadura->id }}"
            aria-expanded="false" aria-controls="collapseExternos{{ $fechadura->id }}">
            <b>Usuários externos cadastrados</b>
            <i class="fas fa-plus-square"></i>
        </div>
    </div>
    <div class="collapse" id="collapseExternos{{ $fechadura->id }}">
        <div class="card card-body">
            @if($usuariosExternos->isEmpty())
                <div class="alert alert-info text-center mb-0">
                    Nenhum usuário externo cadastrado.
                </div>
            @else
                <ul class="list-group">
                    @foreach ($usuariosExternos as $usuario)
                        <li class="list-group-item">
                            @can('adminFechadura', $fechadura)
                            <form method="post" action="/fechaduras/{{ $fechadura->id }}/delete_usuario_externo/{{ $usuario->id }}" class="d-inline">
                                <strong>{{ $usuario->nome }}</strong>
                                <br>
                                <small class="text-muted">
                                    Cadastrado por: {{ $usuario->cadastradoPor->name }} | 
                                    {{ $usuario->created_at->format('d/m/Y H:i') }}
                                </small>
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm ml-2" onclick="return confirm('Tem Certeza?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @else
                            <div>
                                <strong>{{ $usuario->nome }}</strong>
                                <br>
                                <small class="text-muted">
                                    Cadastrado por: {{ $usuario->cadastradoPor->name }} | 
                                    {{ $usuario->created_at->format('d/m/Y H:i') }}
                                </small>
                            </div>
                            @endcan
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>