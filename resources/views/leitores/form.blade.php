@extends('layouts.app')
@section('title', $leitor->exists ? 'Editar leitor' : 'Novo leitor')
@section('content')
<h1>{{ $leitor->exists ? 'Editar leitor' : 'Novo leitor' }}</h1>
<form method="POST" action="{{ $leitor->exists ? route('leitores.update', $leitor) : route('leitores.store') }}" class="card card-body mt-3">
@csrf @if($leitor->exists) @method('PUT') @endif
@foreach(['nome' => 'Nome', 'cpf' => 'CPF', 'email' => 'E-mail', 'telefone' => 'Telefone'] as $campo => $rotulo)
<div class="mb-3"><label class="form-label" for="{{ $campo }}">{{ $rotulo }}</label><input class="form-control" id="{{ $campo }}" name="{{ $campo }}" type="{{ $campo === 'email' ? 'email' : 'text' }}" value="{{ old($campo, $leitor->$campo) }}" required @if($campo === 'cpf') maxlength="14" @endif></div>
@endforeach
<div><button class="btn btn-primary">Salvar</button> <a class="btn btn-outline-secondary" href="{{ route('leitores.index') }}">Cancelar</a></div>
</form>
@endsection
