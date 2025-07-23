@extends("main")
@section("content")

<div class="card">
    <div class="card-header bg-danger text-white">
        <h3>Erro de Conexão</h3>
    </div>
    
    <div class="card-body">
        <div class="alert alert-danger">
            <h4 class="alert-heading">Não foi possível conectar à fechadura!</h4>
            <p>O sistema não conseguiu estabelecer conexão com a fechadura no endereço <strong>{{ $fechadura->ip }}</strong>.</p>
            <p><strong>Possíveis causas:</strong></p>
            <ul>
                <li>Endereço IP incorreto</li>
                <li>Credenciais de acesso inválidas</li>
                <li>Fechadura desligada ou sem conexão de rede</li>
            </ul>
            <hr>
            <p class="mb-0">Por favor, verifique as informações ou tente novamente mais tarde.</p>
        </div>

        <div class="d-flex gap-3 mt-4">
            <a href="/fechaduras" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>

            <a href="/fechaduras/{{ $fechadura->id }}/edit" class="btn btn-warning">
                <i class="fas fa-edit"></i> Editar Credenciais
            </a>
            
            <form method="POST" action="/fechaduras/{{ $fechadura->id }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir esta fechadura?')">
                    <i class="fas fa-trash"></i> Excluir Fechadura
                </button>
            </form>          
        </div>
    </div>
</div>

@endsection