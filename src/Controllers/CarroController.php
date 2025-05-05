<?php

namespace Src\Controllers;

use Src\Services\CarroService;
use HTMLPurifier;
use Respect\Validation\Exceptions\ValidationException;

class CarroController{
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
        
        try {
            // Sanitiza a descrição
            if (isset($input['descricao'])) {
                $input['descricao'] = $this->purifier->purify($input['descricao']);
            }
            // Validação de dados
            $this->carroService->validar($input);

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

        if (isset($input['descricao'])) {
            $input['descricao'] = $this->purifier->purify($input['descricao']);
        }

        try {
            $this->carroService->validar($input);
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
}