@extends('main')

@section('content')
</div>
<div class="card">
    <div class="card-header">
        <h3>Cadastrar Senha para Usuário {{ $userId }}</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="/fechaduras/{{ $fechadura->id }}/cadastrar-senha/{{ $userId }}">
            @csrf
            <div class="form-group">
                <label for="senha">Senha (4 dígitos numéricos)</label>
                <input type="password" class="form-control" id="senha" name="senha" 
                       required digits:4 numeric maxlength="4" minlength="4">
            </div>
            <button type="submit" class="btn btn-primary">Cadastrar Senha</button>
            <a href="/fechaduras/{{ $fechadura->id }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
@endsection