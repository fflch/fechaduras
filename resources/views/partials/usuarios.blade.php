<div class="mt-2">
    <div class="card">
        <div class="card-header" type="button" data-toggle="collapse" data-target="#collapse{{ $fechadura->id }}"
            aria-expanded="false" aria-controls="collapse{{ $fechadura->id }}">
            <b>Usuários inseridos na fechadura</b>
            <i class="fas fa-plus-square"></i>
        </div>
    </div>
    <div class="collapse" id="collapse{{ $fechadura->id }}">
        <div class="card card-body">
            <ul class="list-group">
                @foreach ($fechadura->usuarios as $usuario)
                    <li class="list-group-item">
                        @can('adminFechadura', $fechadura)
                        <form method="post" action="/fechaduras/{{ $fechadura->id }}/delete_user/{{ $usuario->id }}">
                            {{ $usuario->codpes }} - {{ $usuario->name }}
                            @csrf
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Tem Certeza?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @else
                        {{ $usuario->codpes }} - {{ $usuario->name }}
                        @endcan
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>