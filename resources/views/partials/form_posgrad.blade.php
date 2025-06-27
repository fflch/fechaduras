<form method="post" action="/fechaduras/{{ $fechadura->id }}/create_fechadura_pos">
    @csrf
    <div class="card">
        <div class="card-header"><b>Cadastrar áreas da pós-graduação</b></div>
        <div class="card-body">
            <select name="areas[]" class="select2 form-control" multiple="multiple">
                @foreach($programas as $programa)
                    <option value="{{ $programa['codare'] }}"
                    {{ $fechadura->areas->contains('codare', $programa['codare']) ? 'selected' : '' }}>
                    {{ $programa['nomare'] }}
                </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-success" style="margin-top:5px;">Atualizar setor</button>
        </div>
    </div>
</form>
