@can('adminFechadura', $fechadura)
<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <b>Cadastrar usuários externos</b>
      <!--  <button type="button" class="btn btn-sm btn-outline-primary" id="adicionarUsuario"> //duplicar form para adicionar mais de um usuário ai mesmo tempo 
            <i class="fas fa-plus"></i> Adicionar outro usuário                                 //estudar para fazer com javascript
        </button> --> 
    </div>
    <div class="card-body">
        <form method="post" action="/fechaduras/{{ $fechadura->id }}/usuarios-externos" enctype="multipart/form-data" id="formUsuariosExternos">
            @csrf
            
            <div id="usuarios-container">
                <div class="usuario-item border-bottom pb-3 mb-3">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Nome Completo *</label>
                                <input type="text" class="form-control" name="nome" value="" >
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Vínculo *</label>
                                <input type="text" class="form-control" name="vinculo" value="" placeholder="Ex: Visitante, Prestador...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Foto</label>
                                <input type="file" class="form-control" name="foto" accept="image/*">
                                <small class="form-text text-muted">Formato: JPG, PNG (até 2MB)</small>
                            </div>
                        </div>
                        <div class="col-md-2"> 
                            <div class="mb-3">
                                <label class="form-label">&nbsp;</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <textarea class="form-control" name="observacao" rows="1" placeholder="Observação (opcional)"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <button class="btn btn-success"><i class="fas fa-user-plus"></i> Adicionar usuários externos</button>
        </form>
    </div>
</div>
@endcan
