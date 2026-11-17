@extends('layouts.app')
@section('title', 'Livros')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><h1>Livros</h1><a href="{{ route('livros.create') }}" class="btn btn-primary">Novo livro</a></div>
<form method="GET" class="d-flex gap-2 mb-3"><input class="form-control" name="busca" value="{{ $busca }}" placeholder="Título ou autor" aria-label="Pesquisar título ou autor"><button class="btn btn-secondary">Pesquisar</button><a href="{{ route('livros.index') }}" class="btn btn-outline-secondary">Limpar</a></form>
<div class="table-responsive"><table class="table table-striped align-middle"><thead><tr><th>Título</th><th>Autor</th><th>Editora</th><th>Ano</th><th>ISBN</th><th>Disponíveis</th><th>Ações</th></tr></thead><tbody>
@forelse($registros as $livro)
<tr><td>{{ $livro->titulo }}</td><td>{{ $livro->autor }}</td><td>{{ $livro->editora }}</td><td>{{ $livro->ano_publicacao }}</td><td>{{ $livro->isbn }}</td><td>{{ $livro->quantidade }}</td><td>
<a class="btn btn-sm btn-outline-primary" href="{{ route('livros.edit', $livro) }}">Editar</a>
<form class="d-inline" method="POST" action="{{ route('livros.destroy', $livro) }}" onsubmit="return confirm('Excluir este livro?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Excluir</button></form>
</td></tr>
@empty<tr><td colspan="7">Nenhum livro encontrado.</td></tr>@endforelse
</tbody></table></div>{{ $registros->links() }}
@endsection
