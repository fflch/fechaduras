<form method="post" action="/fechaduras/{{ $fechadura->id }}/create_fechadura_setor">
    @csrf
    <div class="card">
        <div class="card-header"><b>Setores</b></div>
        <div class="card-body">
            <select name="setores[]" class="select2 form-control" multiple="multiple">
                @foreach (\App\Services\ReplicadoService::retornaSetores() as $setor)
                    <option value="{{ $setor['codset'] }}"
                        {{ $fechadura->setores->contains('codset', $setor['codset']) ? 'selected' : '' }}>
                        {{ $setor['nomabvset'] }} - {{ $setor['nomset'] }}</option>
                @endforeach
            </select>
            <button class="btn btn-success" style="margin-top:5px;"><i class="fas fa-building"></i> Atualizar setores</button>
        </div>
    </div>
</form>