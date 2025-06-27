@extends('main')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Cadastrar Foto para Usuário {{ $userId }}</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="/fechaduras/{{ $fechadura->id }}/cadastrar-foto/{{ $userId }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="foto">Selecione uma imagem</label>
                <input type="file" class="form-control-file" id="foto" name="foto" accept="image/*">
                <small class="form-text text-muted">Tamanho máximo: 2MB</small>
            </div>
            <button type="submit" class="btn btn-primary">Enviar Foto</button>
            <a href="/fechaduras/{{ $fechadura->id }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
@endsection
