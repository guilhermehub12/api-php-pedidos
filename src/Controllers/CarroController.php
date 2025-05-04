<?php

namespace Src\Controllers;

use Src\Services\CarroService;

class CarroController{
    private CarroService $carroService;

    public function __construct(CarroService $carroService)
    {
        $this->carroService = $carroService;
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
            $id = $this->carroService->criar($input);
            http_response_code(201);
            echo json_encode(['id' => $id]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function atualizar(int $id): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        try {
            $rows = $this->carroService->atualizar($id, $input);
            http_response_code(200);
            echo json_encode(['message' => "Carro com ID $id atualizado, $rows linhas afetadas"]);
        } catch (\DomainException $e) {
            http_response_code($e->getCode());
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