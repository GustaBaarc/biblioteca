<?php

namespace App\Http\Controllers;

use App\Models\Emprestimo;
use App\Models\Leitor;
use App\Models\Livro;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BibliotecaController extends Controller
{
    public function dashboard()
    {
        return view('dashboard', [
            'livros' => Livro::count(),
            'leitores' => Leitor::count(),
            'andamento' => Emprestimo::where('status', 'Em andamento')->count(),
            'atrasados' => Emprestimo::where('status', 'Em andamento')
                ->whereNull('data_devolucao')
                ->whereDate('data_devolucao_prevista', '<', today())
                ->count(),
        ]);
    }

    public function livros(Request $request)
    {
        $busca = $request->input('busca');
        $registros = Livro::when($busca, fn ($consulta) => $consulta->where(
            fn ($consulta) => $consulta->where('titulo', 'like', "%{$busca}%")
                ->orWhere('autor', 'like', "%{$busca}%")
        ))->orderBy('titulo')->paginate(10)->withQueryString();

        return view('livros.index', compact('registros', 'busca'));
    }

    public function criarLivro()
    {
        return view('livros.form', ['livro' => new Livro]);
    }

    public function editarLivro(Livro $livro)
    {
        return view('livros.form', compact('livro'));
    }

    public function salvarLivro(Request $request)
    {
        Livro::create($this->validarLivro($request));

        return redirect()->route('livros.index')->with('sucesso', 'Livro cadastrado com sucesso!');
    }

    public function atualizarLivro(Request $request, Livro $livro)
    {
        $livro->update($this->validarLivro($request, $livro));

        return redirect()->route('livros.index')->with('sucesso', 'Livro atualizado com sucesso!');
    }

    public function excluirLivro(Livro $livro)
    {
        return $this->excluir($livro, 'livros.index');
    }

    public function leitores(Request $request)
    {
        $busca = $request->input('busca');
        $registros = Leitor::when($busca, fn ($consulta) => $consulta->where('nome', 'like', "%{$busca}%"))
            ->orderBy('nome')->paginate(10)->withQueryString();

        return view('leitores.index', compact('registros', 'busca'));
    }

    public function criarLeitor()
    {
        return view('leitores.form', ['leitor' => new Leitor]);
    }

    public function editarLeitor(Leitor $leitor)
    {
        return view('leitores.form', compact('leitor'));
    }

    public function salvarLeitor(Request $request)
    {
        Leitor::create($this->validarLeitor($request));

        return redirect()->route('leitores.index')->with('sucesso', 'Leitor cadastrado com sucesso!');
    }

    public function atualizarLeitor(Request $request, Leitor $leitor)
    {
        $leitor->update($this->validarLeitor($request, $leitor));

        return redirect()->route('leitores.index')->with('sucesso', 'Leitor atualizado com sucesso!');
    }

    public function excluirLeitor(Leitor $leitor)
    {
        return $this->excluir($leitor, 'leitores.index');
    }

    public function emprestimos(Request $request)
    {
        $consulta = Emprestimo::with(['livro', 'leitor']);
        if ($request->filled('status')) {
            $consulta->where('status', $request->input('status'));
        }
        if ($request->boolean('atrasados')) {
            $consulta->where('status', 'Em andamento')
                ->whereNull('data_devolucao')
                ->whereDate('data_devolucao_prevista', '<', today());
        }

        $registros = $consulta->orderByDesc('data_emprestimo')->paginate(10)->withQueryString();

        return view('emprestimos.index', compact('registros'));
    }

    public function criarEmprestimo()
    {
        return view('emprestimos.form', [
            'livros' => Livro::where('quantidade', '>', 0)->orderBy('titulo')->get(),
            'leitores' => Leitor::orderBy('nome')->get(),
        ]);
    }

    public function salvarEmprestimo(Request $request)
    {
        $dados = $request->validate([
            'livro_id' => 'required|exists:livros,id',
            'leitor_id' => 'required|exists:leitores,id',
            'data_emprestimo' => 'required|date',
            'data_devolucao_prevista' => 'required|date|after_or_equal:data_emprestimo',
        ]);

        DB::transaction(function () use ($dados) {
            $livro = Livro::whereKey($dados['livro_id'])->lockForUpdate()->firstOrFail();
            if ($livro->quantidade < 1) {
                throw ValidationException::withMessages(['livro_id' => 'Não há exemplares disponíveis deste livro.']);
            }

            $livro->decrement('quantidade');
            Emprestimo::create([...$dados, 'status' => 'Em andamento']);
        });

        return redirect()->route('emprestimos.index')->with('sucesso', 'Empréstimo registrado com sucesso!');
    }

    public function devolver(Emprestimo $emprestimo)
    {
        DB::transaction(function () use ($emprestimo) {
            $emprestimo = Emprestimo::whereKey($emprestimo->id)->lockForUpdate()->firstOrFail();
            if ($emprestimo->status !== 'Em andamento' || $emprestimo->data_devolucao) {
                throw ValidationException::withMessages(['emprestimo' => 'Este empréstimo já foi devolvido.']);
            }

            $livro = Livro::whereKey($emprestimo->livro_id)->lockForUpdate()->firstOrFail();
            $emprestimo->update(['data_devolucao' => today(), 'status' => 'Devolvido']);
            $livro->increment('quantidade');
        });

        return redirect()->route('emprestimos.index')->with('sucesso', 'Devolução registrada com sucesso!');
    }

    private function validarLivro(Request $request, ?Livro $livro = null): array
    {
        return $request->validate([
            'titulo' => 'required|string|max:255',
            'autor' => 'required|string|max:255',
            'editora' => 'nullable|string|max:255',
            'ano_publicacao' => 'nullable|integer|min:1000|max:'.date('Y'),
            'isbn' => ['required', 'string', 'max:30', Rule::unique('livros', 'isbn')->ignore($livro?->id)],
            'quantidade' => 'required|integer|min:0|max:4294967295',
        ]);
    }

    private function validarLeitor(Request $request, ?Leitor $leitor = null): array
    {
        return $request->validate([
            'nome' => 'required|string|max:255',
            'cpf' => ['required', 'string', 'max:14', Rule::unique('leitores', 'cpf')->ignore($leitor?->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('leitores', 'email')->ignore($leitor?->id)],
            'telefone' => 'required|string|max:30',
        ]);
    }

    private function excluir(Livro|Leitor $cadastro, string $rota)
    {
        $mensagem = 'Não é possível excluir um cadastro com empréstimos registrados.';
        if ($cadastro->emprestimos()->exists()) {
            return back()->with('erro', $mensagem);
        }

        try {
            $cadastro->delete();
        } catch (QueryException $excecao) {
            if (! in_array($excecao->getCode(), ['23000', '23503'], true)) {
                throw $excecao;
            }

            return back()->with('erro', $mensagem);
        }

        return redirect()->route($rota)->with('sucesso', 'Cadastro excluído com sucesso!');
    }
}
