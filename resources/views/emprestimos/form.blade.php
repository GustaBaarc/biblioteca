@extends('layouts.app')
@section('title', 'Novo empréstimo')
@section('content')
<h1>Novo empréstimo</h1>
@if($livros->isEmpty() || $leitores->isEmpty())<div class="alert alert-warning">Cadastre um leitor e um livro com exemplares disponíveis antes de registrar um empréstimo.</div>@endif
<form class="card card-body mt-3" method="POST" action="{{ route('emprestimos.store') }}">@csrf
<div class="mb-3"><label class="form-label" for="leitor_id">Leitor</label><select class="form-select" id="leitor_id" name="leitor_id" required><option value="">Selecione</option>@foreach($leitores as $leitor)<option value="{{ $leitor->id }}" @selected(old('leitor_id') == $leitor->id)>{{ $leitor->nome }}</option>@endforeach</select></div>
<div class="mb-3"><label class="form-label" for="livro_id">Livro</label><select class="form-select" id="livro_id" name="livro_id" required><option value="">Selecione</option>@foreach($livros as $livro)<option value="{{ $livro->id }}" @selected(old('livro_id') == $livro->id)>{{ $livro->titulo }} ({{ $livro->quantidade }} disponíveis)</option>@endforeach</select></div>
<div class="mb-3"><label class="form-label" for="data_emprestimo">Data do empréstimo</label><input class="form-control" type="date" id="data_emprestimo" name="data_emprestimo" value="{{ old('data_emprestimo', today()->toDateString()) }}" required></div>
<div class="mb-3"><label class="form-label" for="data_devolucao_prevista">Data prevista para devolução</label><input class="form-control" type="date" id="data_devolucao_prevista" name="data_devolucao_prevista" value="{{ old('data_devolucao_prevista') }}" required></div>
<div><button class="btn btn-primary">Confirmar empréstimo</button> <a class="btn btn-outline-secondary" href="{{ route('emprestimos.index') }}">Cancelar</a></div>
</form>
@endsection
