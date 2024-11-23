<?php
// Genera los enlaces para la navegación (anterior, siguiente, etc.)
function generatePrev($page, $prevlabel) {
    if ($page == 1) {
        return "<li class='disabled'><span><a>$prevlabel</a></span></li>";
    }
    return "<li><span><a href='javascript:void(0);' onclick='load(" . ($page - 1) . ")'>$prevlabel</a></span></li>";
}

// Genera el enlace para la primera página
function generateFirstPage($page, $adjacents) {
    return ($page > ($adjacents + 1)) ? "<li><a href='javascript:void(0);' onclick='load(1)'>1</a></li>" : '';
}

// Genera el intervalo de páginas (los puntos suspensivos)
function generateInterval($page, $adjacents) {
    return ($page > ($adjacents + 2)) ? "<li><a>...</a></li>" : '';
}

// Genera los enlaces para las páginas intermedias
function generatePageLinks($page, $adjacents, $tpages) {
    $pmin = max(1, $page - $adjacents);
    $pmax = min($tpages, $page + $adjacents);
    $links = '';

    for ($i = $pmin; $i <= $pmax; $i++) {
        $links .= ($i == $page) 
            ? "<li class='active'><a>$i</a></li>"
            : "<li><a href='javascript:void(0);' onclick='load($i)'>$i</a></li>";
    }

    return $links;
}

// Genera el enlace para la última página
function generateLastPage($page, $tpages, $adjacents) {
    return ($page < ($tpages - $adjacents)) ? "<li><a href='javascript:void(0);' onclick='load($tpages)'>$tpages</a></li>" : '';
}

// Genera el enlace para la página siguiente
function generateNext($page, $tpages, $nextlabel) {
    if ($page < $tpages) {
        return "<li><span><a href='javascript:void(0);' onclick='load(" . ($page + 1) . ")'>$nextlabel</a></span></li>";
    }
    return "<li class='disabled'><span><a>$nextlabel</a></span></li>";
}

function paginate($reload, $page, $tpages, $adjacents) {
    $prevlabel = "&lsaquo; Prev";
    $nextlabel = "Next &rsaquo;";
    $out = '<ul class="pagination pagination-large">';

    // Concatenar los enlaces generados por las funciones
    $out .= generatePrev($page, $prevlabel);
    $out .= generateFirstPage($page, $adjacents);
    $out .= generateInterval($page, $adjacents);
    $out .= generatePageLinks($page, $adjacents, $tpages);
    $out .= generateInterval($page, $adjacents);  // Intervalo al final
    $out .= generateLastPage($page, $tpages, $adjacents);
    $out .= generateNext($page, $tpages, $nextlabel);

    $out .= "</ul>";
    return $out;
}
?>
