@extends("main")
@section("content")
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3>Nova Fechadura</h3>
        <a href="/fechaduras" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
    
    <div class="card-body">
        @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="/fechaduras">
            @csrf
            
            <div class="mb-3">
                <label class="form-label">Local</label>
                <input type="text" class="form-control" name="local" value="{{ old('local') }}" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">IP</label>
                <input type="text" class="form-control" name="ip" value="{{ old('ip') }}" placeholder="Ex: 10.172.2.143" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Porta</label>
                <input type="number" class="form-control" name="porta" value="{{ old('porta', 80) }}" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Usuário API</label>
                <input type="text" class="form-control" name="usuario" value="{{ old('usuario') }}" placeholder="Ex: admin" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Senha API</label>
                <input type="password" class="form-control" name="senha" required>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Salvar
            </button>
        </form>
    </div>
</div>
@endsection