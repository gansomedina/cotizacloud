<?php
// ============================================================
//  FACT-LINT v2 del motor de tips — cobertura TOTAL de señales.
//  Verifica cada afirmación de HECHOS contra TODO lo que el motor
//  usa: 20 buckets del Radar, momentum, FIT, personas, pc_source,
//  reaperturas, aperturas + declaraciones (contacto/compromiso/
//  postura/👍👎) + negocio (ventana/ticket/intentos).
//  50 reglas del catálogo (workflow). 0 violaciones = motor sano.
// ============================================================
define('COTIZAAPP', 1);
require __DIR__ . '/../core/MesaSugerencias.php';
$RULES = require __DIR__ . '/factlint_tips_rules.php';

$now = time();
$HOY = date('Y-m-d H:i:s', $now);
$H3  = date('Y-m-d H:i:s', $now - 3*86400);

// ── Estados de Radar (bucket + actividad consistente) ──
// hotB = buckets calientes; se prueban con es_hot=1.
$hotB = ['onfire','inminente','validando_precio','decision_activa','prediccion_alta',
         'lectura_comprometida','multi_persona','alto_importe','re_enganche_caliente'];
$coldB = ['re_enganche','regreso','revivio','vistas_multiples','hesitacion',
          'sobre_analisis','comparando','enfriandose'];

$states = [];
// nunca abierta
$states[] = ['visitas'=>0,'dsv'=>30,'v24'=>0,'v7'=>0,'hot'=>0,'ips7'=>1,'bucket'=>null,'uv'=>null,'mom'=>null,'fit'=>0,'pc'=>null];
// buckets calientes: viendo reciente
foreach ($hotB as $b) {
  $ips = $b==='multi_persona' ? 3 : 1;
  $states[] = ['visitas'=>3,'dsv'=>0,'v24'=>1,'v7'=>3,'hot'=>1,'ips7'=>$ips,'bucket'=>$b,'uv'=>0,'mom'=>null,'fit'=>0,'pc'=>null];
}
// probable_cierre con cada pc_source
foreach ($hotB as $b) {
  $ips = $b==='multi_persona' ? 3 : 1;
  $states[] = ['visitas'=>3,'dsv'=>0,'v24'=>1,'v7'=>3,'hot'=>1,'ips7'=>$ips,'bucket'=>'probable_cierre','uv'=>0,'mom'=>null,'fit'=>0,'pc'=>$b];
}
// buckets fríos (no hot)
foreach ($coldB as $b) {
  // buckets fríos-ACTIVOS: el cliente sigue abriendo (reciente); enfriandose declina
  if ($b==='enfriandose') { $dsv=4; $v24=0; $mom='down'; }
  elseif (in_array($b,['hesitacion','vistas_multiples','comparando'],true)) { $dsv=1; $v24=1; $mom=null; }
  else { $dsv=1; $v24=0; $mom=null; } // re_enganche/regreso/revivio/sobre_analisis: volvió reciente
  $states[] = ['visitas'=>3,'dsv'=>$dsv,'v24'=>$v24,'v7'=>2,'hot'=>0,'ips7'=>1,'bucket'=>$b,'uv'=>$dsv,'mom'=>$mom,'fit'=>0,'pc'=>null];
}
// sin bucket: variar momentum/fit/dsv/edad para las ramas default
$states[] = ['visitas'=>3,'dsv'=>4,'v24'=>0,'v7'=>2,'hot'=>0,'ips7'=>1,'bucket'=>null,'uv'=>4,'mom'=>'down','fit'=>0,'pc'=>null];
$states[] = ['visitas'=>3,'dsv'=>3,'v24'=>0,'v7'=>4,'hot'=>0,'ips7'=>1,'bucket'=>null,'uv'=>3,'mom'=>null,'fit'=>70,'pc'=>null];
$states[] = ['visitas'=>3,'dsv'=>1,'v24'=>0,'v7'=>2,'hot'=>0,'ips7'=>1,'bucket'=>null,'uv'=>1,'mom'=>null,'fit'=>0,'pc'=>null];
$states[] = ['visitas'=>3,'dsv'=>0,'v24'=>2,'v7'=>3,'hot'=>0,'ips7'=>1,'bucket'=>null,'uv'=>0,'mom'=>null,'fit'=>0,'pc'=>null];
$states[] = ['visitas'=>3,'dsv'=>8,'v24'=>0,'v7'=>0,'hot'=>0,'ips7'=>1,'bucket'=>null,'uv'=>8,'mom'=>null,'fit'=>0,'pc'=>null];
$states[] = ['visitas'=>3,'dsv'=>15,'v24'=>0,'v7'=>0,'hot'=>0,'ips7'=>1,'bucket'=>null,'uv'=>15,'mom'=>null,'fit'=>0,'pc'=>null];
$states[] = ['visitas'=>3,'dsv'=>2,'v24'=>0,'v7'=>2,'hot'=>0,'ips7'=>1,'bucket'=>null,'uv'=>2,'mom'=>null,'fit'=>0,'pc'=>null];

