<div class="mt-2">
    <div class="card">
        <div class="card-header" type="button" data-toggle="collapse" data-target="#collapseAdmins{{ $fechadura->id }}"
            aria-expanded="false" aria-controls="collapseAdmins{{ $fechadura->id }}">
            <b>Administradores cadastrados nesta fechadura</b>
            <i class="fas fa-plus-square"></i>
        </div>
    </div>
    <div class="collapse" id="collapseAdmins{{ $fechadura->id }}">
        <div class="card card-body">
            @if($admins->isEmpty())
                <div class="alert alert-info text-center mb-0">
                    Nenhum administrador cadastrado.
                </div>
            @else
                <ul class="list-group">
                    @foreach ($admins as $admin)
                        <li class="list-group-item">
                            @can('admin')
                            <form method="post" action="/fechaduras/{{ $fechadura->id }}/admin/{{ $admin->id }}" class="d-inline">
                                {{ $admin->codpes }} - {{ $admin->user->name ?? 'Nome não encontrado' }}
                                <br>
                                <small class="text-muted">
                                    Cadastrado por: {{ $admin->cadastradoPor->name ?? 'Sistema' }} | 
                                    {{ $admin->created_at->format('d/m/Y H:i') }}
                                </small>
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm ml-2" onclick="return confirm('Tem Certeza?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @else
                            <div>
                                {{ $admin->codpes }} - {{ $admin->user->name ?? 'Nome não encontrado' }}
                                <br>
                                <small class="text-muted">
                                    Cadastrado por: {{ $admin->cadastradoPor->name ?? 'Sistema' }} | 
                                    {{ $admin->created_at->format('d/m/Y H:i') }}
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