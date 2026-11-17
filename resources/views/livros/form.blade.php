@extends('layouts.app')
@section('title', $livro->exists ? 'Editar livro' : 'Novo livro')
@section('content')
<h1>{{ $livro->exists ? 'Editar livro' : 'Novo livro' }}</h1>
<form method="POST" action="{{ $livro->exists ? route('livros.update', $livro) : route('livros.store') }}" class="card card-body mt-3">
@csrf @if($livro->exists) @method('PUT') @endif
@foreach(['titulo' => 'Título', 'autor' => 'Autor', 'editora' => 'Editora', 'ano_publicacao' => 'Ano de publicação', 'isbn' => 'ISBN', 'quantidade' => 'Quantidade disponível'] as $campo => $rotulo)
<div class="mb-3"><label class="form-label" for="{{ $campo }}">{{ $rotulo }}</label>
<input class="form-control" id="{{ $campo }}" name="{{ $campo }}" type="{{ in_array($campo, ['ano_publicacao', 'quantidade']) ? 'number' : 'text' }}" value="{{ old($campo, $livro->$campo) }}" @if(!in_array($campo, ['editora', 'ano_publicacao'])) required @endif @if($campo === 'quantidade') min="0" @endif @if($campo === 'ano_publicacao') min="1000" max="{{ date('Y') }}" @endif>
</div>
@endforeach
<div><button class="btn btn-primary">Salvar</button> <a class="btn btn-outline-secondary" href="{{ route('livros.index') }}">Cancelar</a></div>
</form>
@endsection