// ── Declaraciones ──
$decls = [];
$decls[] = ['con'=>null,'com'=>null,'pos'=>null];
foreach (['no_contesta','hablamos'] as $ce) {
  $decls[] = ['con'=>['estado'=>$ce,'at'=>$HOY],'com'=>null,'pos'=>null];
  foreach ([null,'compromiso','propuse_no_quiso','sin_compromiso'] as $ke) {
    foreach ([null,'decidiendo','objecion_precio','pidio_cambios','en_el_aire'] as $pe) {
      if ($ke===null && $pe===null) continue;
      $d = ['con'=>['estado'=>$ce,'at'=>$HOY],'com'=>null,'pos'=>null];
      if ($ke) $d['com']=['estado'=>$ke,'at'=>$HOY];
      if ($pe) $d['pos']=['estado'=>$pe,'at'=>$HOY];
      $decls[] = $d;
      if ($ke==='compromiso') { $d2=$d; $d2['com']=['estado'=>'compromiso','at'=>$H3]; $decls[]=$d2; }
    }
  }
}
foreach (['decidiendo','objecion_precio','pidio_cambios','en_el_aire'] as $pe)
  $decls[] = ['con'=>null,'com'=>null,'pos'=>['estado'=>$pe,'at'=>$HOY]];

$cats   = ['trabajo','interes_muriendo','ultimo_tramo'];
$edades = [2,6,12,25];
$arqs   = ['','regalador','sin_ritmo','teatro','cierre_falso','sordo_a_senales','meseta','una_pierna','francotirador'];
$totals = [50000, 200000, 350000];

$combos=0; $viol=[]; $everfired=[];
function pv(&$viol,$rid,$frase,$ctxs){ $k=$rid.'||'.$frase; if(!isset($viol[$k]))$viol[$k]=['rid'=>$rid,'frase'=>$frase,'ctx'=>$ctxs,'n'=>0]; $viol[$k]['n']++; }

