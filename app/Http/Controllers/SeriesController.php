<?php
namespace App\Http\Controllers;
use App\Events\NovaSerie;
use App\Http\Requests\SeriesFormRequest;
use App\Serie;
use App\Services\CriadorDeSerie;
use App\Services\RemovedorDeSerie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Favorita;


class SeriesController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }// apenas para adicionar uma autenticação direto no construtor
    public function index(Request $request) {
        $series = Serie::query()
            ->orderBy('nome')
            ->get();
        $mensagem = $request->session()->get('mensagem');

        if(Auth::check()){
            $usuario_id = Auth::user()->id;
            $favoritas = Favorita::where('usuario_id', $usuario_id)->get();
            return view('series.index', compact('series', 'mensagem', 'favoritas', 'usuario_id'));
        }

        return view('series.index', compact('series', 'mensagem'));
    }//faz uma query no banco buscando os dados da Serie e exibindo na view

    public function create()
    {
        return view('series.create');
    }//Adiciona uma serie ao dar input nos dados atraves da view

    public function store(
        SeriesFormRequest $request,
        CriadorDeSerie $criadorDeSerie
    ) {
        $capa = null;
        if ($request->hasFile('capa')) 
        {
            $capa = $request->file('capa')->store('serie');
            //dd($request->file('capa')->store('serie'));
        }
        
        $serie = $criadorDeSerie->criarSerie(
            $request->nome,
            $request->qtd_temporadas,
            $request->ep_por_temporada,
            $capa
        );
      $eventoNovaSerie = new NovaSerie(
          $request->nome, 
          $request->qtd_temporadas, 
          $request->ep_por_temporada
      );//cria uma evento 
      event($eventoNovaSerie);//emite o evento para envio de email   
    $request->session()
        ->flash(
            'mensagem',
            "Série {$serie->nome} e suas temporadas e episódios criados com sucesso"
        );

    return redirect()->route('listar_series');
    }//depois que chma o servico para criar a serie redireciona para aviwe index de listar serie

    public function destroy(Request $request, RemovedorDeSerie $removedorDeSerie)
    {
        $nomeSerie = $removedorDeSerie->removerSerie($request->id);
        $request->session()
            ->flash(
                'mensagem',
                "Série $nomeSerie removida com sucesso"
            );
        return redirect()->route('listar_series');
    }
    public function editaNome(int $id, Request $request)
    {
        $serie = Serie::find($id);
        $novoNome = $request->nome;
        $serie->nome = $novoNome;
        $serie->save();
    }

    public function favoritaSerie(int $id){

        $serie_id = $id;
        $usuario_id = Auth::user()->id;

        $result = Favorita::create([
            'serie_id' => $serie_id,
            'usuario_id' => $usuario_id
        ]);
    }

    public function listarSeriesFavoritas(){

        $usuario_id = Auth::user()->id;

        $series = Favorita::where('usuario_id', '=', $usuario_id)
            ->join('series', 'series.id', '=', 'favoritas.serie_id')->get();

         return view('series.series_favoritas', compact('series'));

    }

    public function desfavoritaSerie(int $id)
    {
      
        $usuario_id = Auth::user()->id;
        $result = Favorita::where('serie_id', '=' ,$id)
        ->where('usuario_id', '=', $usuario_id)
        ->delete();
    }
}

