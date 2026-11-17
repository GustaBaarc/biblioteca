@extends('layouts.app')
@section('title', 'Leitores')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><h1>Leitores</h1><a href="{{ route('leitores.create') }}" class="btn btn-primary">Novo leitor</a></div>
<form method="GET" class="d-flex gap-2 mb-3"><input class="form-control" name="busca" value="{{ $busca }}" placeholder="Nome" aria-label="Pesquisar nome"><button class="btn btn-secondary">Pesquisar</button><a href="{{ route('leitores.index') }}" class="btn btn-outline-secondary">Limpar</a></form>
<div class="table-responsive"><table class="table table-striped align-middle"><thead><tr><th>Nome</th><th>CPF</th><th>E-mail</th><th>Telefone</th><th>Ações</th></tr></thead><tbody>
@forelse($registros as $leitor)
<tr><td>{{ $leitor->nome }}</td><td>{{ $leitor->cpf }}</td><td>{{ $leitor->email }}</td><td>{{ $leitor->telefone }}</td><td>
<a class="btn btn-sm btn-outline-primary" href="{{ route('leitores.edit', $leitor) }}">Editar</a>
<form class="d-inline" method="POST" action="{{ route('leitores.destroy', $leitor) }}" onsubmit="return confirm('Excluir este leitor?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Excluir</button></form>
</td></tr>
@empty<tr><td colspan="5">Nenhum leitor encontrado.</td></tr>@endforelse
</tbody></table></div>{{ $registros->links() }}
@endsection