foreach ($states as $rs) {
  foreach ($decls as $dc) {
    foreach ($cats as $cat) {
      // cat interes/ultimo solo tiene sentido sin declaración de postura y con 👍
      if ($cat!=='trabajo' && ($dc['pos']||$dc['com'])) continue;
      $feedback = in_array($cat,['interes_muriendo','ultimo_tramo'],true) ? 'con_interes' : null;
      // interes_muriendo solo existe con cliente dormido (dsv>=7); si no, saltar
      if ($cat==='interes_muriendo' && !($rs['visitas']>0 && $rs['dsv']>=7)) continue;
      foreach ($edades as $edad) {
        // ultimo_tramo solo existe fuera de ventana (edad>p75=10)
        if ($cat==='ultimo_tramo' && $edad <= 10) continue;
        foreach ($totals as $total) {
          foreach ($arqs as $arq) {
            foreach ([0,1] as $sv) {
              $pos_e0 = $dc['pos']['estado'] ?? null;
              $ctx = [
                'cot_id'=>$sv+$edad*3+$rs['dsv']*7+strlen((string)$rs['bucket']),
                'total'=>$total,'edad'=>$edad,'cat'=>$cat,
                'bucket'=>$rs['bucket'],'es_hot'=>$rs['hot'],'pc_source'=>$rs['pc'],
                'momentum'=>$rs['mom'],'fit_pct'=>$rs['fit'],
                'visitas'=>$rs['visitas'],'dias_sin_vista'=>$rs['dsv'],
                'vistas_24h'=>$rs['v24'],'vistas_7d'=>$rs['v7'],'ips_7d'=>$rs['ips7'],
                'ultima_vista_at'=>$rs['uv']===null?null:date('Y-m-d H:i:s',$now-$rs['uv']*86400),
                'revivida'=>false,'milagro'=>false,
                'contacto'=>$dc['con'],'compromiso'=>$dc['com'],'postura_decl'=>$dc['pos'],
                'razon_descarte'=>null,
                'intentos_nc'=>($dc['con']['estado']??'')==='no_contesta'?3:0,
                'accion_post_cambios'=>($pos_e0==='pidio_cambios' && $sv===1 && $rs['dsv']>=3),
                'p75'=>10,'mediana'=>5,'ticket_empresa'=>90000,'arquetipo'=>$arq,
              ];
              $f = MesaSugerencias::sugerir($ctx);
              $combos++;

              // ── derivar TODAS las vars que usan las reglas ──
              $dsv=$rs['dsv']; $v24=$rs['v24']; $v7=$rs['v7']; $visitas=$rs['visitas'];
              $hot=(bool)$rs['hot']; $bucket=$rs['bucket']; $momentum=$rs['mom']; $fit=$rs['fit'];
              $ips7=$rs['ips7']; $pc_source=$rs['pc']; $edad2=$edad; $p75=10; $mediana=5;
              $intentos_nc=$ctx['intentos_nc']; $accion_post_cambios=$ctx['accion_post_cambios'];
              $ratio=$total/90000.0;
              $leyendo=$v24>=1; $reciente=$visitas>0 && $dsv<=2; $dormida=$visitas>0 && $dsv>=7;
              $ult_decl=0; foreach([$dc['con'],$dc['com'],$dc['pos']] as $d) if($d)$ult_decl=max($ult_decl,strtotime($d['at']));
              $uv=$ctx['ultima_vista_at']?strtotime($ctx['ultima_vista_at']):0;
              $reabrio=$ult_decl>0 && $uv>$ult_decl;
              $tc=$dc['con']?strtotime($dc['con']['at']):0;
              $con_e=$dc['con']['estado']??null; $com_e=$dc['com']['estado']??null; $pos_e=$dc['pos']['estado']??null;
              $com_vig=$dc['com'] && strtotime($dc['com']['at'])>=$tc;
              $pos_vig=$dc['pos'] && strtotime($dc['pos']['at'])>=$tc;
              $feedbackv=$feedback;
              $edad=$edad2;

              $ctxs=sprintf("bucket=%s hot=%d dsv=%d v24=%d ips7=%d mom=%s fit=%d cat=%s edad=%d con=%s com=%s pos=%s arq=%s ratio=%.1f",
                $bucket?:'-',$hot,$dsv,$v24,$ips7,$momentum?:'-',$fit,$cat,$edad,$con_e?:'-',$com_e?:'-',$pos_e?:'-',$arq?:'-',$ratio);

              $fc = preg_replace('/\\bsi\\b[^,.;]*/iu',' ',$f); // quitar clausulas condicionales (no son afirmaciones)
              foreach ($RULES as $R) {
                if (!@preg_match($R['rx'],$fc,$m)) continue; // regex no matchea (o error) → skip
                // resolver $1 = primer grupo numérico capturado
                $cap=null;
                for($i=1;$i<count($m);$i++){ if(isset($m[$i]) && $m[$i]!=='' && ctype_digit($m[$i])){ $cap=(int)$m[$i]; break; } }
                $req = str_replace('$1', $cap===null?'-999':(string)$cap, $R['req']);
                $req = str_replace('$feedback','$feedbackv',$req);
                $ok=false;
                try { $ok = eval('return ('.$req.');'); }
                catch (\Throwable $e) { $viol['ERR||'.$R['id']]=['rid'=>'ERR:'.$R['id'],'frase'=>$req,'ctx'=>$e->getMessage(),'n'=>1]; continue; }
                if (!$ok) pv($viol,$R['id'],$f,$ctxs);
                else $everfired[$R['id']]=($everfired[$R['id']]??0)+1;
              }
              // longitud por oración
              $ora=preg_split('/(?<=[.?]) /u',$f); $mo=0; foreach($ora as $o)$mo=max($mo,mb_strlen($o));
              if ($mo>185) pv($viol,'F_oracion_'.$mo,$f,$ctxs);
            }
          }
        }
      }
    }
  }
}


