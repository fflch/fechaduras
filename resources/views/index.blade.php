@extends('main')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Sistema de Fechaduras</h3>
    </div>

    <div class="card-body">
        <p class="text-muted">
            Para pedir acesso a uma fechadura, entre em contato com um dos administradores dela.
        </p>
 
        @if($fechaduras->isEmpty())
            <div class="alert alert-info">Nenhuma fechadura cadastrada.</div>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="thead-fflch" style="background-color: #002a5e; color: white;">
                        <tr>
                            <th>Fechadura</th>
                            <th>Administradores</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fechaduras as $fechadura)
                        <tr>
                            <td><strong>{{ trim($fechadura->local) }}</strong></td>
                            <td>
                                @if($fechadura->admins->isEmpty())
                                    <span class="text-muted">Nenhum administrador cadastrado.</span>
                                @else
                                    <ul class="list-unstyled mb-0">
                                        @foreach($fechadura->admins as $admin)
                                            <li class="mb-2">
                                                {{ $admin->user->name ?? 'Nome não encontrado' }}
                                                <br>
                                                @if(!empty($admin->user?->email))
                                                    <small>
                                                        <i class="fas fa-envelope"></i>
                                                        <a href="mailto:{{ $admin->user->email }}">{{ $admin->user->email }}</a>
                                                    </small>
                                                @else
                                                    <small class="text-muted">E-mail não disponível</small>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection