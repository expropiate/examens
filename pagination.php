<?php
function generatePrev($page, $prevlabel) {
    if ($page == 1) {
        return "<li class='disabled'><span><a>$prevlabel</a></span></li>";
    } else if ($page == 2) {
        return "<li><span><a href='javascript:void(0);' onclick='load(1)'>$prevlabel</a></span></li>";
    } else {
        return "<li><span><a href='javascript:void(0);' onclick='load(" . ($page - 1) . ")'>$prevlabel</a></span></li>";
    }
}

function generateFirstPage($page, $adjacents) {
    if ($page > ($adjacents + 1)) {
        return "<li><a href='javascript:void(0);' onclick='load(1)'>1</a></li>";
    }
    return '';
}

function generateInterval($page, $adjacents) {
    if ($page > ($adjacents + 2)) {
        return "<li><a>...</a></li>";
    }
    return '';
}

function generatePageLinks($page, $adjacents, $tpages) {
    $pmin = ($page > $adjacents) ? ($page - $adjacents) : 1;
    $pmax = ($page < ($tpages - $adjacents)) ? ($page + $adjacents) : $tpages;
    $links = '';
    
    for ($i = $pmin; $i <= $pmax; $i++) {
        if ($i == $page) {
            $links .= "<li class='active'><a>$i</a></li>";
        } else {
            $links .= "<li><a href='javascript:void(0);' onclick='load(" . $i . ")'>$i</a></li>";
        }
    }
    
    return $links;
}

function generateLastPage($page, $tpages, $adjacents) {
    if ($page < ($tpages - $adjacents)) {
        return "<li><a href='javascript:void(0);' onclick='load($tpages)'>$tpages</a></li>";
    }
    return '';
}

function generateNext($page, $tpages, $nextlabel) {
    if ($page < $tpages) {
        return "<li><span><a href='javascript:void(0);' onclick='load(" . ($page + 1) . ")'>$nextlabel</a></span></li>";
    } else {
        return "<li class='disabled'><span><a>$nextlabel</a></span></li>";
    }
}

function paginate($reload, $page, $tpages, $adjacents) {
    $prevlabel = "&lsaquo; Prev";
    $nextlabel = "Next &rsaquo;";
    $out = '<ul class="pagination pagination-large">';

    // Generar el enlace de la página anterior
    $out .= generatePrev($page, $prevlabel);

    // Generar el enlace para la primera página
    $out .= generateFirstPage($page, $adjacents);

    // Generar el intervalo de páginas
    $out .= generateInterval($page, $adjacents);

    // Generar los enlaces de las páginas intermedias
    $out .= generatePageLinks($page, $adjacents, $tpages);

    // Generar un intervalo si es necesario
    if ($page < ($tpages - $adjacents - 1)) {
        $out .= "<li><a>...</a></li>";
    }

    // Generar el enlace para la última página
    $out .= generateLastPage($page, $tpages, $adjacents);

    // Generar el enlace de la página siguiente
    $out .= generateNext($page, $tpages, $nextlabel);

    $out .= "</ul>";
    return $out;
}
?>
