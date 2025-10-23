@can('admin')
<div class="card mt-4">
    <div class="card-header">
        <b>Cadastrar administradores</b>
    </div>
    <div class="card-body">
        <form method="post" action="/fechaduras/{{ $fechadura->id }}/admin">
            @csrf
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Número USP *</label>
                        <input type="text" class="form-control" name="codpes">
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Cadastrar Administrador
            </button>
        </form>
    </div>
</div>
@endcan