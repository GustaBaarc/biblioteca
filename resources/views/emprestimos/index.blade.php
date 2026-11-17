@extends('layouts.app')
@section('title', 'Empréstimos')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><h1>Empréstimos</h1><a href="{{ route('emprestimos.create') }}" class="btn btn-primary">Novo empréstimo</a></div>
<form method="GET" class="d-flex flex-wrap gap-2 align-items-center mb-3">
<select name="status" class="form-select w-auto" aria-label="Situação"><option value="">Todas as situações</option><option value="Em andamento" @selected(request('status') === 'Em andamento')>Em andamento</option><option value="Devolvido" @selected(request('status') === 'Devolvido')>Devolvido</option></select>
<label><input type="checkbox" name="atrasados" value="1" @checked(request()->boolean('atrasados'))> Somente atrasados</label><button class="btn btn-secondary">Filtrar</button><a class="btn btn-outline-secondary" href="{{ route('emprestimos.index') }}">Limpar</a>
</form>
<div class="table-responsive"><table class="table table-striped align-middle"><thead><tr><th>Leitor</th><th>Livro</th><th>Empréstimo</th><th>Previsão</th><th>Devolução</th><th>Situação</th><th>Ações</th></tr></thead><tbody>
@forelse($registros as $emprestimo)
<tr><td>{{ $emprestimo->leitor->nome }}</td><td>{{ $emprestimo->livro->titulo }}</td><td>{{ $emprestimo->data_emprestimo->format('d/m/Y') }}</td><td>{{ $emprestimo->data_devolucao_prevista->format('d/m/Y') }}</td><td>{{ $emprestimo->data_devolucao?->format('d/m/Y') ?? '—' }}</td><td>{{ $emprestimo->status }} @if($emprestimo->status === 'Em andamento' && $emprestimo->data_devolucao_prevista->lt(today()))<span class="badge text-bg-danger">Atrasado</span>@endif</td><td>
@if($emprestimo->status === 'Em andamento')<form method="POST" action="{{ route('emprestimos.devolver', $emprestimo) }}" onsubmit="return confirm('Registrar devolução?')">@csrf @method('PATCH')<button class="btn btn-sm btn-success">Registrar Devolução</button></form>@endif
</td></tr>
@empty<tr><td colspan="7">Nenhum empréstimo encontrado.</td></tr>@endforelse
</tbody></table></div>{{ $registros->links() }}
@endsection
