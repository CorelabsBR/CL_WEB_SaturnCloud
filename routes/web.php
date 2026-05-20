<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WebHostingController;
use App\Http\Controllers\GameHostingController;
use App\Http\Controllers\CartCheckoutController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\MercadoPagoWebhookController;

Route::get('/', fn () => view('home'))->name('home');
Route::get('/vps', fn () => view('vps.index'))->name('vps.index');
Route::get('/termos', fn () => view('termos.service'))->name('termos.service');
Route::get('/politica-privacidade', fn () => view('privacidade.index'))->name('privacidade');
Route::get('/reembolso', fn () => view('reembolso.index'))->name('reembolso');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::middleware('guest')->group(function () {
    Route::get('/registro', [AuthController::class, 'showRegister'])->name('registro');
    Route::post('/registro', [AuthController::class, 'register'])->name('registro.post');
});

Route::middleware('auth')->group(function () {
    Route::get('/completar-cadastro', [AuthController::class, 'showCompleteProfile'])
        ->name('cadastro.completar');

    Route::post('/completar-cadastro', [AuthController::class, 'completeProfile'])
        ->name('cadastro.completar.post');

    Route::get('/perfil', [AuthController::class, 'showEditProfile'])
        ->name('perfil.edit');

    Route::patch('/perfil', [AuthController::class, 'updateProfile'])
        ->name('perfil.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/recuperar-senha', fn () => view('auth.forgot-password'))
    ->name('password.request');

Route::get('/web', [WebHostingController::class, 'index'])->name('web');

Route::get('/games', [GameHostingController::class, 'index'])->name('games.index');
Route::get('/games/{jogo:slug}', [GameHostingController::class, 'show'])->name('games.show');

Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])
    ->name('social.redirect');

Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])
    ->name('social.callback');

Route::middleware('auth')->group(function () {
    Route::get('/carrinho', [CartController::class, 'index'])->name('cart.index');
    Route::post('/carrinho/add-plan/{plan}', [CartController::class, 'addPlan'])->name('cart.addPlan');
    Route::post('/carrinho/produto/{product}', [CartController::class, 'addProduct'])->name('cart.addProduct');
    Route::patch('/carrinho/update/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/carrinho/remove/{cartItem}', [CartController::class, 'remove'])->name('cart.remove');
    Route::delete('/carrinho/clear', [CartController::class, 'clear'])->name('cart.clear');
    Route::get('/suporte', fn () => redirect('/')->with('support_widget_open', true))->name('ticket.index');
    Route::get('/painel', fn () => view('dashboard.index'))->name('dashboard.index');
    Route::get('/dashboard', fn () => redirect()->route('dashboard.index'));

    Route::post('/suporte', function (\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'subject' => ['required', 'string', 'min:4', 'max:120'],
            'category' => ['required', 'string', 'max:60'],
            'priority' => ['required', 'in:baixa,normal,alta,urgente'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $tickets = session('support_tickets', []);
        $tickets[] = [
            ...$data,
            'id' => now()->format('YmdHis'),
            'status' => 'aberto',
            'created_at' => now()->format('d/m/Y H:i'),
        ];

        session(['support_tickets' => $tickets]);

        return redirect()
            ->back()
            ->with('support_widget_open', true)
            ->with('success', 'Chamado aberto com sucesso. Nossa equipe já pode acompanhar sua solicitação.');
    })->name('ticket.store');

});

Route::middleware('auth')->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/pagar', [CheckoutController::class, 'pay'])->name('checkout.pay');

    Route::get('/checkout/sucesso', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/checkout/pendente', [CheckoutController::class, 'pending'])->name('checkout.pending');
    Route::get('/checkout/falhou', [CheckoutController::class, 'failure'])->name('checkout.failure');
});

Route::post('/webhooks/mercadopago', [MercadoPagoWebhookController::class, 'handle'])
    ->name('webhooks.mercadopago')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
