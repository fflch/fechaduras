<form method="post" action="/fechaduras/{{ $fechadura->id }}/create_fechadura_pos">
    @csrf
    <div class="card">
        <div class="card-header"><b>Usuários da pós-graduação</b></div>
        <div class="card-body">
            <select name="setores_pos[]" class="select2 form-control" multiple="multiple">
                @foreach(\App\Services\ReplicadoService::programasPosUnidade() as $setor_pos)
                    <option value="{{ $setor_pos['codare'] }}" 
                    {{ $fechadura->areas->contains('codare', $setor_pos['codare']) ? 'selected' : '' }}>
                    {{ $setor_pos['nomare'] }}
                </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-success" style="margin-top:5px;">Atualizar setor</button>
        </div>
    </div>
</form>