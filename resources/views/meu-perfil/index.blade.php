@extends('main')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user-circle"></i> Meus Dados</h5>
                </div>
                <div class="card-body text-center">
                    <!-- FOTO DO USUÁRIO -->
                    <div class="mb-3">
                        <img src="{{ $user->foto_url }}" 
                             alt="Foto do usuário"
                             class="img-fluid img-thumbnail"
                             style="max-width: 200px; max-height: 200px;"
                             onerror="this.style.display='none'; document.getElementById('sem-foto-perfil').style.display='block';">
                        
                        <div id="sem-foto-perfil" class="alert alert-warning p-2" style="display: none; max-width: 200px; margin: 0 auto;">
                            <i class="fas fa-user-slash"></i> Sem foto
                        </div>
                    </div>
                    
                    <h5>{{ $user->name }}</h5>
                    <p class="text-muted">
                        <i class="fas fa-id-card"></i> Nº USP: {{ $user->codpes }}<br>
                        <i class="fas fa-envelope"></i> {{ $user->email }}
                    </p>
                    
                    @if($user->temFotoLocal())
                        <div class="small text-muted mb-2">
                            <i class="fas fa-clock"></i>
                            Foto atualizada em: {{ $user->foto_atualizada_em->format('d/m/Y H:i:s') }}
                        </div>
                    @endif

                    <!-- BOTÃO PARA ATUALIZAR FOTO LOCAL -->
                    <a href="/meu-perfil/atualizar-foto-local" class="btn btn-primary">
                        <i class="fas fa-camera"></i> Atualizar Minha Foto
                    </a>
                    <small class="d-block text-muted mt-2">
                        Esta foto será usada como padrão em todas as fechaduras
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-door-open"></i> Fechaduras com Acesso</h5>
                </div>
                <div class="card-body">
                    @if($fechaduras->isEmpty())
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Você não está cadastrado em nenhuma fechadura.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Local</th>
                                        <th>Foto</th>
                                        <th>Senha</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($fechaduras as $fechadura)
                                        @php
                                            $apiService = new \App\Services\ApiControlIdService($fechadura);
                                            $usuarios = $apiService->loadUsers();
                                            $usuarioFechadura = collect($usuarios)->firstWhere('id', (int)$user->codpes);
                                        @endphp
                                        
                                        @if($usuarioFechadura)
                                        <tr>
                                            <td>{{ $fechadura->local }}</td>
                                            <td>
                                                @if($usuarioFechadura['image_timestamp'] > 0)
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-camera"></i> Com foto
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-camera-off"></i> Sem foto
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(!empty($usuarioFechadura['password']))
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check"></i> Cadastrada
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-times"></i> Não cadastrada
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="/meu-perfil/senha/{{ $fechadura->id }}" 
                                                   class="btn btn-sm btn-warning">
                                                    <i class="fas fa-key"></i> 
                                                    {{ empty($usuarioFechadura['password']) ? 'Cadastrar' : 'Alterar' }} Senha
                                                </a>
                                            </td>
                                        </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection