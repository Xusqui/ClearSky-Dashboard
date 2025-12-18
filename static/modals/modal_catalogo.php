<?php
    // modal_catalogo.php
?>
<div id="CatalogoModal" class="modal" style="display: none;">
    <div class="modal-content">
        <button class="close" aria-label="Cerrar" id="closeCatalogoModal">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>

        <div id="CatalogoContent" class="infografia ephemeris-infografia">
            <h1 class="seeing-modal-title">📚 Catálogo Astronómico</h1>

            <div id="catalogo-buttons-container" class="button-group">
                <button id="catalogoSolarButton" class="catalogo-option-button">
                    ☀️ Sistema Solar
                </button>

                <button id="catalogoMessierButton" class="catalogo-option-button">
                    🌌 Catálogo Messier
                </button>
<details class="seccion-colapsable">
  <summary>
    <span class="icono"></span>
    Catálogo Messier. Descripción
  </summary>
<section id="catalogo-messier">
  <h2>El Catálogo Messier</h2>

  <p>
    El <strong>catálogo Messier</strong> es una lista de <strong>110 objetos astronómicos</strong>
    compilada por el astrónomo francés <strong>Charles Messier</strong> en el siglo XVIII.
    La primera versión del catálogo fue publicada en <strong>1774</strong>.
  </p>

  <h3>¿Para qué se creó?</h3>
  <p>
    Charles Messier era un apasionado <strong>cazador de cometas</strong>.
    Para evitar confundir cometas con otros objetos difusos del cielo,
    decidió catalogar aquellos objetos que <strong>no eran cometas</strong>,
    pero que podían parecerse a ellos cuando se observaban con los telescopios de la época.
  </p>

  <h3>¿Qué contiene el catálogo?</h3>
  <p>
    El catálogo Messier incluye algunos de los objetos más notables del cielo profundo,
    entre los que se encuentran:
  </p>
  <ul>
    <li>Nebulosas (por ejemplo, la <strong>Nebulosa de Orión – M42</strong>)</li>
    <li>Cúmulos estelares abiertos y globulares</li>
    <li>Galaxias (aunque en la época de Messier aún no se sabía que eran galaxias)</li>
  </ul>

  <p>
    Cada objeto se identifica mediante la letra <strong>M</strong> seguida de un número correlativo.
    Algunos ejemplos destacados son:
  </p>
  <ul>
    <li><strong>M1</strong>: la Nebulosa del Cangrejo</li>
    <li><strong>M31</strong>: la Galaxia de Andrómeda</li>
    <li><strong>M45</strong>: las Pléyades</li>
  </ul>

  <h3>Importancia astronómica</h3>
  <p>
    El catálogo Messier es uno de los catálogos más conocidos y utilizados en la
    <strong>astronomía amateur</strong>. Contiene muchos de los objetos más brillantes
    y espectaculares del cielo nocturno, varios de los cuales pueden observarse
    con <strong>binoculares</strong> o <strong>pequeños telescopios</strong>.
  </p>

  <h3>Curiosidades</h3>
  <ul>
    <li>
      Messier describió muchos objetos como “nebulosas” porque en su época
      aún no existía el concepto moderno de galaxia.
    </li>
    <li>
      Aunque su intención original no era crear un catálogo científico,
      su trabajo se convirtió en una referencia fundamental para la astronomía moderna.
    </li>
  </ul>
</section>
</details>
                <button id="catalogoCaldwellButton" class="catalogo-option-button">
                    ✨ Catálogo Caldwell
                </button>
            </div>

<details class="seccion-colapsable">
  <summary>
    <span class="icono"></span>
    Catálogo Caldwell. Descripción
  </summary>
<section id="catalogo-caldwell">
  <h2>El Catálogo Caldwell</h2>

  <p>
    El <strong>Catálogo Caldwell</strong> es una lista de <strong>109 objetos astronómicos</strong>
    compilada por el astrónomo y divulgador británico <strong>Patrick Moore</strong>
    y publicada en <strong>1995</strong>.
  </p>

  <h3>¿Por qué se creó?</h3>
  <p>
    El catálogo Caldwell fue concebido como un <strong>complemento al catálogo Messier</strong>.
    Patrick Moore observó que el catálogo Messier dejaba fuera muchos objetos
    espectaculares del cielo profundo, especialmente del <strong>hemisferio sur</strong>,
    y decidió reunirlos en una nueva lista pensada para astrónomos aficionados.
  </p>

  <h3>¿Qué contiene el catálogo?</h3>
  <p>
    El Catálogo Caldwell incluye una amplia variedad de objetos del cielo profundo,
    muchos de ellos más desafiantes que los del catálogo Messier:
  </p>
  <ul>
    <li>Nebulosas brillantes y oscuras</li>
    <li>Cúmulos estelares abiertos y globulares</li>
    <li>Galaxias cercanas y lejanas</li>
    <li>Restos de supernova</li>
  </ul>

  <p>
    Los objetos se identifican con la letra <strong>C</strong> seguida de un número.
    Algunos ejemplos conocidos son:
  </p>
  <ul>
    <li><strong>C14</strong>: la Doble Cúmulo de Perseo</li>
    <li><strong>C33</strong>: la Nebulosa de la Llama</li>
    <li><strong>C46</strong>: el Cúmulo Omega Centauri</li>
  </ul>

  <h3>Importancia astronómica</h3>
  <p>
    El Catálogo Caldwell es muy apreciado por la <strong>astronomía amateur</strong>
    porque amplía considerablemente la lista de objetos observables más allá del catálogo Messier.
    Incluye objetos visibles tanto desde el <strong>hemisferio norte</strong> como desde el
    <strong>hemisferio sur</strong>, y muchos de ellos requieren telescopios de apertura media
    o grande para apreciarse en detalle.
  </p>

  <h3>Curiosidades</h3>
  <ul>
    <li>
      El nombre “Caldwell” proviene del segundo apellido de Patrick Moore,
      ya que su apellido principal, <em>Moore</em>, no podía usarse como designación.
    </li>
    <li>
      A diferencia del catálogo Messier, el Caldwell no sigue un orden por
      posición en el cielo, sino por una selección personal de objetos destacados.
    </li>
    <li>
      Muchos objetos del catálogo Caldwell también aparecen en otros catálogos,
      como el <strong>NGC</strong> o el <strong>IC</strong>.
    </li>
  </ul>
</section>
</details>
            <div id="catalogo-data-container" class="ephemeris-card-grid">
                <p>Selecciona un catálogo para ver más detalles.</p>
            </div>
            </div>
    </div>
</div>
