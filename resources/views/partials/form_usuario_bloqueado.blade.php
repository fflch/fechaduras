@can('adminFechadura', $fechadura)
<div class="card mt-4">
    <div class="card-header">
        <b>Bloquear usuário</b>
    </div>
    <div class="card-body">
        <form method="post" action="/fechaduras/{{ $fechadura->id }}/bloquear-usuario">
            @csrf
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Número USP *</label>
                        <input type="number" class="form-control" name="codpes" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Motivo (opcional)</label>
                        <input type="text" class="form-control" name="motivo" placeholder="Ex: Matrícula trancada, afastado, etc.">
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-warning">
                <i class="fas fa-ban"></i> Bloquear Usuário
            </button>
        </form>
    </div>
</div>
@endcan