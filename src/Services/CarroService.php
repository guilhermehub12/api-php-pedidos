<?php

namespace Src\Services;

use Src\Models\Carro;
use Psr\Log\LoggerInterface;
use Respect\Validation\Validator as v;

class CarroService
{
    private Carro $carro;
    private LoggerInterface $logger;

    public function __construct(Carro $carro, LoggerInterface $logger)
    {
        $this->carro = $carro;
        $this->logger = $logger;
    }

    public function validar(array $data): void
    {
        // Validação dos dados
        $validator = v::key('imagem', v::stringType()->notEmpty()->assert($data['imagem']))
            ->key('nome', v::stringType()->notEmpty()->length(3, 100)->assert($data['nome']))
            ->key('descricao', v::stringType()->notEmpty()->length(0, 255)->assert($data['descricao']))
            ->key('preco', v::stringType()->assert($data['preco']))
            ->key('fabricante', v::stringType()->notEmpty()->assert($data['fabricante']))
            ->key('marca', v::stringType()->notEmpty()->assert($data['marca']))
            ->key('estado', v::in(['novo', 'usado'])->assert($data['estado']))
            ->key('tipo', v::in(['hatch', 'sedan', 'SUV'])->assert($data['tipo']))
            ->key('ano', v::intVal()->between(1900, date("Y"))->assert($data['ano']));
        // Validação de imagem
        $validator->key('imagem', v::callback(function ($value) {
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            $extension = pathinfo($value, PATHINFO_EXTENSION);
            return in_array($extension, $allowedExtensions);
        }));
        // Validação de preço
        $validator->key('preco', v::callback(function ($value) {
            return preg_match('/^\d+(\.\d{1,2})?$/', $value);
        }));
        // Validação de ano
        $validator->key('ano', v::callback(function ($value) {
            return preg_match('/^\d{4}$/', $value);
        }));
        // Validação de estado
        $validator->key('estado', v::callback(function ($value) {
            return in_array($value, ['novo', 'usado']);
        }));
        // Validação de tipo
        $validator->key('tipo', v::callback(function ($value) {
            return in_array($value, ['hatch', 'sedan', 'SUV']);
        }));
        // Validação de fabricante
        $validator->key('fabricante', v::callback(function ($value) {
            return preg_match('/^[a-zA-Z0-9\s]+$/', $value);
        }));
        // Validação de marca
        $validator->key('marca', v::callback(function ($value) {
            return preg_match('/^[a-zA-Z0-9\s]+$/', $value);
        }));

        if (!$validator->validate($data)) {
            $this->logger->error("Dados inválidos: " . json_encode($data));
            throw new \DomainException("Dados inválidos", 422);
        }
    }

    public function validarUpdate(array $data): void
    {
        // Validar dados que irão ser atualizados
        if (array_key_exists('imagem', $data)) {
            v::stringType()->notEmpty()->assert($data['imagem']);
        }
        if (array_key_exists('nome', $data)) {
            v::stringType()->length(3,100)->assert($data['nome']);
        }

        if (array_key_exists('descricao', $data)) {
            v::stringType()->length(0,255)->assert($data['descricao']);
        }
        if (array_key_exists('preco', $data)) {
            v::stringType()->assert($data['preco']);
        }
        if (array_key_exists('fabricante', $data)) {
            v::stringType()->notEmpty()->assert($data['fabricante']);
        }
        if (array_key_exists('marca', $data)) {
            v::stringType()->notEmpty()->assert($data['marca']);
        }
        if (array_key_exists('estado', $data)) {
            v::in(['novo', 'usado'])->assert($data['estado']);
        }
        if (array_key_exists('tipo', $data)) {
            v::in(['hatch', 'sedan', 'SUV'])->assert($data['tipo']);
        }
        if (array_key_exists('ano', $data)) {
            v::intVal()->between(1900, date("Y"))->assert($data['ano']);
        }
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
