@extends('layout')

@section('cabecalho')
Séries
@endsection

@section('conteudo')

@include('mensagem', ['mensagem' => $mensagem])

@auth
<a href="{{ route('form_criar_serie') }}" class="btn btn-dark mb-2">Adicionar</a>
<a href="{{ route('series_favoritas') }}" class="btn btn-dark mb-2">Minha séries favoritas</a>
@endauth

<ul class="list-group">
    @foreach($series as $serie)
    <?php $eFavorita = false; ?>

         @auth
         @foreach($favoritas as $favorita)
            <?php if($serie->id == $favorita->serie_id) { $eFavorita = true; } ?>
         @endforeach
        @endauth

    <li class="list-group-item d-flex justify-content-between align-items-center">

      
        <div>
            <img src="{{ $serie->capa_url }}" class="img-thumbnail" height="100px" width="100px">
            <span id="nome-serie-{{ $serie->id }}">{{ $serie->nome }}
            </span>            
        </div>  
        <div>            
            <span id="nome-categoria-{{ $serie->categoria_id }}">{{ $serie->categoriaNome }}
            </span>        
        </div>  
       

       
        <div class="input-group w-50" hidden id="input-nome-serie-{{ $serie->id }}">
            <input type="text" class="form-control" value="{{ $serie->nome }}">
            <div class="input-group-append">
                <button class="btn btn-primary" onclick="editarSerie(({{ $serie->id }}))">
                    <i class="fas fa-check"></i>
                </button>
                @csrf
            </div>
        </div>


        <span class="d-flex">

            @auth
                <button title="Favoritar" <?php if($eFavorita) { ?> hidden <?php } ?> id="favoritar-{{ $serie->id }}" class="btn btn-dark btn-sm mr-1" onclick="favoritaSerie(({{ $serie->id }}))">
                    <i class="fas fa-heart" ></i>
                </button>
                <button title="Desfavoritar" <?php if(!$eFavorita) { ?> hidden <?php } ?> id="desfavoritar-{{ $serie->id }}" class="btn btn-danger btn-sm mr-1" onclick="desfavoritaSerie(({{ $serie->id }}))">
                    <i class="fas fa-heart" ></i>
                </button>
        
                <button class="btn btn-info btn-sm mr-1" onclick="toggleInput({{ $serie->id }})">
                    <i class="fas fa-edit"></i>
                </button>
            @endauth

            <a href="/series/{{ $serie->id }}/temporadas" class="btn btn-info btn-sm mr-1">
                <i class="fas fa-external-link-alt"></i>
            </a>

            @auth
            <form method="post" action="/series/{{ $serie->id }}"
                  onsubmit="return confirm('Tem certeza que deseja remover {{ addslashes($serie->nome) }}?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger btn-sm">
                    <i class="far fa-trash-alt"></i>
                </button>
            </form>
            @endauth
        </span>
    </li>
    @endforeach
</ul>
<script>
    function trocaFavoritar(serieId){
        const botaoFavoritar = document.getElementById(`favoritar-${serieId}`);
        const botaoDesfavoritar = document.getElementById(`desfavoritar-${serieId}`);
        if (botaoFavoritar.hasAttribute('hidden')) {
            botaoFavoritar.removeAttribute('hidden');
            botaoDesfavoritar.hidden = true;
        } else {
            botaoDesfavoritar.removeAttribute('hidden');
            botaoFavoritar.hidden = true;
        }
    }
    function favoritaSerie(serieId){
       let formData = new FormData();
       const token = document
            .querySelector('input[name="_token"]')
            .value;
        formData.append('_token', token);

        const url = `/series/${serieId}/favoritaSerie`;
        fetch(url, {
            method: 'POST',
            body: formData,
        }).then(() => {
            trocaFavoritar(serieId);
        });
    }

    function desfavoritaSerie(serieId){
       let formData = new FormData();
       const token = document
            .querySelector('input[name="_token"]')
            .value;
        formData.append('_token', token);

        const url = `/series/${serieId}/desfavoritaSerie`;
        fetch(url, {
            method: 'POST',
            body: formData,
        }).then(() => {
            trocaFavoritar(serieId);
        });
    }

    function toggleInput(serieId) {
        const nomeSerieEl = document.getElementById(`nome-serie-${serieId}`);
        const inputSerieEl = document.getElementById(`input-nome-serie-${serieId}`);
        if (nomeSerieEl.hasAttribute('hidden')) {
            nomeSerieEl.removeAttribute('hidden');
            inputSerieEl.hidden = true;
        } else {
            inputSerieEl.removeAttribute('hidden');
            nomeSerieEl.hidden = true;
        }
    }
    function editarSerie(serieId) {
        let formData = new FormData();
        const nome = document
            .querySelector(`#input-nome-serie-${serieId} > input`)
            .value;
        const token = document
            .querySelector('input[name="_token"]')
            .value;
        formData.append('nome', nome);
        formData.append('_token', token);
        const url = `/series/${serieId}/editaNome`;
        fetch(url, {
            method: 'POST',
            body: formData
        }).then(() => {
            toggleInput(serieId);
            document.getElementById(`nome-serie-${serieId}`).textContent = nome;
        });
    }
</script>
@endsection