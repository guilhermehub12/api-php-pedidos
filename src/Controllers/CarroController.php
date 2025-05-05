<?php

namespace Src\Controllers;

use Src\Services\CarroService;
use HTMLPurifier;
use Respect\Validation\Exceptions\ValidationException;

class CarroController
{
    private CarroService $carroService;

    public function __construct(CarroService $carroService, HTMLPurifier $purifier)
    {
        $this->carroService = $carroService;
        $this->purifier = $purifier;
    }

    public function listar(array $query): void
    {
        try {
            $carros = $this->carroService->listar($query);
            http_response_code(200);
            echo json_encode($carros);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function obterPorId(int $id): void
    {
        try {
            $data = $this->carroService->obterPorId($id);
            http_response_code(200);
            echo json_encode($data);
        } catch (\DomainException $e) {
            http_response_code($e->getCode());
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function criar(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        // Verificar arquivo de imagem
        if (empty($_FILES['imagem']) ||  $_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['error' => 'Arquivo de imagem não enviado ou erro no upload']);
            return;
        }

        try {
            // Método para processar e validar upload
            $filename = $this->processarUpload($_FILES['imagem']);
            $input = $_POST;
            $input['imagem'] = $filename;

            // Sanitiza a descrição
            if (isset($input['descricao'])) {
                $input['descricao'] = $this->purifier->purify($input['descricao']);
            }

            // Validação de dados
            $this->carroService->validar($input);

            // Cria o carro
            $id = $this->carroService->criar($input);
            http_response_code(201);
            echo json_encode(['id' => $id]);
        } catch (ValidationException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function atualizar(int $id): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        
        try {
            // Verificar atualização de arquivo de imagem
            if (!empty($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
                $input['imagem'] = $this->processarUpload($_FILES['imagem']);
            }

            // Sanitiza a descrição
            if (isset($input['descricao'])) {
                $input['descricao'] = $this->purifier->purify($input['descricao']);
            }

            $this->carroService->validarUpdate($input);
            $rows = $this->carroService->atualizar($id, $input);
            http_response_code(200);
            echo json_encode(['message' => "Carro com ID $id atualizado, $rows linhas afetadas"]);
        } catch (ValidationException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function deletar(int $id): void
    {
        try {
            $rows = $this->carroService->deletar($id);
            http_response_code(200);
            echo json_encode(['message' => "Carro com ID $id deletado, $rows linhas afetadas"]);
        } catch (\DomainException $e) {
            http_response_code($e->getCode());
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function processarUpload(array $file): string
    {
        // Tamanho máximo do arquivo
        $maxSize = 2 * 1024 * 1024; // 2MB
        if ($file['size'] > $maxSize) {
            throw new \DomainException('O tamanho do arquivo é muito grande (máx. 2MB.', 413);
        }

        // Verifica se o arquivo é uma imagem
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        $allowedExt = array_search($mimeType, [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png'
        ], true);
        if ($allowedExt === false) {
            throw new \DomainException('Tipo de arquivo não permitido.', 415);
        }

        // Gera um nome único para o arquivo
        $filename = uniqid('carro_', true) . '.' . $allowedExt;

        // Definir o diretório de upload
        $uploadDir = __DIR__ . '/../../public/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $uploadFile = $uploadDir . basename($filename);

        if (!move_uploaded_file($file['tmp_name'], $uploadFile)) {
            throw new \DomainException('Erro ao mover o arquivo para o diretório de upload.', 500);
        }

        return $filename;
    }
}
