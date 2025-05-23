<div class="mt-2">
    <div class="card">
        <div class="card-header">
            <label for="setor" class="form-label"><b>Sincronização</b></label>
        </div>
        <div class="card-body">
            <label for="setores[]"><b>Setores</b></label>
            @csrf
            <select name="setores[]" class="select2 form-control" multiple="multiple">
                @foreach (\App\Services\ReplicadoService::retornaSetores() as $setor)
                    <option value="{{ $setor['codset'] }}"
                        {{ $fechadura->setores->contains('codset', $setor['codset']) ? 'selected' : '' }}>
                        {{ $setor['nomabvset'] }} - {{ $setor['nomset'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="card-body">
            <label for="codpes"><b>Adicionar usuários</b></label>
            @csrf
            
            <input class="form-control" name="codpes" value="{{ old('codpes') }}" placeholder="Número USP">
            <small>Escreva sem espaço. Ex.: 419281,1471228,14528382</small>
        </div>
        <div class="card-body">
            <button class="btn btn-primary" id="btn_sync">
                <i class="fas fa-sync-alt"></i> Sincronizar dados
            </button>
        </div>
    </div>
</div>
