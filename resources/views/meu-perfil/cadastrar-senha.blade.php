@extends('main')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card">
                <div class="card-header">
                    <a href="/meu-perfil" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
                <div class="card-header bg-primary text-white">
                    <h4>{{ $fechadura->local }}</h4>
                </div>
                <div class="card-body">
                    <h5 class="text-center mb-4">Cadastrar/Atualizar Senha</h5>
                    
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="/meu-perfil/senha/{{ $fechadura->id }}">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="senha">Senha (4 dígitos numéricos)</label>
                            <input type="password" class="form-control form-control-lg text-center" id="senha" name="senha" maxlength="4" minlength="4" pattern="\d{4}" inputmode="numeric" placeholder="****" required>
                            <small class="text-muted">Apenas números, 4 dígitos</small>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-save"></i> Salvar Senha
                        </button>
                    </form>

                    <div class="alert alert-warning mt-4">
                        <i class="fas fa-exclamation-triangle"></i>
                        Esta senha será usada apenas nesta fechadura específica.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection