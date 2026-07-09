<?php
// ============================================================
//  FACT-LINT del motor de tips (core/MesaSugerencias.php)
//  Genera una malla combinatoria CONSISTENTE de contextos, corre
//  el motor en cada uno y reprueba toda frase cuya afirmación de
//  HECHOS no esté respaldada por el dato de esa combinación.
//  Prioridad blindada: Radar (hechos) es veto absoluto.
//  Correr: php factlint.php   (0 violaciones = motor sano)
// ============================================================
define('COTIZAAPP', 1);
require __DIR__ . '/../core/MesaSugerencias.php';

$now = time();
$D = fn(int $days) => date('Y-m-d H:i:s', $now - $days * 86400);
$HOY = $D(0); $H3 = $D(3);

// ── Malla de escenarios base (consistentes internamente) ──
// Cada uno define visitas/dsv/v24/hot/ips7 de forma coherente.
$radar_states = [];
// nunca abierta
$radar_states[] = ['visitas'=>0,'dsv'=>30,'v24'=>0,'hot'=>0,'ips7'=>1,'bucket'=>null,'uv'=>null];
// abrió hoy (leyendo)
$radar_states[] = ['visitas'=>3,'dsv'=>0,'v24'=>2,'hot'=>0,'ips7'=>1,'bucket'=>null,'uv'=>0];
$radar_states[] = ['visitas'=>3,'dsv'=>0,'v24'=>1,'hot'=>1,'ips7'=>1,'bucket'=>'probable_cierre','uv'=>0];
// abrió ayer / antier (reciente, no leyendo)
$radar_states[] = ['visitas'=>3,'dsv'=>1,'v24'=>0,'hot'=>0,'ips7'=>1,'bucket'=>null,'uv'=>1];
$radar_states[] = ['visitas'=>3,'dsv'=>2,'v24'=>0,'hot'=>0,'ips7'=>1,'bucket'=>null,'uv'=>2];
// frío 4d (no reciente, no dormida)
$radar_states[] = ['visitas'=>3,'dsv'=>4,'v24'=>0,'hot'=>0,'ips7'=>1,'bucket'=>null,'uv'=>4];
$radar_states[] = ['visitas'=>3,'dsv'=>4,'v24'=>0,'hot'=>0,'ips7'=>1,'bucket'=>'enfriandose','uv'=>4];
// dormida 8d / 15d
$radar_states[] = ['visitas'=>3,'dsv'=>8,'v24'=>0,'hot'=>0,'ips7'=>1,'bucket'=>null,'uv'=>8];
$radar_states[] = ['visitas'=>3,'dsv'=>15,'v24'=>0,'hot'=>0,'ips7'=>1,'bucket'=>'enfriandose','uv'=>15];
// hot con varias personas (multi_persona), viendo recientemente
$radar_states[] = ['visitas'=>4,'dsv'=>0,'v24'=>1,'hot'=>1,'ips7'=>3,'bucket'=>'multi_persona','uv'=>0];
$radar_states[] = ['visitas'=>4,'dsv'=>1,'v24'=>0,'hot'=>1,'ips7'=>3,'bucket'=>'multi_persona','uv'=>1];
// hot validando_precio
$radar_states[] = ['visitas'=>3,'dsv'=>0,'v24'=>2,'hot'=>1,'ips7'=>1,'bucket'=>'validando_precio','uv'=>0];

// ── Declaraciones (contacto × compromiso × postura) con timestamps ──
$decls = [];
$decls[] = ['con'=>null,'com'=>null,'pos'=>null];
foreach (['no_contesta','hablamos'] as $ce) {
  $decls[] = ['con'=>['estado'=>$ce,'at'=>$HOY],'com'=>null,'pos'=>null];
  foreach ([null,'compromiso','propuse_no_quiso','sin_compromiso'] as $ke) {
    foreach ([null,'decidiendo','objecion_precio','pidio_cambios','en_el_aire'] as $pe) {
      if ($ke===null && $pe===null) continue;
      $d = ['con'=>['estado'=>$ce,'at'=>$HOY],'com'=>null,'pos'=>null];
      if ($ke) $d['com'] = ['estado'=>$ke,'at'=>$HOY];
      if ($pe) $d['pos'] = ['estado'=>$pe,'at'=>$HOY];
      $decls[] = $d;
      // variante: compromiso viejo (3d) para PACTO/dcom
      if ($ke==='compromiso') {
        $d2 = $d; $d2['com'] = ['estado'=>'compromiso','at'=>$H3];
        $decls[] = $d2;
      }
    }
  }
}
// postura sola (sin contacto)
foreach (['decidiendo','objecion_precio','pidio_cambios','en_el_aire'] as $pe) {
  $decls[] = ['con'=>null,'com'=>null,'pos'=>['estado'=>$pe,'at'=>$HOY]];
}

$edades = [2,6,12,25];
$arqs   = ['','regalador','sin_ritmo','teatro','cierre_falso','sordo_a_senales'];
$totals = [50000, 200000];

// ── Predicados de hechos (regex → requisito sobre ctx) ──
// Silencio ABSOLUTO (no relativo a una declaración)
$RX_SIL_ABS = '/ni abre la cotización|no abre la cotización|tampoco abre|sin una sola apertura|sin asomarse|ni ha abierto|desapareció|jamás ha abierto|sin haber abierto|dejó de abrir|dejó de ver la cotización|ni abre/u';
// Silencio con "desde/después del acuerdo" (relativo → validar vs reabrio)
$RX_SIL_REL = '/no ha vuelto a abrir|no ha abierto la versión|no la ha abierto|ni se ha asomado|sin que el cliente abra|no ha visto la cotización/u';
// Afirma apertura/lectura reciente EN PRESENTE
$RX_VIEW = '/está viendo la|está leyendo la|la tiene abierta|sigue leyendo la|sigue abriendo la|relee la cotización|volvió a abrir la|volvió a la cotización|reabrió la|regresó a la cotización|se asomó a la cotización|clavado en los (?:totales|precios)|está revisando (?:el precio|la cotización)|estudia (?:toda )?la cotización|revisan la cotización|viendo la cotización|la abrió (?:hoy|ayer)|abrió la cotización (?:hoy|ayer)/u';

$combos = 0; $viol = [];
function push_viol(&$viol, $regla, $frase, $ctx) {
  $key = $regla . '||' . $frase;
  if (!isset($viol[$key])) $viol[$key] = ['regla'=>$regla,'frase'=>$frase,'ctx'=>$ctx,'n'=>0];
  $viol[$key]['n']++;
}

foreach ($radar_states as $rs) {
  foreach ($decls as $dc) {
    foreach ($edades as $edad) {
      foreach ($totals as $total) {
        foreach ($arqs as $arq) {
          foreach ([0,1,2] as $seedvar) { // recorrer variantes del pool
            // recencia interna: pos más nueva que con en algunos casos ya cubierto por default (mismo HOY)
            $ctx = [
              'cot_id' => $seedvar + $edad*10 + ($rs['dsv']*100), // varía la variante
              'total' => $total, 'edad' => $edad, 'cat' => 'trabajo',
              'bucket' => $rs['bucket'], 'es_hot' => $rs['hot'],
              'pc_source' => null,
              'momentum' => null, 'fit_pct' => 0,
              'visitas' => $rs['visitas'], 'dias_sin_vista' => $rs['dsv'],
              'vistas_24h' => $rs['v24'], 'vistas_7d' => $rs['v24'] ?: ($rs['dsv']<7?2:0),
              'ips_7d' => $rs['ips7'],
              'ultima_vista_at' => $rs['uv']===null ? null : date('Y-m-d H:i:s', $now - $rs['uv']*86400),
              'revivida' => false, 'milagro' => false,
              'contacto' => $dc['con'], 'compromiso' => $dc['com'], 'postura_decl' => $dc['pos'],
              'razon_descarte' => null,
              'intentos_nc' => ($dc['con']['estado']??'')==='no_contesta' ? 1 : 0,
              'accion_post_cambios' => false,
              'p75' => 10, 'mediana' => 5,
              'ticket_empresa' => 90000, 'arquetipo' => $arq,
            ];

            $f = MesaSugerencias::sugerir($ctx);
            $combos++;

            // recomputar hechos como el motor
            $visitas = $rs['visitas']; $dsv = $rs['dsv']; $v24 = $rs['v24'];
            $leyendo = $v24 >= 1;
            $reciente = $visitas>0 && $dsv<=2;
            $dormida = $visitas>0 && $dsv>=7;
            $hot = (bool)$rs['hot'];
            $ult_decl = 0;
            foreach ([$dc['con'],$dc['com'],$dc['pos']] as $d) if ($d) $ult_decl = max($ult_decl, strtotime($d['at']));
            $uv = $ctx['ultima_vista_at'] ? strtotime($ctx['ultima_vista_at']) : 0;
            $reabrio = $ult_decl>0 && $uv>$ult_decl;
            $activo = $leyendo || $reciente || $reabrio || $hot;

            // RULE A: silencio absoluto cuando el cliente abrió hace <=2d
            if (preg_match($RX_SIL_ABS, $f) && !preg_match('/si (?:en \d+ días? )?él no abre|si (?:el cliente )?no (?:abre|responde|contesta)/u',$f) && $reciente) {
              push_viol($viol, 'A_silencio_falso', $f, $ctx);
            }
            // RULE A-rel: silencio relativo cuando SÍ reabrió tras la declaración
            if (preg_match($RX_SIL_REL, $f) && $reabrio) {
              push_viol($viol, 'A_rel_reabrio', $f, $ctx);
            }
            // RULE B: afirma apertura/lectura sin ningún dato de actividad
            if (preg_match($RX_VIEW, $f) && !$activo) {
              push_viol($viol, 'B_vista_falsa', $f, $ctx);
            }
            // RULE B2: "la abrió ayer" pero dsv!=1 ; "veces hoy" pero v24 no matchea
            if (preg_match('/la abrió ayer|abrió la cotización ayer/u',$f) && $dsv!==1) push_viol($viol,'B2_ayer',$f,$ctx);
            if (preg_match('/abrió la cotización hoy|la abrió hoy/u',$f) && $v24<1) push_viol($viol,'B2_hoy',$f,$ctx);
            // RULE C: números citados vs ctx
            if (preg_match('/(\d+)d sin/u',$f,$m) && (int)$m[1]!==$dsv) push_viol($viol,'C_dias',$f,$ctx);
            if (preg_match('/(\d+) veces hoy/u',$f,$m) && (int)$m[1]!==$v24) push_viol($viol,'C_veceshoy',$f,$ctx);
            if (preg_match('/(\d+) intentos/u',$f,$m) && (int)$m[1]!==$ctx['intentos_nc']) push_viol($viol,'C_intentos',$f,$ctx);
            if (preg_match('/(\d+) (?:personas|dispositivos)/u',$f,$m) && (int)$m[1]!==$ctx['ips_7d']) push_viol($viol,'C_personas',$f,$ctx);
            if (preg_match('/(?<!antes del )(?<!antes del el )en (?:el )?día (\d+)|va en (?:el )?día (\d+)/u',$f,$m) ){ $dd=(int)($m[1]?:$m[2]); if($dd!==$edad) push_viol($viol,'C_dia',$f,$ctx); }
            if (preg_match('/los (\d+) días/u',$f,$m) && (int)$m[1]!==$ctx['p75'] && (int)$m[1]!==$ctx['mediana']) push_viol($viol,'C_ventana',$f,$ctx);
            if (preg_match('/antes del día (\d+)/u',$f,$m) && (int)$m[1]!==$ctx['mediana'] && (int)$m[1]!==$ctx['p75']) push_viol($viol,'C_mediana',$f,$ctx);
            // RULE E: Radar caliente citado pero no hot
            if (preg_match('/Radar (?:trae|marca|tiene) la cotización (?:caliente|On Fire|en probable cierre|en cierre inminente)/u',$f) && !$hot) push_viol($viol,'E_hot_falso',$f,$ctx);
            // RULE F: longitud
            $oraciones = preg_split('/(?<=[.?]) /u', $f);
            $maxOra = 0; foreach ($oraciones as $o) $maxOra = max($maxOra, mb_strlen($o));
            if ($maxOra > 185) push_viol($viol,'F_oracion_'.$maxOra,$f,$ctx);
            // RULE D: dos grupos de dígitos (excluye "#1" y montos)
            $tmp = preg_replace('/#\d+/','',$f);
            $tmp = preg_replace('/\$[\d,\.]+/','',$tmp);
            if (preg_match_all('/\d+/u',$tmp,$mm) && count($mm[0])>1) push_viol($viol,'D_dos_numeros',$f,$ctx);
          }
        }
      }
    }
  }
}

echo "Combos evaluados: $combos\n";
echo "Violaciones únicas: " . count($viol) . "\n\n";
// agrupar por regla
$porRegla = [];
foreach ($viol as $v) $porRegla[preg_replace('/_\d+$/','',$v['regla'])] = ($porRegla[preg_replace('/_\d+$/','',$v['regla'])]??0)+1;
foreach ($porRegla as $r=>$n) echo "  $r: $n\n";
echo "\n── DETALLE ──\n";
foreach ($viol as $v) {
  $c = $v['ctx'];
  $ctxs = sprintf("dsv=%d v24=%d visitas=%d hot=%d ips7=%d edad=%d con=%s com=%s pos=%s arq=%s",
    $c['dias_sin_vista'],$c['vistas_24h'],$c['visitas'],$c['es_hot'],$c['ips_7d'],$c['edad'],
    $c['contacto']['estado']??'-',$c['compromiso']['estado']??'-',$c['postura_decl']['estado']??'-',$c['arquetipo']?:'-');
  echo "[{$v['regla']}] (x{$v['n']})\n  FRASE: {$v['frase']}\n  CTX:   $ctxs\n\n";
}
