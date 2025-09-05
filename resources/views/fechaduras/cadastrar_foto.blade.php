@extends('main')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Cadastrar Foto para Usuário {{ $userId }}</h3>
    </div>
    <div class="card-body">
        {{-- Mensagens de feedback --}}
        @if(session('alert-success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> 
                {{ session('alert-success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @if(session('alert-danger'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle"></i> 
                {{ session('alert-danger') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form method="POST" action="/fechaduras/{{ $fechadura->id }}/cadastrar-foto/{{ $userId }}" enctype="multipart/form-data" id="fotoForm">
            @csrf
            
            <div class="mb-3">
                <label for="foto" class="form-label">Selecione a imagem</label>
                <input type="file" class="form-control" id="foto" name="foto" accept="image/*" onchange="previewImage(this)">
                <div class="form-text">Tamanho máximo: 2MB • Formatos: JPG, PNG</div>
            </div>

            {{-- Preview da imagem --}}
            <div class="mb-3" id="imagePreview" style="display: none;">
                <label class="form-label">Preview:</label>
                <div>
                    <img id="preview" src="#" alt="Preview" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload"></i> Enviar Foto
                </button>
                <a href="/fechaduras/{{ $fechadura->id }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function previewImage(input) {
    const preview = document.getElementById('preview');
    const previewContainer = document.getElementById('imagePreview');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            previewContainer.style.display = 'block';
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        previewContainer.style.display = 'none';
    }
}
</script>
@endsection