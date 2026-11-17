<?php

use App\Http\Controllers\BibliotecaController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BibliotecaController::class, 'dashboard'])->name('dashboard');
Route::get('/livros', [BibliotecaController::class, 'livros'])->name('livros.index');
Route::get('/livros/criar', [BibliotecaController::class, 'criarLivro'])->name('livros.create');
Route::post('/livros', [BibliotecaController::class, 'salvarLivro'])->name('livros.store');
Route::get('/livros/{livro}/editar', [BibliotecaController::class, 'editarLivro'])->name('livros.edit');
Route::put('/livros/{livro}', [BibliotecaController::class, 'atualizarLivro'])->name('livros.update');
Route::delete('/livros/{livro}', [BibliotecaController::class, 'excluirLivro'])->name('livros.destroy');
Route::get('/leitores', [BibliotecaController::class, 'leitores'])->name('leitores.index');
Route::get('/leitores/criar', [BibliotecaController::class, 'criarLeitor'])->name('leitores.create');
Route::post('/leitores', [BibliotecaController::class, 'salvarLeitor'])->name('leitores.store');
Route::get('/leitores/{leitor}/editar', [BibliotecaController::class, 'editarLeitor'])->name('leitores.edit');
Route::put('/leitores/{leitor}', [BibliotecaController::class, 'atualizarLeitor'])->name('leitores.update');
Route::delete('/leitores/{leitor}', [BibliotecaController::class, 'excluirLeitor'])->name('leitores.destroy');
Route::get('/emprestimos', [BibliotecaController::class, 'emprestimos'])->name('emprestimos.index');
Route::get('/emprestimos/criar', [BibliotecaController::class, 'criarEmprestimo'])->name('emprestimos.create');
Route::post('/emprestimos', [BibliotecaController::class, 'salvarEmprestimo'])->name('emprestimos.store');
Route::patch('/emprestimos/{emprestimo}/devolver', [BibliotecaController::class, 'devolver'])->name('emprestimos.devolver');
