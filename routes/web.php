<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\HelpCenterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\VpsController;
use App\Http\Controllers\WebHostingController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::view('/modelo-saturno', 'pages.saturn-model')->name('saturn.model');
Route::get('/ajuda', [HelpCenterController::class, 'index'])->name('help.index');
Route::get('/ajuda/{slug}', [HelpCenterController::class, 'show'])->name('help.show');
Route::get('/{page}', [PublicPageController::class, 'show'])
    ->whereIn('page', ['revenda', 'dominios', 'infraestrutura', 'sobre', 'faq', 'contato', 'sla'])
    ->name('pages.show');
Route::get('/conteudos', [ContentController::class, 'index'])->name('content.index');
Route::get('/sitemap.xml', [ContentController::class, 'sitemap'])->name('sitemap');
Route::get('/vps', [VpsController::class, 'index'])->name('vps.index');
Route::get('/vps/linux', fn () => app(PublicPageController::class)->show('vps-linux'))->name('vps.linux');
Route::get('/vps/windows', fn () => app(PublicPageController::class)->show('vps-windows'))->name('vps.windows');
Route::get('/termos', fn () => view('termos.service'))->name('termos.service');
Route::get('/politica-privacidade', fn () => view('privacidade.index'))->name('privacidade');
Route::get('/reembolso', fn () => view('reembolso.index'))->name('reembolso');
Route::get('/404', fn () => abort(404));
Route::get('/403', fn () => abort(403));
Route::get('/500', fn () => abort(500));
Route::get('/503', fn () => response()->view('errors.503', [], 503));

Route::get('/web', [WebHostingController::class, 'index'])->name('web');
Route::get('/carrinho', [CartController::class, 'index'])->name('cart.index');
Route::post('/carrinho/produto/{product}', [CartController::class, 'addProduct'])->name('cart.addProduct');
Route::patch('/carrinho/update/{product}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/carrinho/remove/{product}', [CartController::class, 'remove'])->name('cart.remove');
Route::delete('/carrinho/clear', [CartController::class, 'clear'])->name('cart.clear');
