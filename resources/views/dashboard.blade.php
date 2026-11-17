@extends('layouts.app')
@section('title', 'Painel')
@section('content')
<h1 class="mb-4">Painel da biblioteca</h1>
<div class="row g-3">
    @foreach(['Livros cadastrados' => $livros, 'Leitores cadastrados' => $leitores, 'Empréstimos em andamento' => $andamento, 'Empréstimos atrasados' => $atrasados] as $titulo => $total)
        <div class="col-sm-6 col-lg-3"><div class="card h-100 shadow-sm"><div class="card-body">
            <h2 class="h6 text-secondary">{{ $titulo }}</h2><p class="display-6 mb-0">{{ $total }}</p>
        </div></div></div>
    @endforeach
</div>
@endsection
