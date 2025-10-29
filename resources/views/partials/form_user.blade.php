@can('adminFechadura', $fechadura)
<form method="post" action="/fechaduras/{{ $fechadura->id }}/create_fechadura_user">
    @csrf
    <div class="card">
        <div class="card-header"><b>Cadastrar usuários</b></div>
        <div class="card-body">
            <input class="form-control" name="codpes" value="{{ old('codpes', request()->codpes) }}" placeholder="Número USP">
            <small>Escreva sem espaço. Ex.: 419281,1471228,14528382</small><br/>
            <button class="btn btn-success"><i class="fas fa-user-plus"></i> Adicionar usuários</button>
        </div>
    </div>
</form>
@endcan