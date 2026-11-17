<?php

namespace Tests\Feature;

use App\Models\Emprestimo;
use App\Models\Leitor;
use App\Models\Livro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BibliotecaTest extends TestCase
{
    use RefreshDatabase;

    private function livro(): Livro
    {
        return Livro::create([
            'titulo' => 'Dom Casmurro',
            'autor' => 'Machado',
            'isbn' => '123',
            'quantidade' => 1,
        ]);
    }

    private function leitor(): Leitor
    {
        return Leitor::create([
            'nome' => 'Ana',
            'cpf' => '12345678900',
            'email' => 'ana@example.com',
            'telefone' => '12345678',
        ]);
    }

    public function test_paginas_e_cadastros(): void
    {
        $this->get('/')->assertOk();
        $this->post('/livros', ['autor' => 'Autor', 'isbn' => '1', 'quantidade' => 1])->assertSessionHasErrors('titulo');
        $livro = $this->livro();
        $this->post('/livros', ['titulo' => 'Outro', 'autor' => 'Autor', 'isbn' => $livro->isbn, 'quantidade' => 1])->assertSessionHasErrors('isbn');
        $this->post('/leitores', ['nome' => 'Ana', 'cpf' => '123', 'email' => 'ana@example.com', 'telefone' => '123'])->assertRedirect(route('leitores.index'));
        $this->get('/livros?busca=Machado')->assertOk()->assertSee('Dom Casmurro');
        $this->get('/leitores?busca=Ana')->assertOk()->assertSee('Ana');
        $this->get('/emprestimos/criar')->assertOk();
        $this->get(route('livros.edit', $livro))->assertOk();
    }

    public function test_emprestimo_devolucao_e_protecao_do_historico(): void
    {
        $livro = $this->livro();
        $leitor = $this->leitor();
        $dados = [
            'livro_id' => $livro->id,
            'leitor_id' => $leitor->id,
            'data_emprestimo' => today()->subDays(3)->toDateString(),
            'data_devolucao_prevista' => today()->subDay()->toDateString(),
        ];
        $this->post(route('emprestimos.store'), $dados)->assertRedirect(route('emprestimos.index'));
        $this->assertSame(0, $livro->fresh()->quantidade);
        $this->post(route('emprestimos.store'), $dados)->assertSessionHasErrors('livro_id');
        $emprestimo = Emprestimo::firstOrFail();
        $this->get('/emprestimos?atrasados=1')->assertOk()->assertSee('Dom Casmurro');
        $this->delete(route('livros.destroy', $livro))->assertSessionHas('erro');
        $this->delete(route('leitores.destroy', $leitor))->assertSessionHas('erro');
        $this->patch(route('emprestimos.devolver', $emprestimo))->assertRedirect(route('emprestimos.index'));
        $this->assertSame(1, $livro->fresh()->quantidade);
        $this->assertSame('Devolvido', $emprestimo->fresh()->status);
        $this->patch(route('emprestimos.devolver', $emprestimo))->assertSessionHasErrors('emprestimo');
        $this->assertSame(1, $livro->fresh()->quantidade);
        $this->get('/emprestimos?atrasados=1')->assertDontSee('Dom Casmurro');
    }

    public function test_data_prevista_nao_pode_ser_anterior(): void
    {
        $livro = $this->livro();
        $leitor = $this->leitor();
        $this->post(route('emprestimos.store'), [
            'livro_id' => $livro->id,
            'leitor_id' => $leitor->id,
            'data_emprestimo' => today()->toDateString(),
            'data_devolucao_prevista' => today()->subDay()->toDateString(),
        ])->assertSessionHasErrors('data_devolucao_prevista');
        $this->assertSame(1, $livro->fresh()->quantidade);
    }
}
