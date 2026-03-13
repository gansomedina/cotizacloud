<?php
// ============================================================
//  CotizaApp — modules/auth/logout.php
//  GET /logout — Cierra sesión
// ============================================================

defined('COTIZAAPP') or die;

Auth::logout();
redirect('/login');