// ── OVERLAYS revivida/milagro: freshness gate (hueco marcado por el workflow) ──
// La afirmación "regresó/está viendo AHORA/antes de que se enfríe" exige que el
// cliente esté fresco (abrió <=2d) o caliente; si ya se calló, es mentira.
$RX_REV_URGENTE = '/antes de que se (?:vuelva a )?enfr[íi]e|una se[ñn]al as[íi] no se repite|una segunda oportunidad as[íi] no se repite|regres[óo] solo|volvi[óo] a abrir la cotizaci[óo]n esta semana|reabri[óo] una cotizaci[óo]n que ya hab[íi]as descartado|La descartaste por precio y el cliente la volvi[óo] a abrir solo|reabri[óo] sin que lo buscaras|esta se[ñn]al no va a repetirse/u';
$RX_MIL_AHORA = '/est[áa] (?:viendo|leyendo)[^.]{0,30}AHORA|la tiene (?:abierta )?AHORA|la est[áa] viendo AHORA|est[áa] leyendo AHORA/u';
foreach ([0,1,2,4,6] as $dsvv) {
  foreach (['','precio','no_responde','competencia','despues'] as $rz) {
    foreach ([0,1] as $ihot) {
      $ov = [
        'cot_id'=>$dsvv+strlen($rz), 'total'=>50000,'edad'=>7,'cat'=>'revivida',
        'bucket'=>$ihot?'onfire':null,'es_hot'=>$ihot,'pc_source'=>null,'momentum'=>null,'fit_pct'=>0,
        'visitas'=>3,'dias_sin_vista'=>$dsvv,'vistas_24h'=>($dsvv===0?1:0),'vistas_7d'=>1,'ips_7d'=>1,
        'ultima_vista_at'=>date('Y-m-d H:i:s',$now-$dsvv*86400),'revivida'=>true,'milagro'=>false,
        'contacto'=>null,'compromiso'=>null,'postura_decl'=>null,'razon_descarte'=>$rz?:null,
        'intentos_nc'=>0,'accion_post_cambios'=>false,'p75'=>10,'mediana'=>5,'ticket_empresa'=>90000,'arquetipo'=>'',
      ];
      $fr = MesaSugerencias::sugerir($ov);
      $fresco = ($dsvv<=2) || $ihot;
      if (preg_match($RX_REV_URGENTE,$fr) && !$fresco)
        pv($viol,'OV_revivida_stale',$fr,"revivida dsv=$dsvv hot=$ihot rz=".($rz?:'-'));
      // milagro SOLO existe con hot (Mesa.php: cat milagro requiere hot_reciente)
      if (!$ihot) continue;
      $ov['cat']='trabajo'; $ov['revivida']=false; $ov['milagro']=true;
      $fm = MesaSugerencias::sugerir($ov);
      $activo = ($ov['vistas_24h']>=1); // AHORA exige apertura de hoy, no solo hot
      if (preg_match($RX_MIL_AHORA,$fm) && !$activo)
        pv($viol,'OV_milagro_stale',$fm,"milagro dsv=$dsvv hot=$ihot");
    }
  }
}

echo "Combos: $combos\n";
echo "Reglas cargadas: ".count($RULES)."\n";
$nunca = array_filter($RULES, fn($r)=>!isset($everfired[$r['id']]));
echo "Reglas que NUNCA dispararon (posible malla incompleta): ".count($nunca)."\n";
foreach($nunca as $r) echo "   ~ ".$r['id']."\n";
echo "\nViolaciones únicas: ".count($viol)."\n\n";
$byr=[]; foreach($viol as $v){$b=preg_replace('/_\d+$/','',$v['rid']); $byr[$b]=($byr[$b]??0)+1;}
foreach($byr as $r=>$n) echo "  $r: $n\n";
echo "\n── UN EJEMPLO POR REGLA ──\n"; $seen=[];
foreach($viol as $v){ $b=preg_replace('/_\d+$/','',$v['rid']); if(isset($seen[$b]))continue; $seen[$b]=1;
  echo "[{$v['rid']}] (x{$v['n']})\n  FRASE: {$v['frase']}\n  CTX:   {$v['ctx']}\n\n"; }
