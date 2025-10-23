<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Services\LockSessionService;

class TestarFotoService
{
    private array $errorMessages = [
        1 => 'Erro nos parâmetros da requisição ou formato de imagem inválido.',
        2 => 'Rosto não detectado na imagem.',
        3 => 'Esta face já está cadastrada para outro usuário.',
        4 => 'Rosto não está centralizado na imagem.',
        5 => 'Rosto muito distante da câmera.',
        6 => 'Rosto muito próximo da câmera.',
        7 => 'Rosto não está posicionado corretamente (está torto).',
        8 => 'Imagem com baixa nitidez.',
        9 => 'Rosto muito próximo das bordas da imagem.'
    ];

    public function execute($fechadura, $foto, $userId = null)
    {
        // Testa a foto primeiro
        $testResult = $this->testarFoto($fechadura, $foto);
        
        if (!$testResult['success']) {
            return $testResult; // Retorna erro do teste
        }

        // Se passou no teste e tem userId, faz upload
        if ($userId) {
            return $this->fazerUploadFoto($fechadura, $userId, $foto);
        }

        return $testResult; // Apenas resultado do teste
    }

    private function testarFoto($fechadura, $foto)
    {
        $sessao = LockSessionService::conexao(
            $fechadura->ip, $fechadura->porta, $fechadura->usuario, $fechadura->senha
        );

        $url = 'http://' . $fechadura->ip . ':' . $fechadura->porta . '/user_test_image.fcgi?session=' . $sessao;

        $fileContents = is_string($foto) ? file_get_contents($foto) : file_get_contents($foto->path());

        $response = Http::timeout(30)->withHeaders([
            'Content-Type' => 'application/octet-stream'
        ])->withBody(
            $fileContents,
            'application/octet-stream'
        )->post($url);

        $data = $response->json();

        return [
            'success' => $data['success'] ?? false,
            'message' => $data['success'] ? 
                'Foto aprovada no teste de qualidade.' : 
                $this->getErrorMessage($data['errors'] ?? []),
            'scores' => $data['scores'] ?? null,
            'errors' => $data['errors'] ?? []
        ];
    }

    private function fazerUploadFoto($fechadura, $userId, $foto)
    {
        $sessao = LockSessionService::conexao(
            $fechadura->ip, $fechadura->porta, $fechadura->usuario, $fechadura->senha
        );

        $url = 'http://' . $fechadura->ip . ':' . $fechadura->porta . '/user_set_image.fcgi?user_id='. $userId ."&timestamp=".time()."&match=0&session=" . $sessao;

        $fileContents = is_string($foto) ? file_get_contents($foto) : file_get_contents($foto->path());

        $response = Http::timeout(30)->withHeaders([
            'Content-Type' => 'application/octet-stream'
        ])->withBody(
            $fileContents,
            'application/octet-stream'
        )->post($url);

        $data = $response->json();

        return [
            'success' => $data['success'] ?? false,
            'message' => $data['success'] ? 
                'Foto cadastrada com sucesso.' : 
                $this->getErrorMessage($data['errors'] ?? []),
            'scores' => $data['scores'] ?? null
        ];
    }

    private function getErrorMessage(array $errors)
    {
        if (empty($errors)) {
            return 'Erro desconhecido ao processar a foto.';
        }

        $error = $errors[0];
        return $this->errorMessages[$error['code']] ?? 'Erro ao processar a foto.';
    }
}