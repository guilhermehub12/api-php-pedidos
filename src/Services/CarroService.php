<?php

namespace Src\Services;

use Src\Models\Carro;
use Psr\Log\LoggerInterface;

class CarroService
{
    private Carro $carro;
    private LoggerInterface $logger;

    public function __construct(Carro $carro, LoggerInterface $logger)
    {
        $this->carro = $carro;
        $this->logger = $logger;
    }

    public function listar(array $query): array
    {
        $limit = isset($query['limit']) ? (int) $query['limit'] : 10;
        $offset = isset($query['offset']) ? (int) $query['offset'] : 0;
        $this->logger->info("Listando carros com limite $limit e offset $offset");
        return $this->carro->all($limit, $offset);
    }

    public function obterPorId(int $id): array
    {
        $carro = $this->carro->findById($id);
        if (!$carro) {
            $this->logger->warning("Carro com ID $id não encontrado");
            throw new \DomainException("Carro com ID $id não encontrado", 404);
        }
        return $carro;
    }

    public function criar(array $data): int
    {
        $id = $this->carro->create($data);
        $this->logger->info("Carro criado com ID $id");
        return $id;
    }

    public function atualizar(int $id, array $data): int
    {
        $rows = $this->carro->update($id, $data);
        $this->logger->info("Carro com ID $id atualizado, $rows linhas afetadas");
        return $rows;
    }

    public function deletar(int $id): int
    {
        $rows = $this->carro->delete($id);
        if ($rows === 0) {
            $this->logger->warning("Carro com ID $id não encontrado para deletar");
            throw new \DomainException("Carro com ID $id não encontrado", 404);
        }
        $this->logger->info("Carro com ID $id deletado, $rows linhas afetadas");
        return $rows;
    }
}